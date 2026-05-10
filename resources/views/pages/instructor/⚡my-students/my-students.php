<?php

use Livewire\Component;
use App\Models\Enrollment;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use App\Models\BookingSession;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\InstructorMetric;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Flux\Flux;
new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $module = 'all';
    public $selectedEnrollmentId = null;
    public $activeSessionExists = false;


    public $pdcEnrollmentId = null;
    public $selectedVehicleId = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingModule()
    {
        $this->resetPage();
    }

    public function showDetails($id)
    {
        $this->selectedEnrollmentId = $id;
        $this->dispatch('modal-opened', name: 'student-details');
    }

    #[Computed]
    public function selectedEnrollment()
    {
        if (!$this->selectedEnrollmentId) {
            return null;
        }
        return Enrollment::with(['studentProfile.user', 'course'])->find($this->selectedEnrollmentId);
    }

    #[Computed]
    public function unpaidStudents()
    {
        return Enrollment::where('instructor_id', Auth::user()->instructorProfile->id)
            ->where('status', 'active')
            ->where('balance', '>', 0)
            ->with('studentProfile.user')
            ->get();
    }

    #[Computed]
    public function enrollmentCounts()
    {
        $instructorId = Auth::user()->instructorProfile->id;
        return [
            'total' => Enrollment::where('instructor_id', $instructorId)->count(),
            'pending' => Enrollment::where('instructor_id', $instructorId)->where('status', 'pending')->count(),
            'active' => Enrollment::where('instructor_id', $instructorId)->where('status', 'active')->count(),
            'completed' => Enrollment::where('instructor_id', $instructorId)->where('status', 'completed')->count(),
            'dropped' => Enrollment::where('instructor_id', $instructorId)->where('status', 'dropped')->count(),
        ];
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::query()
            ->with(['studentProfile.user', 'course'])
            ->where('instructor_id', Auth::user()->instructorProfile->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('studentProfile.user', function ($sub) {
                            $sub->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('course', function ($sub) {
                            $sub->where('title', 'like', '%' . $this->search . '%')->orWhere('code', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->module !== 'all', function ($query) {
                if ($this->module === 'tdc') {
                    $query->where('tdc_hours_required', '>', 0);
                } elseif ($this->module === 'pdc') {
                    $query->where('pdc_hours_required', '>', 0);
                }
            })
            ->latest()
            ->paginate(12); 
    }

    public function beginTDC()
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $instructorId = Auth::user()->instructorProfile->id;
        $now = now();

        // Get the TDC Course ID
        $courseId = Course::where('type', 'theoretical')->value('id');

        if (!$courseId) {
            session()->flash('error', 'TDC course configuration missing.');
            return;
        }

        // Fetch active enrollments for this instructor/course that don't already have an active session
        $allEnrollments = Enrollment::where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->where('course_id', $courseId)
            ->whereDoesntHave('bookingSessions', function ($query) {
                $query->whereIn('status', ['scheduled'])
                    ->whereNull('end_time');
            })
            ->get();

        if ($allEnrollments->isEmpty()) {
            session()->flash('warning', 'No active students found without an existing session.');
            return;
        }

        $paidEnrollments = $allEnrollments->filter(fn($enrollment) => $enrollment->balance <= 0);
        $unpaidCount = $allEnrollments->count() - $paidEnrollments->count();

        if ($paidEnrollments->isEmpty()) {
            session()->flash('warning', 'Cannot start TDC sessions — ' . $unpaidCount . ' student(s) have an outstanding balance.');
            return;
        }

        DB::transaction(function () use ($paidEnrollments, $instructorId, $now) {
            foreach ($paidEnrollments as $enrollment) {
                BookingSession::create([
                    'enrollment_id' => $enrollment->id,
                    'instructor_id' => $instructorId,
                    'start_time' => $now,
                    'type' => 'lecture',
                    'status' => 'scheduled',
                ]);
            }
        });

        $message = "TDC Session started for {$paidEnrollments->count()} student(s).";
        if ($unpaidCount > 0) {
            $message .= " ({$unpaidCount} student(s) were skipped due to outstanding balance.)";
        }

        session()->flash('success', $message);
        return $this->redirect(route('instructor.my-schedule'), navigate: true);
    }

    #[Computed]
    public function availableVehicles()
    {
        return Vehicle::where('status', 'available')->get();
    }

    public function openPDCModal(int $enrollmentId)
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $this->pdcEnrollmentId = $enrollmentId;
        $this->selectedVehicleId = '';
        
        $this->dispatch('modal-opened', name: 'start-pdc-modal');
    }

    public function confirmBeginPDC()
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $this->validate([
            'selectedVehicleId' => 'required|exists:vehicles,id',
        ], [
            'selectedVehicleId.required' => 'Please select a vehicle for this session.',
        ]);

        $enrollmentId = $this->pdcEnrollmentId;
        $instructorId = Auth::user()->instructorProfile->id;

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->where('pdc_hours_required', '>', 0)
            ->whereNot('pdc_status', 'completed')
            ->firstOrFail();

        // Check if student has fully paid
        if ($enrollment->balance > 0) {
            session()->flash('warning', 'Cannot start PDC session — this student has an outstanding balance of ₱' . number_format($enrollment->balance, 2) . '.');
            return;
        }

        // Prevent duplicate active PDC sessions
        $hasActiveSession = BookingSession::where('enrollment_id', $enrollment->id)
            ->where('status', 'scheduled')
            ->where('type', 'driving')
            ->whereNull('end_time')
            ->exists();

        if ($hasActiveSession) {
            session()->flash('error', 'A PDC session is already active for this student.');
            return;
        }

        //Update PDC status to in_progress if it was not_started
        if ($enrollment->pdc_status === 'not_started') {
            $enrollment->update(['pdc_status' => 'in_progress']);
        }

        $session = BookingSession::create([
            'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructorId,
            'vehicle_id'    => $this->selectedVehicleId,
            'start_time'    => now(),
            'type'          => 'driving',
            'status'        => 'scheduled',
        ]);

        // Mark vehicle as in-use
        Vehicle::where('id', $this->selectedVehicleId)->update(['status' => 'in-use']);

        //Find or create the single assessment for this enrollment
        $assessment = Assessment::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'assessment_type' => 'practical'],
            [
                'student_id'      => $enrollment->student_id,
                'instructor_id'   => $instructorId,
                'assessment_date' => now()->format('Y-m-d'),
                'is_passed'       => null, // Keep null until final completion
            ]
        );

        // Link the latest booking session to the assessment
        $assessment->update(['booking_session_id' => $session->id]);

        return $this->redirect(route('instructor.assessment', [
            'enrollment'     => $enrollment->id,
            'bookingSession' => $session->id,
        ]), navigate: true);
    }


};
