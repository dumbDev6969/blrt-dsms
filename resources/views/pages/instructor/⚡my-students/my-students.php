<?php

use Livewire\Component;
use App\Models\Enrollment;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use App\Models\BookingSession;
use App\Models\Course;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;
new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $module = 'all';
    public $selectedEnrollmentId = null;
    public $activeSessionExists = false;

    public $grades = [];
    public $results = [];
    public $remarks = [];

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

        // Fetch active enrollments for this instructor/course
        $enrollments = Enrollment::where('instructor_id', $instructorId)->where('status', 'active')->where('course_id', $courseId)->get();

        DB::transaction(function () use ($enrollments, $instructorId, $now) {
            foreach ($enrollments as $enrollment) {
                // Get the ssession that is not yet completed or cancelled
                $hasActiveSession = BookingSession::where('enrollment_id', $enrollment->id)
                    ->whereIn('status', ['scheduled']) 
                    ->whereNull('end_time')
                    ->exists();

                if (!$hasActiveSession) {
                    BookingSession::create([
                        'enrollment_id' => $enrollment->id,
                        'instructor_id' => $instructorId,
                        'start_time' => $now,
                        'type' => 'lecture',
                        'status' => 'scheduled',
                    ]);
                }
            }
        });

        session()->flash('success', 'TDC Session started.');
    }
    
    public function beginPDC(int $enrollmentId)
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $instructorId = Auth::user()->instructorProfile->id;

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->where('pdc_hours_required', '>', 0)
            ->whereNot('pdc_status', 'completed')
            ->firstOrFail();

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

        $session = BookingSession::create([
            'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructorId,
            'start_time'    => now(),
            'type'          => 'driving',
            'status'        => 'scheduled',
        ]);

        // [NEW] Find or create the single assessment for this enrollment
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

    public function submitGrade($enrollmentId)
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('instructor_id', Auth::user()->instructorProfile->id)
            ->firstOrFail();

        $this->validate([
            "grades.$enrollmentId" => 'nullable|numeric|min:0|max:100',
            "results.$enrollmentId" => 'required|in:pass,fail',
            "remarks.$enrollmentId" => 'nullable|string',
        ]);

        DB::transaction(function () use ($enrollment, $enrollmentId) {
            // Update the enrollment with final grade results
            $enrollment->update([
                'final_grade'  => $this->grades[$enrollmentId] ?? null,
                'final_result' => $this->results[$enrollmentId],
                'remarks'      => $this->remarks[$enrollmentId] ?? null,
                'status'       => 'completed',
            ]);

            // If there's an active TDC session, mark it as completed
            BookingSession::where('enrollment_id', $enrollmentId)
                ->where('status', 'scheduled')
                ->whereHas('enrollment.course', function($q) { $q->where('type', 'theoretical'); })
                ->update(['status' => 'completed', 'end_time' => now()]);
        });

        session()->flash('success', 'Student grade submitted successfully.');
        \Flux::modal("score-{$enrollmentId}")->close();
    }
};
