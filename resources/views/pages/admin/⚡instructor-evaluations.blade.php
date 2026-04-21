<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\InstructorPerformance;
use App\Services\InstructorPerformanceService;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public InstructorProfile $instructor;

    public function mount(InstructorProfile $instructor)
    {
        $this->instructor = $instructor->load('user');
    }

    #[Computed]
    public function courses()
    {
        return \App\Models\Course::whereHas('enrollments', function($q) {
            $q->where('instructor_id', $this->instructor->id)
              ->whereHas('instructorPerformances');
        })->get();
    }

    #[Computed]
    public function individualEvaluations()
    {
        return InstructorPerformance::where('instructor_id', $this->instructor->id)
            ->with(['enrollment.course', 'studentProfile.user'])
            ->latest('evaluation_date')
            ->paginate(10);
    }

    #[Computed]
    public function overallStats()
    {
        $summary = app(InstructorPerformanceService::class)->getSummary($this->instructor->id);
        
        if (!$summary) {
            return [
                'avgRating' => 0,
                'totalReviews' => 0,
            ];
        }

        return [
            'avgRating' => $summary->avgRating,
            'totalReviews' => $summary->totalReviews,
        ];
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-10 rounded-xl font-sans text-slate-900 dark:text-slate-100 p-2 sm:p-0">
    <x-callout />

    {{-- Breadcrumbs & Header --}}
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('admin.instructor-performances')" wire:navigate>Back to Analytics</flux:button>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="flex items-center gap-5">
                <flux:avatar src="{{ $instructor->user->avatar_url ?? '' }}" :initials="$instructor->user->initials()" size="xl" class="ring-4 ring-emerald-100 dark:ring-emerald-900/30" />
                <div>
                    <flux:heading size="xl" class="text-3xl font-black tracking-tight">{{ $instructor->user->name }}</flux:heading>
                    <flux:text class="mt-1 font-mono uppercase tracking-widest text-slate-500">Full Performance Dossier • ID #{{ $instructor->id }}</flux:text>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-center px-4 border-r border-slate-100 dark:border-slate-800">
                    <flux:heading size="xl" weight="black" class="text-amber-500">{{ $this->overallStats['avgRating'] }}</flux:heading>
                    <flux:text size="xs" class="uppercase font-bold tracking-tighter">Avg Rating</flux:text>
                </div>
                <div class="text-center px-4">
                    <flux:heading size="xl" weight="black">{{ $this->overallStats['totalReviews'] }}</flux:heading>
                    <flux:text size="xs" class="uppercase font-bold tracking-tighter">Total Reviews</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 1: The Big Picture (Aggregated Course Performance) --}}
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <flux:icon icon="chart-bar" class="size-5 text-emerald-500" />
            <flux:heading size="lg" weight="bold">Course Performance Summaries</flux:heading>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            @forelse ($this->courses as $course)
                <livewire:instructor-performance-card 
                    :instructor="$instructor"
                    :courseId="$course->id"
                    :courseTitle="$course->title"
                    :courseCode="$course->code"
                    :courseType="strtoupper($course->type)"
                    :profileUrl="null"
                    :key="'instructor-' . $instructor->id . '-course-' . $course->id"
                />
            @empty
                <div class="col-span-full">
                    <x-empty-state 
                        variant="card" 
                        icon="chart-bar"
                        heading="No Course Summaries"
                        message="No course performance data available yet."
                    />
                </div>
            @endforelse
        </div>
    </div>

    {{-- Section 2: Detailed Review Feed (Individual Atomic Data) --}}
    <div class="space-y-6">
        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
            <flux:icon icon="chat-bubble-bottom-center-text" class="size-5 text-blue-500" />
            <flux:heading size="lg" weight="bold">Individual Student Evaluations</flux:heading>
        </div>

        <div class="space-y-6">
            @forelse ($this->individualEvaluations as $review)
                <div class="group relative p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        {{-- Left Side: Reviewer & Stars --}}
                        <div class="w-full md:w-64 shrink-0 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-400">?</div>
                                <div>
                                    <flux:heading size="sm" weight="bold">Anonymous Student</flux:heading>
                                    <flux:text size="xs">{{ $review->evaluation_date->translatedFormat('F j, Y') }}</flux:text>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-1 text-amber-500">
                                    @for ($i = 0; $i < 5; $i++)
                                        <flux:icon icon="star" variant="{{ $i < round($review->rating) ? 'solid' : 'outline' }}" class="size-4" />
                                    @endfor
                                    <flux:text size="sm" weight="black" class="ml-1 text-slate-900 dark:text-white">{{ number_format($review->rating, 1) }}</flux:text>
                                </div>
                                <flux:badge size="sm" color="zinc" variant="subtle" class="font-bold tracking-widest">{{ $review->enrollment->course->code }}</flux:badge>
                            </div>
                        </div>

                        {{-- Middle/Right: Comments & Detailed Criteria --}}
                        <div class="flex-1 space-y-6">
                            @if($review->feedback_comment)
                                <div class="relative">
                                    <flux:icon icon="chat-bubble-bottom-center-text" class="absolute -left-6 -top-2 size-4 text-slate-200 dark:text-slate-700" />
                                    <flux:text size="base" class="italic text-slate-700 dark:text-slate-300 leading-relaxed font-medium">
                                        "{{ $review->feedback_comment }}"
                                    </flux:text>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @php
                                    $criteria = is_array($review->performance_criteria) ? $review->performance_criteria : (json_decode($review->performance_criteria, true) ?? []);
                                @endphp
                                @foreach([
                                    'teaching_quality' => 'Teaching',
                                    'communication' => 'Communication',
                                    'punctuality' => 'Punctuality',
                                    'professionalism' => 'Professionalism'
                                ] as $key => $label)
                                    <div>
                                        <flux:text size="min" class="uppercase tracking-widest font-bold opacity-50 block mb-1">{{ $label }}</flux:text>
                                        <div class="flex items-center gap-1 text-amber-500">
                                            <flux:icon icon="star" variant="solid" class="size-3" />
                                            <flux:text size="xs" weight="black" class="text-slate-700 dark:text-slate-300">{{ $criteria[$key] ?? 0 }}</flux:text>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if($review->areas_of_strength)
                                    @foreach(explode(',', $review->areas_of_strength) as $theme)
                                        <flux:badge size="sm" color="emerald" variant="subtle" class="font-bold">{{ trim($theme) }}</flux:badge>
                                    @endforeach
                                @endif
                                @if($review->areas_for_improvement)
                                    @foreach(explode(',', $review->areas_for_improvement) as $theme)
                                        <flux:badge size="sm" color="rose" variant="subtle" class="font-bold">{{ trim($theme) }}</flux:badge>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state 
                        variant="card" 
                        icon="chat-bubble-bottom-center-text"
                        heading="No evaluations found"
                        message="There are no individual student evaluations for this instructor yet."
                    />
                </div>
            @endforelse

            <div class="mt-8">
                {{ $this->individualEvaluations->links() }}
            </div>
        </div>
    </div>
</div>
