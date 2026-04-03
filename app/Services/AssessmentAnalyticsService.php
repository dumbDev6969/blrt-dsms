<?php

namespace App\Services;

use App\Models\Assessment;
use Illuminate\Support\Collection;

class AssessmentAnalyticsService
{
    /**
     * Labels for Pre-Drive Checklist items.
     */
    public function preDriveLabels(): array
    {
        return [
            'pd_1' => 'Check/Switch, Lights and Windshield Wiper',
            'pd_2' => 'Adjust Mirrors',
            'pd_3' => 'Use Seatbelt/Helmet',
            'pd_4' => 'Check doors',
            'pd_5' => 'Disengage clutch when starting engine',
            'pd_6' => 'Disengage emergency/parking brake',
        ];
    }

    /**
     * Labels for Immediate Fail items.
     */
    public function immediateFailLabels(): array
    {
        return [
            'if_1' => 'Stopping at Red Signals/Signs',
            'if_2' => 'Collisions',
            'if_3' => 'Instructor Intervention',
            'if_4' => 'Traffic Sign Adherence',
            'if_5' => 'Vehicle Control',
            'if_6' => 'Clear Road Awareness',
            'if_7' => 'Speed/Lane Discipline',
        ];
    }

    /**
     * Labels for Driving Skill items.
     */
    public function drivingSkillLabels(): array
    {
        return [
            'ds_1' => 'Steering & Hand Positioning',
            'ds_2' => 'Engine Control & Start up',
            'ds_3' => 'Gear Usage',
            'ds_4' => 'Clutch Usage',
            'ds_5' => 'Accelerator Usage',
            'ds_6' => 'Braking Smoothness',
            'ds_7' => 'Reaction to Hazards',
            'ds_8' => 'Vehicle Turning',
            'ds_9' => 'Speed Limit Observance',
            'ds_10' => 'Traffic Rule Observance',
            'ds_11' => 'Road Sign Knowledge',
            'ds_12' => 'Maneuvering (Turns)',
            'ds_13' => 'Lane Positioning & Signaling',
            'ds_14' => 'Hand & Light Signals',
            'ds_15' => 'Backing & Parking Control',
            'ds_16' => 'Correct Spacing',
            'ds_17' => 'Efficiency (Attempts)',
        ];
    }

    /**
     * Labels for Traffic Rule items.
     */
    public function trafficRuleLabels(): array
    {
        return [
            'tr_1' => 'Right of Way (Vehicles)',
            'tr_2' => 'Pedestrian & Cyclist Safety',
            'tr_3' => 'Road Sign Awareness',
            'tr_4' => 'Changing Lanes & Yielding',
            'tr_5' => 'Approaching Intersections',
            'tr_6' => 'Passing & Being Passed',
            'tr_7' => 'Stop Lights & Signals',
            'tr_8' => 'Obeying Traffic Signs',
            'tr_9' => 'Positioning After Stop',
            'tr_10' => 'Full Stops When Necessary',
            'tr_11' => 'Anticipation and Timing',
            'tr_12' => 'Pedestrian Care',
        ];
    }

    /**
     * Generate performance analytics for a given assessment.
     */
    public function generate(Assessment $assessment): array
    {
        $drivingSkills = collect($assessment->driving_skills ?? []);
        $trafficRules = collect($assessment->traffic_rules ?? []);
        $preDrive = collect($assessment->pre_drive_checklist ?? []);
        $immediateFails = collect($assessment->immediate_fails ?? []);

        //  Calculate Score (same as instructor logic)
        $ratedSkills = $drivingSkills->filter(fn($val) => !is_null($val));
        $ratedRules = $trafficRules->filter(fn($val) => !is_null($val));
        
        $drivingPoints = $ratedSkills->sum();
        $trafficPoints = $ratedRules->sum();
        
        $preDriveMax = count($this->preDriveLabels());
        $preDriveErrors = $preDrive->filter(fn($val) => $val === true)->count();
        $preDrivePoints = $preDriveMax - $preDriveErrors;
        
        $ratedCount = $ratedSkills->count() + $ratedRules->count();
        $maxPossible = ($ratedCount * 3) + $preDriveMax;
        
        $scoreValue = $maxPossible > 0 
            ? round((($drivingPoints + $trafficPoints + $preDrivePoints) / $maxPossible) * 100, 2)
            : 0;

        //  Breakdowns
        $allRatings = $ratedSkills->concat($ratedRules);
        $counts = [
            'good' => $allRatings->filter(fn($v) => (int)$v === 3)->count(),
            'fair' => $allRatings->filter(fn($v) => (int)$v === 2)->count(),
            'poor' => $allRatings->filter(fn($v) => (int)$v === 1)->count(),
        ];

        //  Narrative Insights
        $insights = $this->generateInsights($ratedSkills, $ratedRules, $preDriveErrors, $immediateFails);

        return [
            'score' => $scoreValue,
            'counts' => $counts,
            'pre_drive_errors' => $preDriveErrors,
            'immediate_fails' => $immediateFails->filter(fn($v) => $v === true)->count(),
            'insights' => $insights,
            'learner_type' => $assessment->learner_type,
            'is_passed' => $assessment->is_passed,
        ];
    }

    /**
     * Generate dynamic text insights based on ratings.
     */
    private function generateInsights(Collection $skills, Collection $rules, int $pdErrors, Collection $ifFails): array
    {
        $strengths = [];
        $improvements = [];
        $warnings = [];

        // Identify Strengths (Rating 3)
        $topSkillKeys = $skills->filter(fn($v) => (int)$v === 3)->keys();
        $topRuleKeys = $rules->filter(fn($v) => (int)$v === 3)->keys();
        
        $skillLabels = $this->drivingSkillLabels();
        $ruleLabels = $this->trafficRuleLabels();

        foreach ($topSkillKeys->union($topRuleKeys)->take(2) as $key) {
            $strengths[] = $skillLabels[$key] ?? $ruleLabels[$key] ?? 'General Driving';
        }

        // Identify Improvements (Rating 1 or 2)
        $needWorkKeys = $skills->concat($rules)->filter(fn($v) => (int)$v < 3)->sortBy('v')->keys();
        foreach ($needWorkKeys->take(2) as $key) {
            $improvements[] = $skillLabels[$key] ?? $ruleLabels[$key] ?? 'Road Awareness';
        }

        // Warnings
        if ($pdErrors > 0) {
            $warnings[] = "Review the Pre-Drive Checklist carefully before moving.";
        }
        if ($ifFails->filter(fn($v) => $v === true)->isNotEmpty()) {
            $warnings[] = "Avoid critical driving errors to ensure safety and passing marks.";
        }

        // Construct Narrative
        $narrative = "";
        if (count($strengths) > 0) {
            $narrative .= "You did great in " . implode(' and ', $strengths) . ". ";
        } else {
            $narrative .= "Focus on building your foundation in core driving routines. ";
        }

        if (count($improvements) > 0) {
            $narrative .= "Try to focus more on " . implode(' and ', $improvements) . " in your next session.";
        }

        return [
            'strengths' => $strengths,
            'improvements' => $improvements,
            'warnings' => $warnings,
            'narrative' => $narrative,
        ];
    }
}
