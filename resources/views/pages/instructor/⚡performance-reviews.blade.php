<?php

use Livewire\Component;
use App\Models\InstructorPerformance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Services\InstructorPerformanceService;
new class extends Component
{
    use WithPagination;

    #[Computed]
    public function performancesByCourse()
    {
        $service = app(InstructorPerformanceService::class);
        return $service->getPerformancesByCourse(Auth::user()->instructorProfile->id);
    }

    #[Computed]
    public function averageRating()
    {
        return InstructorPerformance::where('instructor_id', Auth::user()->instructorProfile->id)
            ->avg('rating');
    }

    #[Computed]
    public function totalReviews()
    {
        return InstructorPerformance::where('instructor_id', Auth::user()->instructorProfile->id)
            ->count();
    }
};
?>

<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" weight="bold" class="tracking-tight">Performance Reviews</flux:heading>
            <flux:text size="sm" class="text-slate-500 mt-1">See how your students have evaluated your teaching performance.</flux:text>
        </div>
        <flux:button href="{{ route('dashboard') }}" variant="subtle" icon="arrow-left" size="sm" wire:navigate>Back to Dashboard</flux:button>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="p-5 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                    <flux:icon icon="star" class="size-5" />
                </div>
                <div>
                    <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold">Avg. Rating</flux:text>
                    <flux:heading size="lg" class="font-bold tracking-tight">{{ $this->averageRating ? number_format($this->averageRating, 1) : 'N/A' }} / 5</flux:heading>
                </div>
            </div>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <flux:icon icon="chat-bubble-left-right" class="size-5" />
                </div>
                <div>
                    <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-bold">Total Reviews</flux:text>
                    <flux:heading size="lg" class="font-bold tracking-tight">{{ $this->totalReviews }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Cards --}}
    @if ($this->performancesByCourse->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($this->performancesByCourse as $group)
                <x-instructor-performance 
                    :courseTitle="$group->course->title"
                    :courseCode="$group->course->code"
                    :courseType="$group->course->type"
                    :avgRating="$group->avgRating"
                    :totalReviews="$group->totalReviews"
                    :avgCriteria="$group->avgCriteria"
                    :performances="$group->performances"
                    :lastEvaluationDate="$group->lastEvaluationDate"
                    :trend="$group->trend"
                    :ratingDistribution="$group->ratingDistribution"
                    :topStrengths="$group->topStrengths"
                    :topImprovements="$group->topImprovements"
                />
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-[50vh] text-center px-4">
            <div class="size-24 rounded-[2rem] bg-gradient-to-br from-slate-50 to-amber-50 dark:from-slate-900/20 dark:to-amber-900/20 flex items-center justify-center mb-8 border border-slate-100 dark:border-slate-800/50 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-0 bg-amber-500/5 dark:bg-amber-500/10 scale-0 group-hover:scale-100 transition-transform duration-700 rounded-full"></div>
                <flux:icon icon="star" class="size-12 text-slate-400 dark:text-slate-500 relative z-10 group-hover:text-amber-500 transition-colors duration-300" />
            </div>
            <flux:heading size="2xl" class="font-bold tracking-tight mb-2">No Reviews Yet</flux:heading>
            <flux:text class="text-slate-500 max-w-md mx-auto leading-relaxed">
                You haven't received any performance reviews yet. Once your students complete their courses and submit evaluations, their feedback will appear here.
            </flux:text>
        </div>
    @endif
</div>
