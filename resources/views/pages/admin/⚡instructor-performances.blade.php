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
        return InstructorProfile::with('user')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get()
            ->map(function ($instructor) {
                $instructor->courses = \App\Models\Course::whereHas('enrollments', function($q) use ($instructor) {
                    $q->where('instructor_id', $instructor->id)
                      ->whereHas('instructorPerformances');
                })->get();
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
                    @forelse ($instructor->courses as $course)
                        <livewire:instructor-performance-card 
                            :instructor="$instructor"
                            :courseId="$course->id"
                            :courseTitle="$course->title"
                            :courseCode="$course->code"
                            :courseType="strtoupper($course->type)"
                            :key="'instructor-' . $instructor->id . '-course-' . $course->id"
                        />
                    @empty
                        <div class="col-span-full">
                            <x-empty-state 
                                variant="card" 
                                icon="star"
                                heading="No evaluations recorded yet"
                                message="This instructor hasn't received any student performance reviews for their assigned courses."
                            />
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <x-empty-state 
                variant="card" 
                icon="users"
                heading="No Approved Instructors"
                message="There are currently no instructors with an 'Approved' status in the system."
                action-url="{{ route('admin.pending-registrations') }}"
                action-label="Manage Registrations"
            />
        @endforelse
    </div>
</div>
