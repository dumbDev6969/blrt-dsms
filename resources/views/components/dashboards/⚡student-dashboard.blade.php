<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\Course;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
new class extends Component {
    protected array $blockingEnrollmentStatuses = ['active', 'pending', 'waiting_list'];

    // Check if the student uploaded at least one document
    #[Computed]
    public function hasDocument()
    {
        if (Document::where('user_id', Auth::user()->id)->exists()) {
            return true;
        }
    }

    #[Computed]
    public function isComplete()
    {
        if (
            Document::where('user_id', Auth::user()->id)
                ->where('status', 'approved')
                ->exists()
        ) {
            return true;
        }
    }

    #[Computed]
    public function courses()
    {
        return Course::query()->select('id', 'title', 'description', 'price', 'type')->get();
    }

    //Get the active current enrollment fo the student
    #[Computed]
    public function currentEnrollment()
    {
        return Auth::user()
        ->studentProfile
        ->enrollments()
        ->where('status', 'active')
        ->first();
    }

    #[Computed]
    public function hasBlockingEnrollment()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return false;
        }

        return $studentProfile->enrollments()
            ->whereIn('status', $this->blockingEnrollmentStatuses)
            ->exists();
    }

    #[Computed]
    public function blockingEnrollmentStatus()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return null;
        }

        $statuses = $studentProfile->enrollments()
            ->whereIn('status', $this->blockingEnrollmentStatuses)
            ->pluck('status');

        foreach (['pending', 'active', 'waiting_list'] as $priorityStatus) {
            if ($statuses->contains($priorityStatus)) {
                return $priorityStatus;
            }
        }

        return null;
    }

    #[Computed]
    public function hasPendingEnrollmentForm()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return false;
        }

        return $studentProfile->enrollmentForms()
            ->where('status', 'submitted')
            ->exists();
    }

    #[Computed]
    public function isEnrollmentBlocked()
    {
        return $this->hasBlockingEnrollment || $this->hasPendingEnrollmentForm;
    }

    #[Computed]
    public function enrollmentBlockReason()
    {
        if ($this->blockingEnrollmentStatus === 'pending') {
            return 'Your enrollment is currently pending.';
        }

        if ($this->blockingEnrollmentStatus === 'active') {
            return 'You already have an active enrollment.';
        }

        if ($this->blockingEnrollmentStatus === 'waiting_list') {
            return 'You are currently on the waiting list for enrollment.';
        }

        if ($this->hasPendingEnrollmentForm) {
            return 'Your submitted enrollment form is still under review.';
        }

        return null;
    }

    #[Computed]
    public function hasCompletedTdc()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return false;
        }

        // Student is considered done with TDC if they have an enrollment with tdc_status = 'completed'
        // or a theoretical course enrollment that is marked completed/active.
        // We will rely on tdc_status = 'completed'.
        return $studentProfile->enrollments()
            ->where('tdc_status', 'completed')
            ->exists();
    }

    #[Computed]
    public function progressData()
    {
        $enrollment = $this->currentEnrollment;

        if (!$enrollment) {
            return [
                'percent' => 0,
                'milestone' => 'No active enrollment',
            ];
        }

        $course = $enrollment->course;
        $isTdc = $course->type === 'theoretical';

        $percent = (int) $enrollment->progress_percent;

        if ($isTdc) {
            $completed = (float) $enrollment->tdc_hours_completed;
            $required = (float) $enrollment->tdc_hours_required;
            $milestone = $completed >= $required 
                ? 'Next Milestone: TDC Final Exam' 
                : "Hours Completed: {$completed} / {$required}";
        } else {
            $completed = (float) $enrollment->pdc_hours_completed;
            $required = (float) $enrollment->pdc_hours_required;
            $milestone = $completed >= $required 
                ? 'Next Milestone: Practical Exam' 
                : "Driving Hours: {$completed} / {$required}";
        }

        return [
            'percent' => $percent,
            'milestone' => $milestone,
        ];
    }

    #[Computed]
    public function completedEnrollments()
    {
        $studentProfile = Auth::user()->studentProfile;

        if (!$studentProfile) {
            return collect();
        }

        return $studentProfile->enrollments()
            ->where('status', 'completed')
            ->with('course')
            ->get();
    }
};
?>


