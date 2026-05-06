<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\BookingSession;
use App\Models\InstructorMetric;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $activeSessionExists = '';
    public $cancelSessionId = null;
    public $cancelReason = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function openCancelModal($sessionId)
    {
        $this->cancelSessionId = $sessionId;
        $this->cancelReason = '';
        \Flux\Flux::modal('cancel-session')->show();
    }

    public function cancelSession()
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $this->validate([
            'cancelReason' => 'required|string|min:10',
        ]);

        $session = BookingSession::where('instructor_id', Auth::user()->instructorProfile->id)
            ->where('status', 'scheduled')
            ->findOrFail($this->cancelSessionId);

        $logs = $session->change_log ?? [];
        $logs[] = [
            'action' => 'cancelled',
            'reason' => $this->cancelReason,
            'cancelled_by_user_id' => Auth::id(),
            'cancelled_at' => now()->toIso8601String(),
        ];

        $session->update([
            'status' => 'cancelled',
            'change_log' => $logs,
        ]);

        \Flux\Flux::modal('cancel-session')->close();
        session()->flash('success', 'Session cancelled successfully.');

        $this->cancelSessionId = null;
        $this->cancelReason = '';
    }

    #[Computed]
    public function bookingCounts()
    {
        $instructorId = Auth::user()->instructorProfile->id;
        return [
            'total' => BookingSession::where('instructor_id', $instructorId)->count(),
            'scheduled' => BookingSession::where('instructor_id', $instructorId)->where('status', 'scheduled')->count(),
            'completed' => BookingSession::where('instructor_id', $instructorId)->where('status', 'completed')->count(),
            'cancelled' => BookingSession::where('instructor_id', $instructorId)->where('status', 'cancelled')->count(),
            'no-show' => BookingSession::where('instructor_id', $instructorId)->where('status', 'no-show')->count(),
        ];
    }
    // Schedules
    #[Computed]
    public function bookings()
    {
        return BookingSession::query()
            ->with(['enrollment.studentProfile.user', 'enrollment.course', 'vehicle'])
            ->where('instructor_id', Auth::user()->instructorProfile->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('enrollment.studentProfile.user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('enrollment.course', function ($sub) {
                            $sub->where('title', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('vehicle', function ($sub) {
                            $sub->where('plate_number', 'like', '%' . $this->search . '%')->orWhere('model', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->latest('start_time')
            ->paginate(12);
    }

    public function endTDC()
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $instructorId = Auth::user()->instructorProfile->id;
        $now = now();

        // Get the TDC active sessions
        $activeSessions = BookingSession::where('instructor_id', $instructorId)
            ->where('status', 'scheduled')
            ->whereNull('end_time')
            ->where('start_time', '<=', $now)
            ->whereHas('enrollment.course', function ($q) {
                $q->where('type', 'theoretical');
            })
            ->with('enrollment')
            ->get();

        if ($activeSessions->isEmpty()) {
            session()->flash('status', 'No active TDC sessions found to end.');
            return;
        }

        $affectedCount = 0;
        $totalBatchDuration = 0;

        DB::transaction(function () use ($activeSessions, $now, &$affectedCount, &$totalBatchDuration, $instructorId) {
            foreach ($activeSessions as $session) {
                $enrollment = $session->enrollment;

                // Calculate duration in hours
                $durationHours = $session->start_time->diffInMinutes($now) / 60;
                $durationHours = round($durationHours, 2);
                $totalBatchDuration += $durationHours;

                // Update Session
                $session->update([
                    'end_time' => $now,
                    'status' => 'completed',
                ]);

                // Update Enrollment Progress
                $newTdcHours = ($enrollment->tdc_hours_completed ?? 0) + $durationHours;
                $maxTdcHours = $enrollment->tdc_hours_required;

                // Cap at required hours
                if ($newTdcHours > $maxTdcHours) {
                    $newTdcHours = $maxTdcHours;
                }

                $tdcStatus = $newTdcHours >= $maxTdcHours ? 'completed' : 'in_progress';

                // Recalculate overall progress
                $totalRequired = ($enrollment->tdc_hours_required ?? 0) + ($enrollment->pdc_hours_required ?? 0);
                $totalCompleted = $newTdcHours + ($enrollment->pdc_hours_completed ?? 0);
                $progressPercent = $totalRequired > 0 ? ($totalCompleted / $totalRequired) * 100 : 100;

                $enrollment->update([
                    'tdc_hours_completed' => $newTdcHours,
                    'tdc_status' => $tdcStatus,
                    'progress_percent' => round($progressPercent, 2),
                    'status' => $progressPercent >= 100 ? 'completed' : 'active',
                ]);

                $affectedCount++;
            }

            // Update Instructor Metrics for the current month
            $metric = InstructorMetric::firstOrCreate(
                [
                    'instructor_id' => $instructorId,
                    'metric_month' => $now->copy()->startOfMonth()->format('Y-m-d'),
                ],
                [
                    'total_sessions' => 0,
                    'completed_sessions' => 0,
                    'total_hours' => 0,
                    'avg_rating' => 0,
                    'students_taught' => 0,
                    'students_passed' => 0,
                    'pass_rate' => 0,
                ],
            );

            $metric->increment('total_sessions', $affectedCount);
            $metric->increment('total_hours', round($totalBatchDuration, 2));
        });

        session()->flash('status', "TDC Session ended for {$affectedCount} records. Progress updated.");
        return $this->redirect(route('instructor.my-students'), navigate: true);
    }
};
