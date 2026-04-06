<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\InstructorPerformance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Flux\Flux;

new class extends Component
{
    public $evalTargetEnrollmentId = null;
    public $evalCriteria = [
        'teaching_quality' => 5,
        'communication' => 5,
        'punctuality' => 5,
        'professionalism' => 5
    ];
    public $evalFeedback = '';
    public $evalStrength = '';
    public $evalImprovement = '';

    #[Computed]
    public function pastEnrollments()
    {
        return Auth::user()->studentProfile?->enrollments()
            ->with(['course', 'instructorProfile.user', 'instructorPerformances'])
            ->where('status', 'completed')
            ->get() ?? collect();
    }

    public function openEvaluationModal($enrollmentId)
    {
        $this->evalTargetEnrollmentId = $enrollmentId;
        $this->evalCriteria = [
            'teaching_quality' => 5,
            'communication' => 5,
            'punctuality' => 5,
            'professionalism' => 5
        ];
        $this->evalFeedback = '';
        $this->evalStrength = '';
        $this->evalImprovement = '';

        Flux::modal('evaluate-instructor')->show();
    }

    public function submitEvaluation()
    {
        $this->validate([
            'evalFeedback' => 'required|min:10',
            'evalStrength' => 'nullable|string',
            'evalImprovement' => 'nullable|string',
        ]);

        $enrollment = Enrollment::findOrFail($this->evalTargetEnrollmentId);

        // Find the latest completed booking session for this enrollment
        $session = $enrollment->bookingSessions()
            ->whereIn('status', ['completed', 'scheduled']) // Taking available if no explicit completed session
            ->latest()
            ->first();

        InstructorPerformance::create([
            'instructor_id'        => $enrollment->instructor_id,
            'student_id'           => $enrollment->student_id,
            'enrollment_id'        => $enrollment->id,
            'booking_session_id'   => $session?->id,
            'rating'               => round(array_sum($this->evalCriteria) / count($this->evalCriteria)),
            'performance_criteria' => $this->evalCriteria,
            'feedback_comment'     => $this->evalFeedback,
            'areas_of_strength'    => $this->evalStrength,
            'areas_for_improvement' => $this->evalImprovement,
            'evaluation_date'      => now()->format('Y-m-d'),
        ]);

        Flux::modal('evaluate-instructor')->close();
        session()->flash('success', 'Instructor evaluation successfully submitted. Thank you for your feedback!');
    }
};
?>

