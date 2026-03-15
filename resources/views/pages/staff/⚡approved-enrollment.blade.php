<?php

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\Document;
use Livewire\Attributes\Validate;
use App\Services\InstructroAvailabilityService;
new class extends Component {
    public Enrollment $enrollment;

    public $amount_paid;

    public $start_date;
    // Define the rules ofr the max value
    public function rules()
    {
        return [
            'amount_paid' => ['required', 'numeric', 'min:0', 'max:' . ($this->enrollment->total_amount ?? $this->enrollment->course->price), 'decimal:0,2'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function mount(Enrollment $enrollment)
    {
        // Eager load necessary relationships
        $this->enrollment = $enrollment->load(['studentProfile.user.documents', 'course', 'instructorProfile.user']);
        $this->amount_paid = $this->enrollment->amount_paid;
        $this->start_date = $this->enrollment->start_date?->format('Y-m-d');
    }

    public function payment()
    {
        $validated = $this->validateOnly('amount_paid');

        $this->enrollment->update([
            'amount_paid' => $validated['amount_paid'],
            'balance' => $this->enrollment->total_amount - $validated['amount_paid'],
        ]);

        session()->flash('status', 'Payment updated successfully!');
        \Flux::modal('add-payment-' . $this->enrollment->id)->close();
    }

    public function startDate(InstructroAvailabilityService $availabilityService)
    {
        $validated = $this->validateOnly('start_date');

        // Check availability
        $availability = $availabilityService->getAvailability($this->enrollment->instructorProfile->id);

        if (!$availability->contains($validated['start_date'])) {
            return $this->addError('start_date', 'The instructor is not available on this date.');
        }

        $this->enrollment->update(['start_date' => $validated['start_date']]);

        session()->flash('status', 'Start date updated!');
        \Flux::modal('start-date-' . $this->enrollment->id)->close();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">
    {{-- Callout Alert --}}
    @if (session('status'))
        <div class="fixed top-5 right-5 z-50 w-full max-w-sm px-4"> {{-- Container ensures proper layout --}}
            <flux:callout icon="check-circle" variant="success" class="shadow-lg" x-data="{ visible: true }" x-show="visible"
                x-transition>
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>

                <x-slot name="controls">
                    <flux:button size="sm" icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        </div>
    @endif
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <flux:button variant="ghost" size="sm" icon="arrow-left"
                    href="{{ route('staff.approved-enrollments') }}" wire:navigate class="px-2 -ml-2" />
                <flux:text class="text-xs font-semibold uppercase tracking-wider text-slate-500">Enrollment Details
                </flux:text>
            </div>
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Code:
                    {{ $this->enrollment->code }}</flux:heading>
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'amber', 'label' => 'Pending'],
                        'active' => ['color' => 'emerald', 'label' => 'Active'],
                        'completed' => ['color' => 'blue', 'label' => 'Completed'],
                        'dropped' => ['color' => 'red', 'label' => 'Dropped'],
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
            <flux:separator />
            <flux:text variant="strong" color="blue">Start Date: {{ $this->enrollment->start_date->format('M d, Y') ?? 'Not Set' }}</flux:text>
            </div>
        </div>
        <flux:modal.trigger name="start-date-{{ $this->enrollment->id }}">
            <flux:button size="sm" variant="primary" icon="calendar">Start Date</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- payment modal --}}
    <flux:modal name="start-date-{{ $this->enrollment->id }}" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Set Start Date</flux:heading>
                <flux:text class="mt-2">Select the date when the student will start the course. This will check
                    against the instructor's availability.</flux:text>
            </div>
            <flux:input type="date" wire:model.blur="start_date" label="Start Date" placeholder="Start Date" />
            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="startDate" type="submit" variant="primary">Save changes
                </flux:button>
            </div>
        </div>
    </flux:modal>

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
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Duration
                                        (Hours)</flux:text>
                                    <flux:text size="sm" weight="bold">
                                        {{ $this->enrollment->course->duration_hours ?? 0 }} hrs</flux:text>
                                </div>
                                <div>
                                    <flux:text size="xs" weight="medium" class="text-slate-500 mb-1">Assigned
                                        Instructor
                                    </flux:text>
                                    <flux:text size="sm" weight="bold">
                                        {{ $this->enrollment->instructorProfile->user->name ?? 'Unassigned' }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FINANCIALS --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="w-full border border-slate-500 flex align-center justify-between p-5 border-b border-slate-200 dark:border-slate-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <flux:heading size="lg" weight="bold">Financials</flux:heading>
                    <flux:modal.trigger name="add-payment-{{ $this->enrollment->id }}">
                        <flux:button size="sm" icon="pencil-square">Payment</flux:button>
                    </flux:modal.trigger>
                </div>
                {{-- payment modal --}}
                <flux:modal name="add-payment-{{ $this->enrollment->id }}" class="md:w-96">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Add payment</flux:heading>
                            <flux:text class="mt-2">Only add payment if the student has paid. You can edit this
                                anytime.</flux:text>
                        </div>
                        <flux:input wire:model.blur="amount_paid" label="Amount" placeholder="Amount" />
                        <div class="flex">
                            <flux:spacer />
                            <flux:button wire:click="payment" type="submit" variant="primary">Save changes
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Total Amount
                        </flux:text>
                        <flux:text size="lg" weight="bold">
                            ₱{{ number_format($this->enrollment->total_amount ?? 0, 2) }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Amount Paid</flux:text>
                        <flux:text size="lg" weight="bold" class="text-emerald-600 dark:text-emerald-400">
                            ₱{{ number_format($this->enrollment->amount_paid ?? 0, 2) }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" weight="medium" class="text-slate-500 mb-1">Balance</flux:text>
                        <flux:text size="lg" weight="bold"
                            class="{{ ($this->enrollment->balance ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-600 dark:text-slate-400' }}">
                            ₱{{ number_format($this->enrollment->balance ?? 0, 2) }}</flux:text>
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Documents & Progress --}}
        <div class="space-y-6">

            {{-- PROGRESS --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden p-5">
                <flux:heading size="sm" weight="bold" class="mb-3">Course Progress</flux:heading>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" weight="medium" class="text-slate-500">Overall Completion
                        </flux:text>
                        <flux:text size="sm" weight="bold">{{ $this->enrollment->progress_percent ?? 0 }}%
                        </flux:text>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full"
                            style="width: {{ $this->enrollment->progress_percent ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    @if (in_array($this->enrollment->course->type, ['tdc', 'comprehensive']))
                        <div class="flex justify-between items-center">
                            <flux:text size="sm" class="text-slate-500">TDC Hours</flux:text>
                            <flux:text size="sm" weight="medium">
                                {{ $this->enrollment->tdc_hours_completed ?? 0 }} /
                                {{ $this->enrollment->tdc_hours_required ?? 0 }}</flux:text>
                        </div>
                    @endif
                    @if (in_array($this->enrollment->course->type, ['pdc', 'comprehensive']))
                        <div class="flex justify-between items-center">
                            <flux:text size="sm" class="text-slate-500">PDC Hours</flux:text>
                            <flux:text size="sm" weight="medium">
                                {{ $this->enrollment->pdc_hours_completed ?? 0 }} /
                                {{ $this->enrollment->pdc_hours_required ?? 0 }}</flux:text>
                        </div>
                        <div class="flex justify-between items-center">
                            <flux:text size="sm" class="text-slate-500">KMs Driven</flux:text>
                            <flux:text size="sm" weight="medium">{{ $this->enrollment->pdc_kms_driven ?? 0 }} km
                            </flux:text>
                        </div>
                    @endif
                </div>
            </div>

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
                <flux:heading size="sm" weight="bold" class="mb-3">Enrollment Timeline</flux:heading>
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
                            <flux:text size="sm" weight="medium">Enrollment Approved</flux:text>
                            <flux:text size="xs" class="text-slate-500">
                                {{ $this->enrollment->created_at->format('M d, Y • h:i A') }}</flux:text>
                        </div>
                    </div>
                    @if ($this->enrollment->start_date)
                        <div class="relative flex gap-3">
                            <div
                                class="absolute -left-4 w-2 h-2 rounded-full bg-emerald-500 mt-1.5 ring-4 ring-white dark:ring-slate-900">
                            </div>
                            <div>
                                <flux:text size="sm" weight="medium" class="capitalize">Course Started
                                </flux:text>
                                <flux:text size="xs" class="text-slate-500">
                                    {{ $this->enrollment->start_date->format('M d, Y') }}</flux:text>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
