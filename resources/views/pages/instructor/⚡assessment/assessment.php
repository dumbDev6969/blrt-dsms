<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\BookingSession;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

new class extends Component
{
    public Enrollment $enrollment;
    public BookingSession $bookingSession;

    // Form fields
    public $assessment_type = 'practical';
    public $assessment_date;
    
    // Checklist items (Checked = Error found)
    public $pre_drive_checklist = [];
    public $pre_drive_errors = 0;
    
    // Critical errors (Checked = Failure)
    public $immediate_fails = [];
    public $immediate_fail_count = 0;

    // Ratings (1=Poor, 2=Fair, 3=Good)
    public $driving_skills = [];
    public $driving_skills_rating = 0; // Cumulative Poor count placeholder
    
    public $traffic_rules = [];
    public $traffic_rules_rating = 0; // Cumulative Poor count placeholder

    public $learner_type = '2'; // Default: Fast Learner
    public $recommended_for_additional_driving = false;
    public $instructor_remarks = '';
    public $observations = '';
    public $is_passed = null; // Stays null until final complete
    public $failure_reason = '';

    // Overall stats for "Important Note" logic
    public $total_poor_marks = 0;

    protected $rules = [
        'assessment_type' => 'required',
        'assessment_date' => 'required|date',
        'learner_type' => 'required',
        // is_passed is only required on final completion
    ];

    public function mount(Enrollment $enrollment, BookingSession $bookingSession)
    {
        if ($enrollment->instructor_id !== Auth::user()->instructorProfile->id || 
            $bookingSession->enrollment_id !== $enrollment->id) {
            abort(403);
        }

        $this->enrollment = $enrollment;
        $this->bookingSession = $bookingSession;
        $this->assessment_date = now()->format('Y-m-d');

        // Check if an assessment already exists for this enrollment
        $assessment = Assessment::where('enrollment_id', $this->enrollment->id)
            ->where('assessment_type', 'practical')
            ->first();

        if ($assessment) {
            // Load saved data
            $this->pre_drive_checklist = $assessment->pre_drive_checklist ?? [];
            $this->immediate_fails = $assessment->immediate_fails ?? [];
            $this->driving_skills = $assessment->driving_skills ?? [];
            $this->traffic_rules = $assessment->traffic_rules ?? [];
            $this->learner_type = match($assessment->learner_type) {
                'slow' => '1',
                'fast' => '2',
                'hesitant' => '3',
                default => '2'
            };
            $this->recommended_for_additional_driving = $assessment->recommended_for_additional_driving;
            $this->instructor_remarks = $assessment->instructor_remarks;
            $this->observations = $assessment->observations;
            $this->is_passed = $assessment->is_passed;
            $this->failure_reason = $assessment->failure_reason ?? '';
            
            // If empty (newly created by beginPDC), initialize defaults
            if (empty($this->pre_drive_checklist)) $this->initDefaults();
        } else {
            $this->initDefaults();
        }

        $this->calculateResults();
    }

    private function initDefaults()
    {
        $this->pre_drive_checklist = [
            '2.1 Check/Switch, Lights and Windshield Wiper.' => false,
            '2.2 Adjust Mirrors ( Rear view mirror and Side mirror)' => false,
            '2.3 Use Seatbelt/Helmet' => false,
            '2.4 Check doors' => false,
            '2.5 Disengage clutch when starting engine' => false,
            '2.6 Disengage emergency/parking brake before moving' => false,
        ];

        $this->immediate_fails = [
            '1.1 Failure to stop at RED SIGNAL, STOP SIGN or STOP LINE.' => false,
            '1.2 Colliding with any mobile or immobile object, or curb.' => false,
            '1.3 Triggering Intervention of the instructor or causing any other motorist to swerve to avoid collision.' => false,
            '1.4 Not Adhering to Traffic Sign (entering No-Entry Sign or Yellow Box Junction) or instructor\'s directions.' => false,
            '1.5 Lack of Vehicle Control/Dangerous maneuver.' => false,
            '1.6 Failure to ensure the road, lane or roundabout is clear before entering/proceeding.' => false,
            '1.7 Speed/Lane violation.' => false,
        ];

        $this->driving_skills = [
            '3.1 Steering: Position of hands, hand grip, smoothness' => 3,
            '3.2 Engine Control: Smooth start up' => 3,
            'Use of gears' => 3,
            'Use clutch' => 3,
            'Use of accelerator' => 3,
            '3.3 Use of Brakes: Apply smooth braking' => 3,
            'Reactions to Hazards' => 3,
            'Vehicle/Motor turning' => 3,
            '3.4 Speed Control: Observance of speed limit' => 3,
            'Observance of traffic rules' => 3,
            'Road signs knowledge' => 3,
            '3.5 Maneuvering: Turning left/right/U-turn' => 3,
            'Takes proper lane and Signal intention' => 3,
            'Use of Hand and Light Signal' => 3,
            'Backing/Parking: Control of the Vehicle' => 3,
            'Correct Spacing/Distancing' => 3,
            'Number of Attempts' => 3,
        ];

        $this->traffic_rules = [
            '3.1 Right of Way: For other Vehicles' => 3,
            'For Pedestrian, Emergency and Bicyclist' => 3,
            '3.2 Road signs knowledge' => 3,
            'In Changing Lanes & Yielding' => 3,
            'While approaching Intersection' => 3,
            'In Passing/Being Passed' => 3,
            '3.3 Stop Lights/Signals & Others:' => 3,
            'Obey Traffic Signs' => 3,
            '3.4 Position After Stopping' => 3,
            'Making Full Stop when Necessary' => 3,
            'Anticipating before Signal Changes' => 3,
            '3.5 Exercise due care for Pedestrian' => 3,
        ];
    }

    public function updated($propertyName)
    {
        $this->calculateResults();
    }

    public function calculateResults()
    {
        $this->pre_drive_errors = collect($this->pre_drive_checklist)->filter(fn($val) => $val)->count();
        $this->immediate_fail_count = collect($this->immediate_fails)->filter(fn($val) => $val)->count();
        
        $poorSkills = collect($this->driving_skills)->filter(fn($val) => $val == 1)->count();
        $poorRules = collect($this->traffic_rules)->filter(fn($val) => $val == 1)->count();
        $this->total_poor_marks = $poorSkills + $poorRules;

        // Auto-detect failure based on rules, but only if they haven't explicitly set it to pass yet
        if ($this->pre_drive_errors > 0 || $this->immediate_fail_count > 0 || $this->total_poor_marks > 15) {
            // Note: We don't force is_passed to false here because they might still be in progress
            // However, for the final "Recommended Result" UI, we can use this logic
        }

        if ($this->recommended_for_additional_driving) $this->learner_type = '4';
    }

    #[Computed]
    public function sessionHistory()
    {
        return BookingSession::where('enrollment_id', $this->enrollment->id)
            ->where('type', 'driving')
            ->where('status', 'completed')
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function currentProgress()
    {
        $completed = $this->enrollment->pdc_hours_completed ?? 0;
        $required = $this->enrollment->pdc_hours_required ?? 8;
        return [
            'completed' => $completed,
            'required' => $required,
            'percent' => min(100, round(($completed / $required) * 100))
        ];
    }

    public function saveAndEndSession()
    {
        $this->validate();
        $this->saveData(false);
        session()->flash('success', 'Progress saved and session ended.');
        return $this->redirect(route('instructor.my-students'), navigate: true);
    }

    public function completeAssessment()
    {
        $this->validate([
            'is_passed' => 'required|boolean',
        ]);
        
        if ($this->is_passed === null) {
            $this->addError('is_passed', 'Please mark the student as PASSED or FAILED for final completion.');
            return;
        }

        $this->saveData(true);
        session()->flash('success', 'Full assessment completed and signed off.');
        return $this->redirect(route('instructor.my-students'), navigate: true);
    }

    private function saveData(bool $isFinal)
    {
        DB::transaction(function () use ($isFinal) {
            $assessment = Assessment::updateOrCreate(
                ['enrollment_id' => $this->enrollment->id, 'assessment_type' => 'practical'],
                [
                    'student_id' => $this->enrollment->student_id,
                    'instructor_id' => $this->enrollment->instructor_id,
                    'booking_session_id' => $this->bookingSession->id,
                    'assessment_date' => $this->assessment_date,
                    'pre_drive_checklist' => $this->pre_drive_checklist,
                    'pre_drive_errors' => $this->pre_drive_errors,
                    'immediate_fails' => $this->immediate_fails,
                    'immediate_fail_count' => $this->immediate_fail_count,
                    'driving_skills' => $this->driving_skills,
                    'driving_skills_rating' => $this->total_poor_marks,
                    'traffic_rules' => $this->traffic_rules,
                    'learner_type' => match($this->learner_type) {
                        '1' => 'slow',
                        '2' => 'fast',
                        '3' => 'hesitant',
                        default => 'fast'
                    },
                    'recommended_for_additional_driving' => ($this->learner_type === '4' || $this->recommended_for_additional_driving),
                    'instructor_remarks' => $this->instructor_remarks,
                    'observations' => $this->observations,
                    'is_passed' => $isFinal ? $this->is_passed : null,
                    'failure_reason' => (!$isFinal || $this->is_passed) ? null : $this->failure_reason,
                ]
            );

            $now = now();
            $durationHours = round($this->bookingSession->start_time->diffInMinutes($now) / 60, 2);

            $this->bookingSession->update([
                'end_time' => $now,
                'status' => 'completed',
                'is_passed' => $isFinal ? $this->is_passed : null,
                'score' => $isFinal ? ($this->is_passed ? 100 : 0) : null,
            ]);

            $newPdcHours = min($this->enrollment->pdc_hours_required, ($this->enrollment->pdc_hours_completed ?? 0) + $durationHours);
            $pdcStatus = ($isFinal || $newPdcHours >= $this->enrollment->pdc_hours_required) ? 'completed' : 'in_progress';
            
            $totalRequired = ($this->enrollment->tdc_hours_required ?? 0) + ($this->enrollment->pdc_hours_required ?? 0);
            $totalCompleted = ($this->enrollment->tdc_hours_completed ?? 0) + $newPdcHours;
            $progressPercent = $totalRequired > 0 ? ($totalCompleted / $totalRequired) * 100 : 100;

            $this->enrollment->update([
                'pdc_hours_completed' => $newPdcHours,
                'pdc_status' => $pdcStatus,
                'progress_percent' => round($progressPercent, 2),
                'status' => ($isFinal || $progressPercent >= 100) ? 'completed' : 'active',
            ]);

            // Metrics
            $metric = \App\Models\InstructorMetric::firstOrCreate(
                ['instructor_id' => $this->enrollment->instructor_id, 'metric_month' => $now->copy()->startOfMonth()->format('Y-m-d')],
                ['total_sessions' => 0, 'completed_sessions' => 0, 'total_hours' => 0, 'avg_rating' => 0, 'students_taught' => 0, 'students_passed' => 0, 'pass_rate' => 0]
            );
            $metric->increment('total_sessions');
            $metric->increment('completed_sessions');
            $metric->increment('total_hours', $durationHours);
            if ($isFinal && $this->is_passed) $metric->increment('students_passed');
        });
    }
};