{{-- People find pleasure in different ways. I find it in keeping my mind clear. - Marcus Aurelius --}}

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <flux:callout icon="exclamation-triangle" variant="warning" class="w-full">
        @if ($this->isComplete)
            {{-- STATE 1: COMPLETE --}}
            <flux:callout.heading class="text-green-600">
                Documents Complete
            </flux:callout.heading>
            <flux:callout.text>
                You have submitted all required documents. We are now verifying your application.
            </flux:callout.text>
        @elseif ($this->hasDocument())
            {{-- STATE 2: INCOMPLETE (User has started, but not finished) --}}
            <flux:callout.heading class="text-yellow-600">
                Your documents are incomplete
            </flux:callout.heading>
            <flux:callout.text>
                Please upload the remaining documents to proceed with your driving journey.
            </flux:callout.text>
        @else
            {{-- STATE 3: EMPTY (User hasn't started) --}}
            <flux:callout.heading>
                You haven't uploaded documents yet
            </flux:callout.heading>
            <flux:callout.text>
                Upload your documents to start your driving journey.
            </flux:callout.text>
        @endif

        <x-slot name="actions">
            <flux:button size="sm" href="{{ route('document.upload') }}" wire:navigate>
                Upload Documents
            </flux:button>
        </x-slot>
    </flux:callout>
    {{-- Top Stats / Status Grid --}}
    <div class="grid auto-rows-min gap-6 md:grid-cols-3">

        {{-- CARD 1: COMPLIANCE STATUS --}}
        <x-kpi-cards
            label="Account Status"
            sublabel="Compliance & Requirements"
            icon="check-circle"
            color="emerald"
            icon-position="left"
        >
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <flux:text size="sm">Profile Completion</flux:text>
                    <flux:badge color="emerald" variant="subtle" size="sm">100% Complete</flux:badge>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <flux:text size="sm">Documents</flux:text>
                    @if ($this->isComplete)
                        <flux:badge variant="subtle" size="sm">Under review</flux:badge>
                    @elseif ($this->hasDocument)
                        <flux:badge color="amber" variant="subtle" size="sm">Incomplete</flux:badge>
                    @else
                        <flux:badge color="red" variant="subtle" size="sm">No documents yet</flux:badge>
                    @endif
                </div>
            </div>
        </x-kpi-cards>

        {{-- CARD 2: CURRENT ENROLLMENT --}}
        <x-kpi-cards
            label="Current Enrollment"
            sublabel="Active Courses"
            icon="academic-cap"
            color="blue"
            icon-position="left"
        >
            @if ($this->currentEnrollment)
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <flux:text size="sm">Course</flux:text>
                        <flux:text weight="medium" class="truncate max-w-[150px] text-right" title="{{ $this->currentEnrollment->course->title }}">
                            {{ $this->currentEnrollment->course->title }}
                        </flux:text>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <flux:text size="sm">Status</flux:text>
                        <flux:badge color="emerald" variant="subtle" size="sm" class="capitalize">
                            {{ $this->currentEnrollment->status }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <flux:text size="sm">Code</flux:text>
                        <flux:text color="blue" weight="bold" class="font-mono text-xs bg-blue-50 dark:bg-blue-900/20 px-1.5 py-0.5 rounded">
                            {{ $this->currentEnrollment->code }}
                        </flux:text>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-20 text-center">
                    <flux:text size="sm" weight="medium">No active courses</flux:text>
                    <flux:text size="xs" class="mt-1">Select a course below to begin.</flux:text>
                </div>
            @endif
        </x-kpi-cards>

        {{-- CARD 3: COURSE PROGRESS --}}
        @if ($this->currentEnrollment)
            <a href="{{ route('student.performance-analytics', $this->currentEnrollment->id) }}" wire:navigate class="group block hover:no-underline translate-y-0 hover:-translate-y-1 transition-all duration-300">
                <x-kpi-cards
                    label="Course Progress"
                    sublabel="Overall Completion"
                    :value="$this->progressData['percent'] . '%'"
                    icon="chart-bar"
                    color="purple"
                    icon-position="left"
                    class="group-hover:border-[var(--color-accent)] group-hover:shadow-md transition-all duration-300"
                >
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ $this->progressData['percent'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <flux:text size="xs" class="text-slate-500">{{ $this->progressData['milestone'] }}</flux:text>
                        <div class="flex items-center gap-1 text-[var(--color-accent)] opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <flux:text size="xs" weight="medium" class="text-[var(--color-accent)]">Analyze</flux:text>
                            <flux:icon icon="chevron-right" class="size-3" />
                        </div>
                    </div>
                </x-kpi-cards>
            </a>
        @else
            <x-kpi-cards
                label="Course Progress"
                sublabel="Overall Completion"
                :value="$this->progressData['percent'] . '%'"
                icon="chart-bar"
                color="purple"
                icon-position="left"
            >
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ $this->progressData['percent'] }}%"></div>
                </div>
                <flux:text size="xs" class="text-slate-500 mt-3">{{ $this->progressData['milestone'] }}</flux:text>
            </x-kpi-cards>
        @endif
    </div>

    {{-- Bottom Section: COURSE CATALOG --}}
    <div
        class="relative h-full flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-8 shadow-sm">
        <x-courses :is-complete="$this->isComplete" :is-enroll-blocked="$this->isEnrollmentBlocked" :enroll-block-reason="$this->enrollmentBlockReason" :has-completed-tdc="$this->hasCompletedTdc" />

        <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
            <div class="mb-6">
                <flux:heading size="lg" class="font-bold text-slate-900 dark:text-slate-100">Your Roadmap to a Driver's License</flux:heading>
                <flux:text size="sm" class="text-slate-500 dark:text-slate-400">Track your progress from student permit to non-professional license.</flux:text>
            </div>

            {{-- Roadmap Container --}}
            <div class="relative">
                {{-- Connecting Line --}}
                <div
                    class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 dark:bg-slate-800 -translate-y-1/2 rounded-full hidden md:block">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">

                    @php
                        $tdcCompleted = $this->completedEnrollments->firstWhere(fn ($e) => $e->course->type === 'theoretical');
                        $isTdcActive = optional($this->currentEnrollment)->course?->type === 'theoretical';
                        $tdcOpacity = ($tdcCompleted || $isTdcActive) ? 'opacity-100' : 'opacity-60';
                    @endphp
                    {{-- Step 1: TDC --}}
                    <div class="relative group {{ $tdcOpacity }}">
                        <div
                            class="flex flex-col items-center text-center p-4 bg-white dark:bg-slate-900 rounded-xl border-2 {{ $isTdcActive ? 'border-[var(--color-accent)]' : 'border-slate-100 dark:border-slate-800' }} shadow-sm z-10 relative">
                            <div
                                class="flex items-center justify-center size-10 rounded-full {{ $tdcCompleted ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-[var(--color-accent)]' }} mb-3">
                                <flux:icon icon="{{ $tdcCompleted ? 'check-circle' : 'book-open' }}" class="size-5" />
                            </div>
                            <flux:heading size="sm" class="font-bold text-slate-900 dark:text-slate-100">1. Theoretical (TDC)</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">15-hr Seminar</flux:text>
                            
                            @if ($tdcCompleted)
                                <flux:badge color="{{ strtolower($tdcCompleted->final_result) === 'passed' ? 'green' : 'red' }}" size="sm" class="mt-2">
                                    {{ $tdcCompleted->final_result }} - {{ (int)$tdcCompleted->final_grade }}%
                                </flux:badge>
                            @elseif ($isTdcActive)
                                <flux:badge color="blue" size="sm" class="mt-2">
                                    You are here
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Step 2: Student Permit --}}
                    <div class="relative group opacity-60">
                        <div
                            class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                            <div
                                class="flex items-center justify-center size-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 mb-3">
                                <flux:icon icon="identification" class="size-5" />
                            </div>
                            <flux:heading size="sm" class="font-semibold text-slate-700 dark:text-slate-300">2. Student Permit</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Apply at LTO</flux:text>
                        </div>
                    </div>

                    @php
                        $pdcCompleted = $this->completedEnrollments->firstWhere(fn ($e) => $e->course->type === 'practical');
                        $isPdcActive = optional($this->currentEnrollment)->course?->type === 'practical';
                        $pdcOpacity = ($pdcCompleted || $isPdcActive) ? 'opacity-100' : 'opacity-60';
                    @endphp
                    {{-- Step 3: PDC --}}
                    <div class="relative group {{ $pdcOpacity }}">
                        <div
                            class="flex flex-col items-center text-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 {{ $isPdcActive ? 'border-2 border-[var(--color-accent)]' : '' }} z-10 relative">
                            <div
                                class="flex items-center justify-center size-10 rounded-full {{ $pdcCompleted ? 'bg-green-50 text-green-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} mb-3">
                                <flux:icon icon="{{ $pdcCompleted ? 'check-circle' : 'truck' }}" class="size-5" />
                            </div>
                            <flux:heading size="sm" class="font-semibold text-slate-700 dark:text-slate-300">3. Practical (PDC)</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">8-hr Driving</flux:text>

                            @if ($pdcCompleted)
                                <flux:badge color="{{ strtolower($pdcCompleted->final_result) === 'passed' ? 'green' : 'red' }}" size="sm" class="mt-2">
                                    {{ $pdcCompleted->final_result }} - {{ (int)$pdcCompleted->final_grade }}%
                                </flux:badge>
                            @elseif ($isPdcActive)
                                <flux:badge color="blue" size="sm" class="mt-2">
                                    You are here
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Step 4: License --}}
                    <div class="relative group opacity-60">
                        <div
                            class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                            <div
                                class="flex items-center justify-center size-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 mb-3">
                                <flux:icon icon="star" class="size-5" />
                            </div>
                            <flux:heading size="sm" class="font-semibold text-slate-700 dark:text-slate-300">4. Driver's License</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Final Exam</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
