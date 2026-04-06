<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Services\InstructorPerformanceService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

new class extends Component {
    #[Computed]
    public function instructorsPerformances()
    {
        $service = app(InstructorPerformanceService::class);
        $instructors = InstructorProfile::with('user')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get();

        return $instructors->map(function ($instructor) use ($service) {
            $instructor->coursePerformances = $service->getPerformancesByCourse($instructor->id);
            return $instructor;
        });
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl font-sans text-slate-900 dark:text-slate-100 p-2 sm:p-0">
    <x-callout />
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('dashboard')" wire:navigate>Back to Dashboard</flux:button>
            </div>
            <flux:heading size="xl" class="text-3xl font-black tracking-tight">Instructor Performance Analytics</flux:heading>
            <flux:text class="mt-1">Comprehensive directory of all approved instructors and their course evaluations.</flux:text>
        </div>
        <div class="flex gap-3">
            <flux:button variant="ghost" icon="arrow-down-tray">Export All Data</flux:button>
        </div>
    </div>

    {{-- Performance Directory --}}
    <div class="flex flex-col gap-10">
        @forelse ($this->instructorsPerformances as $instructor)
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-4">
                        <flux:avatar src="{{ $instructor->user->avatar_url ?? '' }}" :initials="$instructor->user->initials()" size="xl" class="ring-4 ring-slate-100 dark:ring-slate-800" />
                        <div>
                            <flux:heading size="xl" weight="black">{{ $instructor->user->name }}</flux:heading>
                            <flux:text size="sm" variant="subtle" class="font-mono uppercase tracking-widest">Instructor #{{ $instructor->id }} • {{ $instructor->license_number }}</flux:text>
                        </div>
                    </div>
                    <flux:button variant="ghost" size="sm" icon="eye" :href="route('admin.instructor.evaluations', $instructor->id)" wire:navigate>View All Evaluations</flux:button>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    @forelse ($instructor->coursePerformances as $perf)
                        <x-instructor-performance 
                            :courseTitle="$perf->course->title"
                            :courseCode="$perf->course->code"
                            :courseType="strtoupper($perf->course->type)"
                            :avgRating="$perf->avgRating"
                            :totalReviews="$perf->totalReviews"
                            :avgCriteria="$perf->avgCriteria"
                            :performances="$perf->performances"
                            :lastEvaluationDate="$perf->lastEvaluationDate"
                            :trend="$perf->trend"
                            :ratingDistribution="$perf->ratingDistribution"
                            :topStrengths="$perf->topStrengths"
                            :topImprovements="$perf->topImprovements"
                        />
                    @empty
                        <div class="col-span-full py-12 text-center rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 text-slate-500">
                            <flux:icon icon="star" class="size-12 mx-auto mb-4 opacity-20" />
                            <flux:heading size="lg" class="opacity-50">No evaluations recorded yet</flux:heading>
                            <flux:text>This instructor hasn't received any student performance reviews for their assigned courses.</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-full mb-4">
                    <flux:icon icon="users" class="size-10 text-slate-400" />
                </div>
                <flux:heading size="lg">No Approved Instructors</flux:heading>
                <flux:text class="text-center max-w-xs mt-2">There are currently no instructors with an 'Approved' status in the system.</flux:text>
                <flux:button variant="primary" class="mt-6" :href="route('admin.pending-registrations')" wire:navigate>Manage Registrations</flux:button>
            </div>
        @endforelse
    </div>
</div>
