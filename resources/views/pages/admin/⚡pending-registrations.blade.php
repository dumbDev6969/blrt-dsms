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
        return InstructorProfile::with('user:id,name')
        ->select('id', 'user_id', 'license_number', 'created_at')
        ->where('is_active', 0)
        ->paginate(5);
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
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <flux:heading size="sm"
                                        class="text-slate-400 font-semibold tracking-wider">Instructor
                                    </flux:heading>
                                </th>
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <flux:heading size="sm"
                                        class="text-slate-400 font-semibold tracking-wider">License Number
                                    </flux:heading>
                                </th>
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <flux:heading size="sm"
                                        class="text-slate-400 font-semibold tracking-wider">Registration Date
                                    </flux:heading>
                                </th>
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <flux:heading size="sm"
                                        class="text-slate-400 font-semibold tracking-wider">Status
                                    </flux:heading>
                                </th>
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <flux:heading size="sm"
                                        class="text-slate-400 font-semibold tracking-wider">Action
                                    </flux:heading>
                                </th>
                                <th class="px-6 py-3 border-b border-slate-100 dark:border-slate-800"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($this->pendingRegistrations as $pending)
                                <tr
                                    class="group hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all duration-200">
                                    {{-- Instructor Info --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="size-10 rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 flex items-center justify-center text-white shadow-sm group-hover:scale-110 transition-transform">
                                                <span class="text-xs font-bold">{{ $pending->user?->initials() }}</span>
                                            </div>
                                                <flux:heading size="md"
                                                    class="group-hover:text-blue-600 transition-colors cursor-default">
                                                    {{ $pending->user->name }}
                                                </flux:heading>                                     
                                        </div>
                                    </td>

                                    {{-- License --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="identification" variant="mini" class="text-slate-300" />
                                            <flux:text
                                                class="font-mono text-sm tracking-tight text-slate-700 dark:text-slate-300">
                                                {{ $pending->license_number }}
                                            </flux:text>
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <flux:text size="sm" class="font-medium">
                                                {{ $pending->created_at->format('M d, Y') }}</flux:text>
                                            <flux:text size="xs" class="text-slate-400">
                                                {{ $pending->created_at->diffForHumans() }}</flux:text>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800/50">
                                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-6 py-4 text-right">
                                        <flux:button href="/admin/registrations/{{ $pending->id }}" wire:navigate
                                            size="sm" variant="ghost" icon-trailing="chevron-right"
                                            class="">
                                            Details
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-20">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="relative">
                                                <div
                                                    class="absolute -inset-1 bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 rounded-full blur opacity-50">
                                                </div>
                                                <div
                                                    class="relative size-16 rounded-full bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center shadow-sm">
                                                    <flux:icon name="check-circle" class="size-8 text-slate-300" />
                                                </div>
                                            </div>
                                            <flux:heading size="lg" class="mt-5">Queue Cleared</flux:heading>
                                            <flux:text class="mt-1 text-center max-w-xs">All instructor applications
                                                have been processed. New requests will appear here.</flux:text>
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