<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" weight="bold" class="tracking-tight">Academic Records</flux:heading>
            <flux:text size="sm" class="text-slate-500 mt-1">Review your completed courses, certificates, and final achievements.</flux:text>
        </div>
        <flux:button href="{{ route('dashboard') }}" variant="subtle" icon="arrow-left" size="sm" wire:navigate>Back to Dashboard</flux:button>
    </div>

    <x-callout />

    @if ($this->pastEnrollments->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($this->pastEnrollments as $enrollment)
                <div class="relative group h-full">
                    <a href="{{ route('student.performance-analytics', $enrollment->id) }}" wire:navigate class="absolute inset-0 z-10"></a>
                    
                    <div class="p-6 md:p-8 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative z-20 pointer-events-none overflow-hidden transition-all group-hover:shadow-lg group-hover:border-emerald-500/50 group-hover:-translate-y-1 duration-300 h-full flex flex-col">
                        {{-- Decorative Background Glow --}}
                        <div class="absolute -right-20 -top-20 size-64 bg-emerald-500/10 dark:bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-colors duration-500"></div>

                        <div class="relative z-10 flex-1 flex flex-col">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                        <flux:icon icon="academic-cap" class="size-5" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg" class="font-bold tracking-tight text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 transition-colors">{{ $enrollment->course->title }}</flux:heading>
                                        <flux:text size="xs" class="text-slate-500 uppercase tracking-widest font-bold">Completed Course</flux:text>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 relative z-30 shrink-0 pointer-events-auto">
                                    @if($enrollment->instructorPerformances->isEmpty())
                                        <flux:button @click.stop wire:click.prevent="openEvaluationModal({{ $enrollment->id }})" variant="ghost" size="xs" icon="star" class="text-amber-600 dark:text-amber-400 font-bold hover:bg-amber-50 dark:hover:bg-amber-900/20">
                                            Evaluate Instructor
                                        </flux:button>
                                    @endif
                                    <flux:badge size="sm" color="emerald" variant="subtle" class="capitalize font-semibold">
                                        <flux:icon icon="check" class="size-3 mr-1" />
                                        {{ $enrollment->status }}
                                    </flux:badge>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
                                <div class="flex flex-col">
                                    <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">Final Grade</flux:text>
                                    <flux:text size="sm" weight="semibold" class="text-emerald-600 dark:text-emerald-400">{{ $enrollment->final_grade ?? 'Pass' }}</flux:text>
                                </div>
                                <div class="flex flex-col">
                                    <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold mb-1">Completion Date</flux:text>
                                    <flux:text size="sm" weight="semibold" class="text-slate-800 dark:text-slate-200">{{ $enrollment->updated_at?->format('F d, Y') ?? 'N/A' }}</flux:text>
                                </div>
                            </div>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-800/60">
                                @if($enrollment->instructorProfile)
                                    <div class="flex items-center gap-2.5">
                                        <div class="size-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ substr($enrollment->instructorProfile->user?->name ?? '?', 0, 2) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <flux:text size="min" class="text-slate-400 uppercase font-black tracking-tighter scale-[0.8] origin-left">Instructor</flux:text>
                                            <flux:text size="xs" weight="medium" class="text-slate-600 dark:text-slate-300 leading-none">{{ $enrollment->instructorProfile->user?->name ?? 'N/A' }}</flux:text>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center gap-3">
                                    <flux:text size="xs" class="font-mono text-slate-400 bg-slate-50 dark:bg-slate-800/80 px-2 py-0.5 rounded border border-slate-100 dark:border-slate-800">{{ $enrollment->code }}</flux:text>
                                    <flux:icon icon="chevron-right" class="size-4 text-emerald-500 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-[70vh] text-center px-4">
            <div class="size-24 rounded-[2rem] bg-gradient-to-br from-slate-50 to-emerald-50 dark:from-slate-900/20 dark:to-emerald-900/20 flex items-center justify-center mb-8 border border-slate-100 dark:border-slate-800/50 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-0 bg-emerald-500/5 dark:bg-emerald-500/10 scale-0 group-hover:scale-100 transition-transform duration-700 rounded-full"></div>
                <flux:icon icon="magnifying-glass" class="size-12 text-slate-400 dark:text-slate-500 relative z-10 group-hover:text-emerald-500 transition-colors duration-300" />
            </div>
            <flux:heading size="2xl" class="font-bold tracking-tight mb-2">No Records Found</flux:heading>
            <flux:text class="text-slate-500 max-w-md mx-auto leading-relaxed">
                You haven't completed any courses yet. Once you finish your enrolled training programs, your certificates and records will appear here for you to access.
            </flux:text>
            <div class="mt-10">
                <flux:button href="{{ route('dashboard') }}" variant="primary" class="rounded-full px-8 shadow-md shadow-blue-500/20">Return to Dashboard</flux:button>
            </div>
        </div>
    @endif

    {{-- Instructor Evaluation Modal --}}
    <flux:modal name="evaluate-instructor" class="sm:max-w-xl">
        <form wire:submit="submitEvaluation" class="space-y-8">
            <div>
                <flux:heading size="xl" weight="bold" class="tracking-tight mb-1">Evaluate Instructor</flux:heading>
                <flux:text size="sm" class="text-slate-500 leading-relaxed">Please share your feedback regarding your instructor's performance during this course.</flux:text>
            </div>

            <div class="space-y-6">
                {{-- Ratings Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:radio.group wire:model="evalCriteria.teaching_quality" label="Teaching Quality" variant="segmented" class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-1">
                            @foreach(range(1, 5) as $val)
                                <flux:radio value="{{ $val }}" label="{{ $val }}" />
                            @endforeach
                        </div>
                    </flux:radio.group>

                    <flux:radio.group wire:model="evalCriteria.communication" label="Communication" variant="segmented" class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-1">
                            @foreach(range(1, 5) as $val)
                                <flux:radio value="{{ $val }}" label="{{ $val }}" />
                            @endforeach
                        </div>
                    </flux:radio.group>

                    <flux:radio.group wire:model="evalCriteria.punctuality" label="Punctuality" variant="segmented" class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-1">
                            @foreach(range(1, 5) as $val)
                                <flux:radio value="{{ $val }}" label="{{ $val }}" />
                            @endforeach
                        </div>
                    </flux:radio.group>

                    <flux:radio.group wire:model="evalCriteria.professionalism" label="Professionalism" variant="segmented" class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-1">
                            @foreach(range(1, 5) as $val)
                                <flux:radio value="{{ $val }}" label="{{ $val }}" />
                            @endforeach
                        </div>
                    </flux:radio.group>
                </div>

                <flux:textarea wire:model="evalFeedback" label="Detailed Feedback" placeholder="Tell us more about your experience..." rows="3" required />
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:textarea wire:model="evalStrength" label="Key Strengths" placeholder="What did they do well?" rows="2" />
                    <flux:textarea wire:model="evalImprovement" label="Areas for Improvement" placeholder="What could be better?" rows="2" />
                </div>
            </div>

            <div class="flex gap-2 justify-end pt-6 border-t border-slate-100 dark:border-slate-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Submit Evaluation</flux:button>
            </div>
        </form>
    </flux:modal>
</div>