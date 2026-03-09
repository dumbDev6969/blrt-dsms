<?php

use Livewire\Component;
use App\Models\InstructorProfile;

new class extends Component {
    public InstructorProfile $instructor;

    public function mount(InstructorProfile $instructor)
    {
        $this->instructor = $instructor->load('user');
    }

    // Verify Instructor
    public function verify()
    {
        $this->instructor->update(['status' => 'verified', 'is_active' => 1]);
        session()->flash('status', 'Instructor verified successfully.');
        $this->redirect(route('admin.pending-registrations'), navigate: true);
    }

    // Unverify / Back to Pending
    public function unverify()
    {
        $this->instructor->update(['status' => 'pending', 'is_active' => 0]);
        session()->flash('status', 'Instructor moved back to pending.');
        $this->redirect(route('admin.pending-registrations'), navigate: true);
    }

    // Reject Instructor
    public function reject()
    {
        $this->instructor->update(['status' => 'rejected', 'is_active' => 0]);
        session()->flash('status', 'Instructor application rejected.');
        $this->redirect(route('admin.pending-registrations'), navigate: true);
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <flux:button variant="ghost" size="sm" icon="arrow-left"
                    href="{{ route('admin.pending-registrations') }}" wire:navigate class="px-2 -ml-2" />
                <flux:text class="text-xs font-semibold uppercase tracking-wider text-slate-500">Registration Details
                </flux:text>
            </div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" class="text-2xl font-bold tracking-tight">
                    {{ $this->instructor->user->name ?? 'Unknown Instructor' }}
                </flux:heading>
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'amber', 'label' => 'Pending Review'],
                        'verified' => ['color' => 'emerald', 'label' => 'Verified'],
                        'rejected' => ['color' => 'red', 'label' => 'Rejected'],
                    ];
                    $config = $statusConfig[$this->instructor->status] ?? [
                        'color' => 'zinc',
                        'label' => $this->instructor->status,
                    ];
                @endphp
                <flux:badge :color="$config['color']" size="sm" class="capitalize">
                    {{ $config['label'] }}
                </flux:badge>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex flex-wrap gap-2">
            @if ($this->instructor->status === 'pending')
                <flux:button variant="danger" icon="x-circle" wire:click="reject"
                    wire:confirm="Are you sure you want to reject this application?">Reject</flux:button>
                <flux:button variant="primary" icon="check-circle" wire:click="verify"
                    wire:confirm="Approve this instructor?">Approve</flux:button>
            @elseif ($this->instructor->status === 'verified')
                <flux:button variant="ghost" icon="arrow-uturn-left" wire:click="unverify">Move to Pending
                </flux:button>
                <flux:button variant="danger" icon="x-circle" wire:click="reject"
                    wire:confirm="Are you sure you want to reject this application?">Reject</flux:button>
            @else
                <flux:button variant="ghost" icon="arrow-uturn-left" wire:click="unverify">Move to Pending
                </flux:button>
                <flux:button variant="primary" icon="check-circle" wire:click="verify"
                    wire:confirm="Approve this instructor?">Approve</flux:button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- INSTRUCTOR INFORMATION --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Instructor Information</flux:heading>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Full Name</flux:text>
                        <flux:text>{{ $this->instructor->user->name ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Email Address</flux:text>
                        <flux:text>{{ $this->instructor->user->email ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">License Number</flux:text>
                        <span
                            class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                            {{ $this->instructor->license_number ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">License Expiry</flux:text>
                        <flux:text>
                            {{ $this->instructor->license_expiry ? $this->instructor->license_expiry->format('F j, Y') : 'N/A' }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Account Status</flux:text>
                        @if ($this->instructor->is_active)
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">
                                <div class="size-1.5 rounded-full bg-emerald-500"></div>
                                Active
                            </div>
                        @else
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-50 text-zinc-600 border border-zinc-200 dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700/50">
                                <div class="size-1.5 rounded-full bg-zinc-400"></div>
                                Inactive
                            </div>
                        @endif
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Registration Date
                        </flux:text>
                        <flux:text>{{ $this->instructor->created_at->format('F j, Y') }}</flux:text>
                    </div>
                </div>
            </div>

            {{-- SKILLS & VEHICLE TYPES --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Qualifications</flux:heading>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Skills --}}
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-2">Skills</flux:text>
                        <div class="flex flex-wrap gap-2">
                            @forelse($this->instructor->skills ?? [] as $skill)
                                <flux:badge color="blue" variant="subtle" size="sm">{{ $skill }}
                                </flux:badge>
                            @empty
                                <flux:text size="sm" class="text-slate-400 italic">No skills listed</flux:text>
                            @endforelse
                        </div>
                    </div>

                    {{-- Vehicle Types --}}
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-2">Vehicle Types
                        </flux:text>
                        <div class="flex flex-wrap gap-2">
                            @forelse($this->instructor->vehicle_types ?? [] as $vehicle)
                                <flux:badge color="purple" variant="subtle" size="sm">{{ $vehicle }}
                                </flux:badge>
                            @empty
                                <flux:text size="sm" class="text-slate-400 italic">No vehicle types listed
                                </flux:text>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Schedule & Timeline --}}
        <div class="space-y-6">

            {{-- WEEKLY SCHEDULE --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Weekly Schedule</flux:heading>
                    <flux:text size="xs" class="mt-1">Instructor's available hours</flux:text>
                </div>
                <div class="p-0 divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $schedule = $this->instructor->weekly_schedule ?? [];
                        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp

                    @if (!empty($schedule))
                        @foreach ($dayOrder as $day)
                            @if (isset($schedule[$day]))
                                <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                                                <flux:icon icon="calendar-days" class="size-4" />
                                            </div>
                                            <flux:text size="sm" weight="medium" class="capitalize">
                                                {{ $day }}</flux:text>
                                        </div>
                                        <flux:badge color="zinc" variant="subtle" size="sm">
                                            {{ $schedule[$day] }}
                                        </flux:badge>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="p-8 text-center">
                            <div
                                class="flex items-center justify-center p-3 bg-zinc-100 dark:bg-zinc-800 rounded-full w-fit mx-auto mb-3">
                                <flux:icon icon="calendar" class="size-6 text-zinc-400" />
                            </div>
                            <flux:text size="sm" weight="medium" class="text-zinc-900 dark:text-zinc-100 mb-1">
                                No schedule set</flux:text>
                            <flux:text size="xs" class="text-zinc-500">The instructor hasn't set their
                                availability
                                yet.</flux:text>
                        </div>
                    @endif
                </div>
            </div>

            {{-- APPLICATION TIMELINE --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden p-5">
                <flux:heading size="sm" weight="bold" class="mb-3">Application Timeline</flux:heading>
                <div
                    class="relative pl-4 space-y-4 before:absolute before:inset-y-0 before:left-[7px] before:w-[2px] before:bg-slate-200 dark:before:bg-slate-800">
                    <div class="relative flex gap-3">
                        <div
                            class="absolute -left-4 w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 mt-1.5 ring-4 ring-white dark:ring-slate-900">
                        </div>
                        <div>
                            <flux:text size="sm" weight="medium">Account Created</flux:text>
                            <flux:text size="xs" class="text-slate-500">
                                {{ $this->instructor->user->created_at->format('M d, Y • h:i A') ?? 'Unknown' }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="relative flex gap-3">
                        <div
                            class="absolute -left-4 w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 mt-1.5 ring-4 ring-white dark:ring-slate-900">
                        </div>
                        <div>
                            <flux:text size="sm" weight="medium">Registration Submitted</flux:text>
                            <flux:text size="xs" class="text-slate-500">
                                {{ $this->instructor->created_at->format('M d, Y • h:i A') }}
                            </flux:text>
                        </div>
                    </div>
                    @if ($this->instructor->status !== 'pending')
                        <div class="relative flex gap-3">
                            @php
                                $dotColor = $this->instructor->status === 'verified' ? 'bg-emerald-500' : 'bg-red-500';
                            @endphp
                            <div
                                class="absolute -left-4 w-2 h-2 rounded-full {{ $dotColor }} mt-1.5 ring-4 ring-white dark:ring-slate-900">
                            </div>
                            <div>
                                <flux:text size="sm" weight="medium" class="capitalize">Application
                                    {{ $this->instructor->status }}</flux:text>
                                <flux:text size="xs" class="text-slate-500">
                                    {{ $this->instructor->updated_at->format('M d, Y • h:i A') }}
                                </flux:text>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
