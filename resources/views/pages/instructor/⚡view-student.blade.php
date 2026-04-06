<?php

use Livewire\Component;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public Enrollment $enrollment;

    public $grades = [];
    public $results = [];
    public $remarks = [];

    public function mount(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->load(['studentProfile.user', 'course', 'instructorProfile.user']);
    }

    public function loadExistingGrade($enrollmentId)
    {
        $enrollment = Enrollment::find($enrollmentId);
        if ($enrollment) {
            $this->grades[$enrollmentId] = $enrollment->final_grade;
            $this->results[$enrollmentId] = $enrollment->final_result;
            $this->remarks[$enrollmentId] = $enrollment->remarks;
        }
    }

    public function submitGrade($enrollmentId)
    {
        if (Auth::user()->instructorProfile->isPending()) {
            return;
        }

        $this->validate([
            "grades.$enrollmentId" => 'nullable|numeric|min:0|max:100',
            "results.$enrollmentId" => 'required|in:pass,fail',
            "remarks.$enrollmentId" => 'nullable|string',
        ]);

        app(\App\Services\InstructorGradingService::class)->submitGrade($enrollmentId, [
            'grade' => $this->grades[$enrollmentId],
            'result' => $this->results[$enrollmentId],
            'remarks' => $this->remarks[$enrollmentId],
        ]);

        $this->enrollment->refresh();
        session()->flash('success', 'Student grade submitted successfully.');
        Flux::modal("score-{$enrollmentId}")->close();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100 p-1 sm:p-0">

    <x-callout />

    {{-- Header & Navigation --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('instructor.my-students')"
                    wire:navigate>Back to Students</flux:button>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mt-2">
                <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Student Details:
                    {{ $enrollment->studentProfile->user->name ?? 'Unknown Student' }}</flux:heading>
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'amber', 'label' => 'Pending'],
                        'active' => ['color' => 'emerald', 'label' => 'Active'],
                        'completed' => ['color' => 'blue', 'label' => 'Completed'],
                        'dropped' => ['color' => 'red', 'label' => 'Dropped'],
                    ];
                    $config = $statusConfig[$enrollment->status] ?? ['color' => 'zinc', 'label' => $enrollment->status];
                @endphp
                <flux:badge :color="$config['color']" variant="subtle" size="sm" class="capitalize w-fit">
                    {{ $config['label'] }}</flux:badge>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($enrollment->course->type === 'theoretical' && in_array($enrollment->status, ['active', 'completed']))
                <flux:modal.trigger name="score-{{ $enrollment->id }}">
                    <flux:button variant="ghost" icon="academic-cap" wire:click="loadExistingGrade({{ $enrollment->id }})" :disabled="Auth::user()->instructorProfile->isPending()">
                        {{ $enrollment->status === 'completed' ? 'Update grade' : 'Grade student' }}
                    </flux:button>
                </flux:modal.trigger>
            @endif

            <flux:button variant="primary" icon="pencil-square" :disabled="Auth::user()->instructorProfile->isPending()">Update Progress</flux:button>
            <flux:dropdown>
                <flux:button variant="ghost" icon="ellipsis-horizontal" :disabled="Auth::user()->instructorProfile->isPending()" />
                <flux:menu>
                    <flux:menu.item icon="calendar-days">Schedule Session</flux:menu.item>
                    <flux:menu.item icon="chat-bubble-left-right">Message Student</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- Main Dashboard Layout --}}
    <x-student-enrollment-details :enrollment="$enrollment" />

    {{-- Grading Modal --}}
    @if ($enrollment->course->type === 'theoretical' && in_array($enrollment->status, ['active', 'completed']))
        <x-instructor.grading-modal :enrollment="$enrollment" />
    @endif
</div>
