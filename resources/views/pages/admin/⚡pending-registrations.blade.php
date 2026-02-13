<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\StudentProfile;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
new class extends Component {
    use WithPagination;

    // Count the status of pending and verified accounts
    #[Computed]
    public function statusCount()
    {
        $pending = InstructorProfile::where('is_active', 0)->count();

        $verified = InstructorProfile::where('is_active', 1)->count();

        return [
            'pending' => $pending,
            'verified' => $verified,
        ];
    }

    #[Computed]
    public function pendingRegistrations()
    {
        return InstructorProfile::with('user:id,name')->select('id', 'user_id', 'license_number', 'created_at')->where('is_active', 0)->paginate(5);
    }
};
?>


{{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Pending Registrations</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Review and verify new user applications
            </flux:text>
        </div>
        <div class="flex gap-3">
            <flux:input placeholder="Search by name or email..." icon="magnifying-glass" class="w-64" />
            <flux:button variant="ghost" icon="funnel">Filter</flux:button>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Pending --}}



        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">{{ $this->statusCount['pending'] }}</span>
                <span class="text-xs text-slate-400">applicants</span>
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
                <span class="text-2xl font-bold">{{ $this->statusCount['verified'] }}</span>
                <span class="text-xs text-emerald-600 font-medium">+3 today</span>
            </div>
        </div>

        {{-- Issues --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Issues</span>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">2</span>
                <span class="text-xs text-slate-400">incomplete docs</span>
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">


        {{-- Filter Tabs --}}
        <div class="flex gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg w-fit">
            <button
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm">
                Pending (12)
            </button>
            <button
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                Verified
            </button>
            <button
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                Rejected
            </button>
        </div>

        {{-- Applicants List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">Applicants Queue</flux:heading>
                    <flux:text size="sm" class="mt-1">Manage new instructor registrations and verification
                        requests.</flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->pendingRegistrations->total() }} Total</flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    {{-- Header --}}
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Instructor</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">License Number</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Registration Date</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right"></th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse($this->pendingRegistrations as $pending)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">

                                {{-- Instructor --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-xs font-bold">
                                            {{ $pending->user?->initials() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                {{ $pending->user->name }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $pending->user->email }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- License ( styled as Code ) --}}
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        {{ $pending->license_number }}
                                    </span>
                                </td>

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
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50">
                                        <div class="size-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                                        Pending
                                    </div>
                                </td>

                                {{-- Action Menu --}}
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:menu.item icon="eye"
                                                href="/admin/registrations/{{ $pending->id }}" wire:navigate>
                                                View Details
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="check-circle">Approve</flux:menu.item>
                                            <flux:menu.item icon="x-circle" variant="danger">Reject</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty State --}}
                            <tr>
                                <td colspan="5" class="py-12 text-center animate-in fade-in zoom-in-95 duration-300">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div
                                            class="flex items-center justify-center size-10 rounded-full bg-zinc-100/50 dark:bg-zinc-800/50 border border-zinc-200/50 dark:border-zinc-700/50 mb-3 shadow-sm">
                                            <flux:icon name="check-circle"
                                                class="size-5 text-zinc-400 dark:text-zinc-500" />
                                        </div>
                                        <flux:heading>Queue Cleared</flux:heading>
                                        <div class="mt-1 text-xs text-zinc-500 max-w-xs mx-auto">
                                            All instructor applications have been processed. New requests will appear
                                            here.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Wrapper --}}
            @if ($this->pendingRegistrations->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->pendingRegistrations->links() }}
                </div>
            @endif
        </div>



    </div>
</div>
