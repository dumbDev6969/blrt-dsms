<?php
 
use Livewire\Component;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
 
new class extends Component {
    public Enrollment $enrollment;
 
    public function mount(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->load(['studentProfile.user', 'course', 'instructorProfile.user', 'bookingSessions']);
    }

    #[On('grade-updated')]
    public function refreshEnrollment()
    {
        $this->enrollment->refresh();
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
                <livewire:instructor.update-grade-button :enrollment="$enrollment" wire:key="grade-btn-{{ $enrollment->id }}" />
            @endif
        </div>
    </div>

    {{-- Main Dashboard Layout --}}
    <x-student-enrollment-details :enrollment="$enrollment" />
</div>
