<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
new class extends Component {
    use WithPagination;

    public $document;
    public $status = 'pending';

    public $search = '';

    #[Computed]
    public function statusCount()
    {
        return [
            'pending' => Document::where('status', 'pending')->count(),
            'verified' => Document::where('status', 'verified')->count(),
            'rejected' => Document::where('status', 'rejected')->count(),
            'today' => Document::whereDate('created_at', today())->count(),
            'verifiedToday' => Document::whereDate('updated_at', today())->where('status', 'verified')->count(),
            'rejectedToday' => Document::whereDate('updated_at', today())->where('status', 'rejected')->count(),
        ];
    }

    #[Computed]
    public function filteredUsers()
    {
        return User::whereHas('documents', function ($query) {
            $query->where('status', $this->status);
        })
            ->where('name', 'like', '%' . $this->search . '%')
            ->with([
                'documents' => function ($query) {
                    $query->where('status', $this->status)->latest();
                },
            ])
            ->paginate(10);
    }

    // Reject Document
    public function reject(Document $document)
    {
        $document->update([
            'status' => 'rejected',
        ]);

        session()->flash('status', 'Document rejected successfully.');
    }

    // Accept Document
    public function verify(Document $document)
    {
        $document->update([
            'status' => 'verified',
        ]);

        session()->flash('status', 'Document accepted successfully.');
    }

    // Back to Pending
    public function backToPending(Document $document)
    {
        $document->update([
            'status' => 'pending',
        ]);

        session()->flash('status', 'Document back to pending successfully.');
    }
};
?>

{{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Pending Documents</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Review and verify submitted student documents
            </flux:text>
        </div>
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
                <flux:text>documents</flux:text>
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
                <flux:text color="emerald">+ {{ $this->statusCount['verifiedToday'] }} today</flux:text>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Rejected</span>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-5" />
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
        {{-- Filter Tabs --}}
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
                <x-live-search placeholder="Search by name" class="w-64" />
            </div>
        </div>

        {{-- Documents List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">
                        @if ($status === 'pending')
                            Pending Queue
                        @elseif($status === 'verified')
                            Verified Documents
                        @else
                            Rejected Documents
                        @endif
                    </flux:heading>
                    <flux:text size="sm" class="mt-1">
                        @if ($status === 'pending')
                            Review and verify student identity and enrollment documents.
                        @elseif($status === 'verified')
                            View previously approved student documents.
                        @else
                            View documents that require correction or resubmission.
                        @endif
                    </flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->filteredUsers->total() }} Students
                </flux:badge>
            </div>
            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    {{-- Header --}}
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">User</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Document Count</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Latest Submission</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right"></th>
                        </tr>
                    </thead>
                    {{-- Body --}}
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse($this->filteredUsers as $user)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                {{-- User --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-xs font-bold">
                                            {{ $user->initials() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                {{ $user->name }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $user->email }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                {{-- Document Count --}}
                                <td class="px-6 py-4">
                                    <flux:badge :color="$user->documents->count() < 3 ? 'red' : 'zinc'">
                                        {{ $user->documents->count() }}
                                        {{ str('Document')->plural($user->documents->count()) }}
                                    </flux:badge>
                                </td>
                                {{-- Date --}}
                                <td class="px-6 py-4">
                                    @if ($latestDoc = $user->documents->first())
                                        <div class="flex flex-col">
                                            <span class="text-zinc-700 dark:text-zinc-300">
                                                {{ $latestDoc->created_at->format('M d, Y') }}
                                            </span>
                                            <span class="text-xs text-zinc-500">
                                                {{ $latestDoc->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">N/A</span>
                                    @endif
                                </td>
                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    @if ($status === 'pending')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50">
                                            <div class="size-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                                            Needs Review
                                        </div>
                                    @elseif($status === 'verified')
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">
                                            <flux:icon icon="check-circle" class="size-3" />
                                            Verified
                                        </div>
                                    @else
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50">
                                            <flux:icon icon="exclamation-triangle" class="size-3" />
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
                                            <flux:menu.item icon="eye" :href="route('admin.document.check', $user->documents->first()->id)">
                                                Review User
                                            </flux:menu.item>
                                            @if ($status === 'verified' || $status === 'rejected')
                                                <flux:menu.item icon="arrow-uturn-left"
                                                    wire:click="backToPending({{ $user->documents->first()->id }})">
                                                    Back to Pending
                                                </flux:menu.item>
                                            @endif
                                            <flux:menu.separator />
                                            @if ($status === 'pending' || $status === 'rejected')
                                                <flux:menu.item icon="check-circle"
                                                    wire:click="verify({{ $user->documents->first()->id }})">Verify
                                                </flux:menu.item>
                                            @endif
                                            @if ($status === 'pending' || $status === 'verified')
                                                <flux:menu.item icon="x-circle" variant="danger"
                                                    wire:click="reject({{ $user->documents->first()->id }})">Reject
                                                </flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty State --}}
                            <tr>
                                <td colspan="5"
                                    class="py-12 text-center animate-in fade-in zoom-in-95 duration-300">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div
                                            class="flex items-center justify-center size-10 rounded-full bg-zinc-100/50 dark:bg-zinc-800/50 border border-zinc-200/50 dark:border-zinc-700/50 mb-3 shadow-sm">
                                            <flux:icon name="check-circle"
                                                class="size-5 text-zinc-400 dark:text-zinc-500" />
                                        </div>
                                        <flux:heading>Queue Cleared</flux:heading>
                                        <div class="mt-1 text-xs text-zinc-500 max-w-xs mx-auto">
                                            All student documents have been processed. New submissions will appear here.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination Wrapper --}}
            @if ($this->filteredUsers->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->filteredUsers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
