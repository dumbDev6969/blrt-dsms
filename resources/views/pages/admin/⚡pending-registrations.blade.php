<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $status = 'pending';
    public $registrationType = 'instructor'; // 'instructor' or 'staff'
    public $search = '';

    public function updatedRegistrationType()
    {
        $this->resetPage();
        $this->status = 'pending';
        $this->search = '';
        unset($this->statusCount, $this->filteredRegistrations);
    }

    #[Computed]
    public function statusCount()
    {
        if ($this->registrationType === 'instructor') {
            return [
                'pending' => InstructorProfile::where('status', 'pending')->count(),
                'verified' => InstructorProfile::where('status', 'verified')->count(),
                'rejected' => InstructorProfile::where('status', 'rejected')->count(),
                'today' => InstructorProfile::whereDate('created_at', today())->count(),
                'verifiedToday' => InstructorProfile::whereDate('updated_at', today())->where('status', 'verified')->count(),
                'rejectedToday' => InstructorProfile::whereDate('updated_at', today())->where('status', 'rejected')->count(),
            ];
        }

        return [
            'pending' => User::role('Staff')->where('status', 'pending')->count(),
            'verified' => User::role('Staff')->where('status', 'active')->count(),
            'rejected' => User::role('Staff')->where('status', 'rejected')->count(),
            'today' => User::role('Staff')->whereDate('created_at', today())->count(),
            'verifiedToday' => User::role('Staff')->whereDate('updated_at', today())->where('status', 'active')->count(),
            'rejectedToday' => User::role('Staff')->whereDate('updated_at', today())->where('status', 'rejected')->count(),
        ];
    }

    #[Computed]
    public function filteredRegistrations()
    {
        if ($this->registrationType === 'instructor') {
            return InstructorProfile::with('user:id,name,email')
                ->whereHas('user', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->where('status', $this->status)
                ->latest()
                ->paginate(10);
        }

        $staffStatus = $this->status === 'verified' ? 'active' : $this->status;
        return User::role('Staff')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->where('status', $staffStatus)
            ->latest()
            ->paginate(10);
    }

    public function verify($id)
    {
        if ($this->registrationType === 'instructor') {
            // Note: The previous view's stats used 'verified' but verify() method did 'approved'.
            // Consistency: set to 'verified'.
            InstructorProfile::findOrFail($id)->update(['status' => 'verified', 'is_active' => 1]);
        } else {
            User::findOrFail($id)->update(['status' => 'active']);
        }
        session()->flash('status', ucfirst($this->registrationType) . ' verified successfully.');
    }

    public function unverify($id)
    {
        if ($this->registrationType === 'instructor') {
            InstructorProfile::findOrFail($id)->update(['status' => 'pending', 'is_active' => 0]);
        } else {
            User::findOrFail($id)->update(['status' => 'pending']);
        }
        session()->flash('status', ucfirst($this->registrationType) . ' moved back to pending.');
    }

    public function reject($id)
    {
        if ($this->registrationType === 'instructor') {
            InstructorProfile::findOrFail($id)->update(['status' => 'rejected', 'is_active' => 0]);
        } else {
            User::findOrFail($id)->update(['status' => 'rejected']);
        }
        session()->flash('status', ucfirst($this->registrationType) . ' application rejected.');
    }
};
?>


