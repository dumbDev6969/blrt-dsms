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
    
    // Additional tracking
    public $session_kms_driven = 0;

    protected $rules = [
        'assessment_type' => 'required',
        'assessment_date' => 'required|date',
        'learner_type' => 'required',
        'session_kms_driven' => 'required|numeric|min:0',
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
            
            // Safety: If keys are in the old format (descriptive strings), reset to defaults
            // This prevents desync between DB data and the new safe slug-based logic
            $firstKey = !empty($this->pre_drive_checklist) ? array_key_first($this->pre_drive_checklist) : null;
            if ($firstKey && !str_starts_with($firstKey, 'pd_')) {
                $this->initDefaults();
            }

            // If empty (newly created by beginPDC), initialize defaults
            if (empty($this->pre_drive_checklist)) $this->initDefaults();
        } else {
            $this->initDefaults();
        }

        $this->calculateResults();
    }

    #[Computed]
    public function preDriveLabels()
    {
        return [
            'pd_1' => '2.1 Check/Switch, Lights and Windshield Wiper.',
            'pd_2' => '2.2 Adjust Mirrors ( Rear view mirror and Side mirror)',
            'pd_3' => '2.3 Use Seatbelt/Helmet',
            'pd_4' => '2.4 Check doors',
            'pd_5' => '2.5 Disengage clutch when starting engine',
            'pd_6' => '2.6 Disengage emergency/parking brake before moving',
        ];
    }

    #[Computed]
    public function immediateFailLabels()
    {
        return [
            'if_1' => '1.1 Failure to stop at RED SIGNAL, STOP SIGN or STOP LINE.',
            'if_2' => '1.2 Colliding with any mobile or immobile object, or curb.',
            'if_3' => '1.3 Triggering Intervention of the instructor or causing any other motorist to swerve to avoid collision.',
            'if_4' => '1.4 Not Adhering to Traffic Sign (entering No-Entry Sign or Yellow Box Junction) or instructor\'s directions.',
            'if_5' => '1.5 Lack of Vehicle Control/Dangerous maneuver.',
            'if_6' => '1.6 Failure to ensure the road, lane or roundabout is clear before entering/proceeding.',
            'if_7' => '1.7 Speed/Lane violation.',
        ];
    }

    #[Computed]
    public function drivingSkillLabels()
    {
        return [
            'ds_1' => '3.1 Steering: Position of hands, hand grip, smoothness',
            'ds_2' => '3.2 Engine Control: Smooth start up',
            'ds_3' => 'Use of gears',
            'ds_4' => 'Use clutch',
            'ds_5' => 'Use of accelerator',
            'ds_6' => '3.3 Use of Brakes: Apply smooth braking',
            'ds_7' => 'Reactions to Hazards',
            'ds_8' => 'Vehicle/Motor turning',
            'ds_9' => '3.4 Speed Control: Observance of speed limit',
            'ds_10' => 'Observance of traffic rules',
            'ds_11' => 'Road signs knowledge',
            'ds_12' => '3.5 Maneuvering: Turning left/right/U-turn',
            'ds_13' => 'Takes proper lane and Signal intention',
            'ds_14' => 'Use of Hand and Light Signal',
            'ds_15' => 'Backing/Parking: Control of the Vehicle',
            'ds_16' => 'Correct Spacing/Distancing',
            'ds_17' => 'Number of Attempts',
        ];
    }

    #[Computed]
    public function trafficRuleLabels()
    {
        return [
            'tr_1' => '3.1 Right of Way: For other Vehicles',
            'tr_2' => 'For Pedestrian, Emergency and Bicyclist',
            'tr_3' => '3.2 Road signs knowledge',
            'tr_4' => 'In Changing Lanes & Yielding',
            'tr_5' => 'While approaching Intersection',
            'tr_6' => 'In Passing/Being Passed',
            'tr_7' => '3.3 Stop Lights/Signals & Others:',
            'tr_8' => 'Obey Traffic Signs',
            'tr_9' => '3.4 Position After Stopping',
            'tr_10' => 'Making Full Stop when Necessary',
            'tr_11' => 'Anticipating before Signal Changes',
            'tr_12' => '3.5 Exercise due care for Pedestrian',
        ];
    }

    private function initDefaults()
    {
        $this->pre_drive_checklist = collect($this->preDriveLabels())->mapWithKeys(fn($v, $k) => [$k => false])->toArray();
        $this->immediate_fails = collect($this->immediateFailLabels())->mapWithKeys(fn($v, $k) => [$k => false])->toArray();
        $this->driving_skills = collect($this->drivingSkillLabels())->mapWithKeys(fn($v, $k) => [$k => null])->toArray();
        $this->traffic_rules = collect($this->trafficRuleLabels())->mapWithKeys(fn($v, $k) => [$k => null])->toArray();
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
    public function finalScore()
    {
        // Only count items that have actually been rated (non-null)
        $ratedSkills = collect($this->driving_skills)->filter(fn($val) => !is_null($val));
        $ratedRules = collect($this->traffic_rules)->filter(fn($val) => !is_null($val));
        
        $drivingPoints = $ratedSkills->sum();
        $trafficPoints = $ratedRules->sum();
        
        // Pre-drive Checklist: checked = error, so perfect = max points
        $preDriveMax = count($this->preDriveLabels());
        $preDrivePoints = $preDriveMax - $this->pre_drive_errors;
        
        // Denominator only counts rated items (each worth max 3) plus pre-drive
        $ratedCount = $ratedSkills->count() + $ratedRules->count();
        $maxPossible = ($ratedCount * 3) + $preDriveMax;
        
        if ($maxPossible <= 0) return 0;
        
        $score = (($drivingPoints + $trafficPoints + $preDrivePoints) / $maxPossible) * 100;
        
        return round($score, 2);
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

            $updateData = [
                'pdc_hours_completed' => $newPdcHours,
                'pdc_kms_driven' => ($this->enrollment->pdc_kms_driven ?? 0) + floatval($this->session_kms_driven),
                'pdc_status' => $pdcStatus,
                'progress_percent' => round($progressPercent, 2),
                'status' => ($isFinal || $progressPercent >= 100) ? 'completed' : 'active',
            ];
            
            if ($isFinal) {
                $updateData['final_grade'] = $this->finalScore;
                $updateData['final_result'] = $this->is_passed ? 'pass' : 'fail';
            }

            $this->enrollment->update($updateData);

            // Metrics Update using Service
            $metricService = app(\App\Services\InstructorMetricService::class);
            $metricService->recordSessionCompletion($this->enrollment->instructor_id, $durationHours);

            if ($isFinal) {
                $metricService->recordCourseCompletion($this->enrollment->instructor_id, $this->is_passed);
            }
        });
    }
};