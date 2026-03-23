<?php

use Livewire\Component;
use App\Models\EnrollmentForm;
use App\Models\Document;
use App\Services\EnrollmentService;
new class extends Component {
    public EnrollmentForm $enrollment;

    public function mount(EnrollmentForm $enrollment)
    {
        // Eager load necessary relationships
        $this->enrollment = $enrollment->load(['studentProfile.user', 'course', 'studentProfile.user.documents']);
    }

    public function approveEnrollment()
    {
        try {
            $service = app(EnrollmentService::class);
            $result = $service->approve($this->enrollment);

            $this->enrollment->refresh();
    
            if ($result['instructor_assigned']) {
                $instructorName = $result['enrollment']->instructorProfile?->user?->name ?? 'an instructor';
                session()->flash('status', 'Enrollment #' . $this->enrollment->control_number . ' has been approved and assigned to ' . $instructorName . '.');
            } else {
                session()->flash('status', 'Enrollment #' . $this->enrollment->control_number . ' has been approved, but no matching instructor was found. Please assign one manually.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to approve enrollment: ' . $e->getMessage());
            return;
        }

        $this->redirect(route('staff.manage-enrollments'), navigate: true);
    }

    public function revertToPending()
    {
        $this->enrollment->update(['status' => 'submitted']);
        session()->flash('status', 'Enrollment #' . $this->enrollment->control_number . ' moved back to pending.');
        $this->redirect(route('staff.manage-enrollments'), navigate: true);
    }

    public function rejectEnrollment()
    {
        $this->enrollment->update(['status' => 'rejected']);
        session()->flash('status', 'Enrollment #' . $this->enrollment->control_number . ' has been rejected.');
        $this->redirect(route('staff.manage-enrollments'), navigate: true);
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('staff.manage-enrollments') }}"
                    wire:navigate class="px-2 -ml-2" />
                <flux:text class="text-xs font-semibold uppercase tracking-wider text-slate-500">Enrollment Details
                </flux:text>
            </div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Control
                    #{{ $this->enrollment->control_number }}</flux:heading>
                @php
                    $statusConfig = [
                        'submitted' => ['color' => 'amber', 'label' => 'Pending Review'],
                        'approved' => ['color' => 'emerald', 'label' => 'Approved'],
                        'rejected' => ['color' => 'red', 'label' => 'Rejected'],
                    ];
                    $config = $statusConfig[$this->enrollment->status] ?? [
                        'color' => 'zinc',
                        'label' => $this->enrollment->status,
                    ];
                @endphp
                <flux:badge :color="$config['color']" size="sm" class="capitalize">
                    {{ $config['label'] }}
                </flux:badge>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex flex-wrap gap-2">
            @if ($this->enrollment->status === 'submitted')
                <flux:button variant="danger" icon="x-circle" wire:click="rejectEnrollment"
                    wire:confirm="Are you sure you want to reject this enrollment?">Reject</flux:button>
                <flux:button variant="primary" icon="check-circle" wire:click="approveEnrollment"
                    wire:confirm="Approve this enrollment?">Approve Enrollment</flux:button>
            @elseif ($this->enrollment->status === 'approved')
                <flux:button variant="ghost" icon="arrow-uturn-left" wire:click="revertToPending">Move to Pending
                </flux:button>
                <flux:button variant="danger" icon="x-circle" wire:click="rejectEnrollment"
                    wire:confirm="Are you sure you want to reject this enrollment?">Reject</flux:button>
            @else
                <flux:button variant="ghost" icon="arrow-uturn-left" wire:click="revertToPending">Move to Pending
                </flux:button>
                <flux:button variant="primary" icon="check-circle" wire:click="approveEnrollment"
                    wire:confirm="Approve this enrollment?">Approve Enrollment</flux:button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- STUDENT INFORMATION --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Student Information</flux:heading>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Full Name</flux:text>
                        <flux:text>{{ $this->enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Email Address</flux:text>
                        <flux:text>{{ $this->enrollment->studentProfile->user->email ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Phone Number</flux:text>
                        <flux:text>{{ $this->enrollment->studentProfile->contact_number ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Date of Birth</flux:text>
                        <flux:text>
                            {{ $this->enrollment->studentProfile->birth_date ? \Carbon\Carbon::parse($this->enrollment->studentProfile->birth_date)->format('F j, Y') : 'N/A' }}
                        </flux:text>
                    </div>
                    <div class="md:col-span-2">
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Complete Address
                        </flux:text>
                        <flux:text>{{ $this->enrollment->studentProfile->address ?? 'N/A' }},
                            {{ $this->enrollment->studentProfile->city ?? '' }}</flux:text>
                    </div>
                </div>
            </div>

            {{-- COURSE INFORMATION --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Course Details</flux:heading>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                            <flux:icon icon="book-open" class="size-8" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="lg">{{ $this->enrollment->course->title ?? 'N/A' }}</flux:heading>
                            <flux:text size="sm" class="text-slate-500 mt-1 mb-4">
                                {{ $this->enrollment->course->description ?? 'No description available.' }}</flux:text>

                            <div
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg">
                                <div>
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Type
                                    </flux:text>
                                    <flux:text size="sm" weight="bold" class="uppercase">
                                        {{ $this->enrollment->course->type ?? 'N/A' }}</flux:text>
                                </div>
                                <div>
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Transmission
                                    </flux:text>
                                    <flux:text size="sm" weight="bold" class="capitalize">
                                        {{ $this->enrollment->course->transmission ?? 'N/A' }}</flux:text>
                                </div>
                                <div>
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Price
                                    </flux:text>
                                    <flux:text size="sm" weight="bold">
                                        ₱{{ number_format($this->enrollment->course->price ?? 0, 2) }}</flux:text>
                                </div>
                                <div>
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Duration
                                        (Hours)</flux:text>
                                    <flux:text size="sm" weight="bold">
                                        {{ $this->enrollment->course->duration_hours ?? 0 }} hrs</flux:text>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Documents --}}
        <div class="space-y-6">
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Submitted Documents</flux:heading>
                    <flux:text size="xs" class="mt-1">Review files submitted by the student</flux:text>
                </div>
                <div class="p-0 divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $documents =
                            Document::where('user_id', $this->enrollment->studentProfile->user_id)->get() ?? collect();
                    @endphp

                    @forelse ($documents as $doc)
                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="p-2 bg-zinc-100 text-zinc-500 rounded-lg dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon icon="document" class="size-5" />
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <flux:text size="sm" weight="medium" class="truncate capitalize">
                                        {{ str_replace('_', ' ', $doc->type) }}</flux:text>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <flux:text size="xs" class="text-slate-500">Uploaded
                                            {{ $doc->created_at->format('M d, Y') }}</flux:text>
                                        @if ($doc->status === 'approved')
                                            <flux:badge color="emerald" size="sm" variant="subtle">Verified
                                            </flux:badge>
                                        @elseif($doc->status === 'rejected')
                                            <flux:badge color="red" size="sm" variant="subtle">Rejected
                                            </flux:badge>
                                        @else
                                            <flux:badge color="amber" size="sm" variant="subtle">Pending
                                            </flux:badge>
                                        @endif
                                    </div>
                                </div>
                                <flux:button variant="ghost" size="sm" icon="eye"
                                    href="{{ route('admin.document.check', $doc) }}" wire:navigate.hover />
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div
                                class="flex items-center justify-center p-3 bg-zinc-100 dark:bg-zinc-800 rounded-full w-fit mx-auto mb-3">
                                <flux:icon icon="document-minus" class="size-6 text-zinc-400" />
                            </div>
                            <flux:text size="sm" weight="medium" class="text-zinc-900 dark:text-zinc-100 mb-1">
                                No documents found</flux:text>
                            <flux:text size="xs" class="text-zinc-500">The student hasn't uploaded any documents
                                yet.</flux:text>
                        </div>
                    @endforelse
                </div>

                @if ($documents->isNotEmpty())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <flux:text size="xs" class="text-center text-slate-500">
                            Click the eye icon to review the full document.
                        </flux:text>
                    </div>
                @endif
            </div>

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
                                {{ $this->enrollment->studentProfile->user->created_at->format('M d, Y • h:i A') ?? 'Unknown' }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="relative flex gap-3">
                        <div
                            class="absolute -left-4 w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 mt-1.5 ring-4 ring-white dark:ring-slate-900">
                        </div>
                        <div>
                            <flux:text size="sm" weight="medium">Enrollment Submitted</flux:text>
                            <flux:text size="xs" class="text-slate-500">
                                {{ $this->enrollment->created_at->format('M d, Y • h:i A') }}</flux:text>
                        </div>
                    </div>
                    @if ($this->enrollment->status !== 'submitted')
                        <div class="relative flex gap-3">
                            @php
                                $dotColor = $this->enrollment->status === 'approved' ? 'bg-emerald-500' : 'bg-red-500';
                            @endphp
                            <div
                                class="absolute -left-4 w-2 h-2 rounded-full {{ $dotColor }} mt-1.5 ring-4 ring-white dark:ring-slate-900">
                            </div>
                            <div>
                                <flux:text size="sm" weight="medium" class="capitalize">Application
                                    {{ $this->enrollment->status }}</flux:text>
                                <flux:text size="xs" class="text-slate-500">
                                    {{ $this->enrollment->updated_at->format('M d, Y • h:i A') }}</flux:text>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
