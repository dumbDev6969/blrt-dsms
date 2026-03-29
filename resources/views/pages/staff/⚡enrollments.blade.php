<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\EnrollmentForm;
use App\Services\EnrollmentService;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    #[Computed]
    public function enrollmentCounts()
    {
        return [
            'total' => EnrollmentForm::count(),
            'submitted' => EnrollmentForm::where('status', 'submitted')->count(),
            'approved' => EnrollmentForm::where('status', 'approved')->count(),
            'rejected' => EnrollmentForm::where('status', 'rejected')->count(),
        ];
    }

    #[Computed]
    public function enrollments()
    {
        $query = EnrollmentForm::query()
            ->with(['studentProfile.user', 'course'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('studentProfile.user', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                    })->orWhere('control_number', 'like', '%' . $this->search . '%');
                });
            });

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->latest()->paginate(10);
    }

    public function approveEnrollment(EnrollmentForm $enrollment)
    {
        try {
            $service = app(EnrollmentService::class);
            $result = $service->approve($enrollment);

            if ($result['instructor_assigned']) {
                $instructorName = $result['enrollment']->instructorProfile?->user?->name ?? 'an instructor';
                session()->flash('status', 'Enrollment #' . $enrollment->control_number . ' has been approved and assigned to ' . $instructorName . '.');
            } else {
                session()->flash('status', 'Enrollment #' . $enrollment->control_number . ' has been approved, but no matching instructor was found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to approve enrollment: ' . $e->getMessage());
        }
    }

    public function revertToPending(EnrollmentForm $enrollment)
    {
        $enrollment->update(['status' => 'submitted']);
        session()->flash('status', 'Enrollment #' . $enrollment->control_number . ' moved back to pending.');
    }

    public function rejectEnrollment(EnrollmentForm $enrollment)
    {
        $enrollment->update(['status' => 'rejected']);
        session()->flash('status', 'Enrollment #' . $enrollment->control_number . ' has been rejected.');
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Manage Enrollments</flux:heading>
            <flux:text>
                Review recently submitted student applications and validate their requirements.
            </flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total Enrollments
                </flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="document-text" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['total'] }}</flux:heading>
                <flux:text>total</flux:text>
            </div>
        </div>

        {{-- Pending --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Pending Review
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['submitted'] }}</flux:heading>
                <flux:text>to review</flux:text>
            </div>
        </div>

        {{-- Approved --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Approved
                </flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['approved'] }}</flux:heading>
                <flux:text>verified</flux:text>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Rejected
                </flux:text>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['rejected'] }}</flux:heading>
                <flux:text>rejected</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">
        {{-- Filter Tabs & Search --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-2 md:gap-0">
            <div class="flex flex-wrap gap-1 p-1">
                <button wire:click="$set('status', 'all')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'all' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    All ({{ $this->enrollmentCounts['total'] }})
                </button>
                <button wire:click="$set('status', 'submitted')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'submitted' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Pending ({{ $this->enrollmentCounts['submitted'] }})
                </button>
                <button wire:click="$set('status', 'approved')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'approved' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Approved ({{ $this->enrollmentCounts['approved'] }})
                </button>
                <button wire:click="$set('status', 'rejected')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'rejected' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Rejected ({{ $this->enrollmentCounts['rejected'] }})
                </button>
            </div>
            <div class="pr-1 w-full md:w-72">
                <x-live-search placeholder="Search control # or name..." />
            </div>
        </div>

        {{-- Enrollment Table Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">Enrollment Queue</flux:heading>
                    <flux:text size="sm" class="mt-1">Monitor and validate incoming student enrollments.
                    </flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->enrollments->total() }} Records
                </flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Control Number</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Student</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Course</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Submitted</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse ($this->enrollments as $enrollment)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text weight="bold" class="font-mono">{{ $enrollment->control_number }}
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col">
                                            <flux:text weight="medium" class="text-zinc-900 dark:text-white">
                                                {{ $enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                                            <flux:text size="xs" class="text-zinc-500">
                                                {{ $enrollment->studentProfile->user->email ?? '' }}</flux:text>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm">{{ $enrollment->course->title ?? 'N/A' }}
                                        </flux:text>
                                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-tighter">
                                            {{ $enrollment->course->type ?? '' }}</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = [
                                            'submitted' => ['color' => 'amber', 'label' => 'Pending'],
                                            'approved' => ['color' => 'emerald', 'label' => 'Approved'],
                                            'rejected' => ['color' => 'red', 'label' => 'Rejected'],
                                        ];
                                        $config = $statusConfig[$enrollment->status] ?? [
                                            'color' => 'zinc',
                                            'label' => $enrollment->status,
                                        ];
                                    @endphp
                                    <flux:badge :color="$config['color']" variant="subtle" size="sm"
                                        class="capitalize">
                                        {{ $config['label'] }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm">{{ $enrollment->created_at->format('M d, Y') }}
                                        </flux:text>
                                        <flux:text size="xs" class="text-zinc-500">
                                            {{ $enrollment->created_at->diffForHumans() }}</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:menu.item icon="eye"
                                                href="{{ route('staff.enrollment.show', $enrollment->id) }}"
                                                wire:navigate>View Details</flux:menu.item>
                                            <flux:menu.separator />
                                            @if ($enrollment->status === 'submitted')
                                                <flux:menu.item icon="check-circle"
                                                    wire:click="approveEnrollment({{ $enrollment->id }})">Approve
                                                </flux:menu.item>
                                                <flux:menu.item icon="x-circle" variant="danger"
                                                    wire:click="rejectEnrollment({{ $enrollment->id }})">Reject
                                                    Application
                                                </flux:menu.item>
                                            @elseif ($enrollment->status === 'approved')
                                                <flux:menu.item icon="arrow-uturn-left"
                                                    wire:click="revertToPending({{ $enrollment->id }})">Move to
                                                    Pending
                                                </flux:menu.item>
                                                <flux:menu.item icon="x-circle" variant="danger"
                                                    wire:click="rejectEnrollment({{ $enrollment->id }})">Reject
                                                    Application
                                                </flux:menu.item>
                                            @else
                                                <flux:menu.item icon="arrow-uturn-left"
                                                    wire:click="revertToPending({{ $enrollment->id }})">Move to
                                                    Pending
                                                </flux:menu.item>
                                                <flux:menu.item icon="check-circle"
                                                    wire:click="approveEnrollment({{ $enrollment->id }})">Approve
                                                </flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div
                                            class="flex items-center justify-center size-10 rounded-full bg-zinc-100/50 dark:bg-zinc-800/50 border border-zinc-200/50 dark:border-zinc-700/50 mb-3 shadow-sm">
                                            <flux:icon name="document-text"
                                                class="size-5 text-zinc-400 dark:text-zinc-500" />
                                        </div>
                                        <flux:heading>No enrollments found</flux:heading>
                                        <flux:text class="mt-1 text-xs max-w-xs mx-auto">
                                            Try adjusting your filters or search criteria.
                                        </flux:text>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($this->enrollments->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->enrollments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