{{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Pending Registrations
            </flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Review and manage applications
            </flux:text>
        </div>
        
        <flux:radio.group wire:model.live="registrationType" variant="segmented">
            <flux:radio label="Instructors" value="instructor" />
            <flux:radio label="Staff" value="staff" />
        </flux:radio.group>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Pending --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->statusCount['pending'] }}</flux:heading>
                <flux:text>applicants</flux:text>
            </div>
        </div>

        {{-- Verified --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Verified</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->statusCount['verified'] }}</flux:heading>
                <flux:text color="emerald">+ {{ $this->statusCount['today'] }} today</flux:text>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Rejected</span>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="x-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->statusCount['rejected'] }}</flux:heading>
                <flux:text color="red">+ {{ $this->statusCount['rejectedToday'] }} today</flux:text>
            </div>
        </div>

        {{-- Registered today --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Registration today</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="user-plus" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->statusCount['today'] }}</flux:heading>
                <flux:text color="blue">applications</flux:text>
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">


        {{-- Filter Tabs & Search --}}
        <div class="flex items-center justify-between p-1 bg-slate-100 dark:bg-slate-800 rounded-lg w-full">
            <div class="flex gap-2 p-1">
                <button wire:click="$set('status', 'pending')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'pending' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    Pending ({{ $this->statusCount['pending'] }})
                </button>
                <button wire:click="$set('status', 'verified')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'verified' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    Verified ({{ $this->statusCount['verified'] }})
                </button>
                <button wire:click="$set('status', 'rejected')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'rejected' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                    Rejected ({{ $this->statusCount['rejected'] }})
                </button>
            </div>
            <div class="pr-1">
                <x-live-search placeholder="Search by name or email" class="w-64" />
            </div>
        </div>

        {{-- Applicants List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">
                        @if($registrationType === 'instructor')
                            {{ $status === 'pending' ? 'Instructor Queue' : ($status === 'verified' ? 'Verified Instructors' : 'Rejected Instructor Applications') }}
                        @else
                            {{ $status === 'pending' ? 'Staff Queue' : ($status === 'verified' ? 'Verified Staff' : 'Rejected Staff Applications') }}
                        @endif
                    </flux:heading>
                    <flux:text size="sm" class="mt-1">
                        @if($registrationType === 'instructor')
                            {{ $status === 'pending' ? 'Manage new instructor registrations and verification requests.' : ($status === 'verified' ? 'View and manage active instructor profiles.' : 'View rejected instructor applications.') }}
                        @else
                            {{ $status === 'pending' ? 'Manage new staff registrations and verification requests.' : ($status === 'verified' ? 'View and manage active staff members.' : 'View rejected staff applications.') }}
                        @endif
                    </flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->filteredRegistrations->total() }} Total
                </flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    {{-- Header --}}
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Applicant</th>
                            @if($registrationType === 'instructor')
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">License Number</th>
                            @endif
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Registration Date</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right"></th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse($this->filteredRegistrations as $pending)
                            <tr :key="$pending->id"
                                class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">

                                {{-- Applicant --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-xs font-bold">
                                            @if($registrationType === 'instructor')
                                                {{ $pending->user?->initials() }}
                                            @else
                                                {{ $pending->initials() }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                {{ $registrationType === 'instructor' ? $pending->user->name : $pending->name }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $registrationType === 'instructor' ? $pending->user->email : $pending->email }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- License ( styled as Code ) (Instructors only) --}}
                                @if($registrationType === 'instructor')
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        {{ $pending->license_number }}
                                    </span>
                                </td>
                                @endif

                                {{-- Date --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-zinc-700 dark:text-zinc-300">
                                            {{ $pending->created_at->format('M d, Y') }}
                                        </span>
                                        <span class="text-xs text-zinc-500">
                                            {{ $pending->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    @php
                                        // $pending->status is 'active' for verified staff
                                        $displayStatus = $status === 'verified' ? 'verified' : $status;
                                    @endphp

                                    @if ($displayStatus === 'pending')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50">
                                            <div class="size-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                                            Pending Review
                                        </div>
                                    @elseif ($displayStatus === 'verified')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">
                                            <flux:icon icon="check-circle" class="size-3" />
                                            Verified
                                        </div>
                                    @else
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50">
                                            <flux:icon icon="x-circle" class="size-3" />
                                            Rejected
                                        </div>
                                    @endif
                                </td>

                                {{-- Action Menu --}}
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            @if($registrationType === 'instructor')
                                                <flux:menu.item icon="eye"
                                                    href="{{ route('admin.registration-data', $pending) }}" wire:navigate>
                                                    View Details
                                                </flux:menu.item>
                                                <flux:menu.separator />
                                            @endif
                                            
                                            @if ($displayStatus === 'pending')
                                                <flux:menu.item icon="check-circle"
                                                    wire:click="verify({{ $pending->id }})">Approve</flux:menu.item>
                                                <flux:menu.item icon="x-circle" variant="danger"
                                                    wire:click="reject({{ $pending->id }})">Reject</flux:menu.item>
                                            @elseif ($displayStatus === 'verified')
                                                <flux:menu.item icon="arrow-uturn-left"
                                                    wire:click="unverify({{ $pending->id }})">Move to Pending
                                                </flux:menu.item>
                                                <flux:menu.item icon="x-circle" variant="danger"
                                                    wire:click="reject({{ $pending->id }})">Reject</flux:menu.item>
                                            @else
                                                <flux:menu.item icon="arrow-uturn-left"
                                                    wire:click="unverify({{ $pending->id }})">Move to Pending
                                                </flux:menu.item>
                                                <flux:menu.item icon="check-circle"
                                                    wire:click="verify({{ $pending->id }})">Approve</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty State --}}
                            <x-empty-state 
                                variant="table" 
                                :colspan="$registrationType === 'instructor' ? 5 : 4"
                                icon="check-circle"
                                heading="Queue Cleared"
                                :message="$status === 'pending' ? 'All applications have been processed. New requests will appear here.' : 'No verified applicants found matching your criteria.'"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Wrapper --}}
            @if ($this->filteredRegistrations->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->filteredRegistrations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
