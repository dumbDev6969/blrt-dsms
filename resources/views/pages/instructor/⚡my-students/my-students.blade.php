<div class="flex h-full w-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- Persistent warning for unpaid students --}}
    @if ($this->unpaidStudents->isNotEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle" class="shadow-sm">
            <flux:callout.heading>
                {{ $this->unpaidStudents->count() }} student(s) have not fully paid
            </flux:callout.heading>
            <flux:callout.text>
                Sessions cannot be started until all active students are fully paid. Please coordinate with the staff
                for payment follow-up.
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">My Students</flux:heading>
            <flux:text>Manage your assigned students and track their course progress.</flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total Assigned
                </flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="users" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['total'] }}</flux:heading>
                <flux:text>students</flux:text>
            </div>
        </div>

        {{-- Active --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Currently Active
                </flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['active'] }}</flux:heading>
                <flux:text>learning</flux:text>
            </div>
        </div>

        {{-- Pending --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Pending Start
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['pending'] }}</flux:heading>
                <flux:text>waiting</flux:text>
            </div>
        </div>

        {{-- Completed --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Finished
                </flux:text>
                <div class="p-2 bg-zinc-50 text-zinc-600 rounded-lg dark:bg-zinc-900/20 dark:text-zinc-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['completed'] }}</flux:heading>
                <flux:text>graduated</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-4 sm:gap-5">
        {{-- Filters & Search --}}
        <div
            class="flex flex-col gap-3 p-3 bg-zinc-100 dark:bg-zinc-800/50 rounded-2xl w-full border border-zinc-200 dark:border-zinc-700/50">
            {{-- Search and Status Tabs row --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div
                    class="flex flex-wrap gap-1 bg-white/50 dark:bg-zinc-900/50 p-1 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700/50 w-full lg:w-auto">
                    @foreach (['all', 'active', 'pending', 'completed', 'dropped'] as $tab)
                        <button wire:click="$set('status', '{{ $tab }}')"
                            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all capitalize {{ $status === $tab ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-white' }}">
                            {{ $tab }} <span
                                class="hidden sm:inline opacity-70 ml-1">({{ $this->enrollmentCounts[$tab] ?? $this->enrollmentCounts['total'] }})</span>
                        </button>
                    @endforeach
                </div>

                <div class="w-full lg:w-72">
                    <x-live-search placeholder="Search name or student code..." />
                </div>
            </div>

            {{-- Module Sub-filter --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-700/50">
                <flux:text size="sm" weight="semibold" class="text-zinc-500 uppercase tracking-wider shrink-0">
                    Module Type:</flux:text>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['all' => 'All Modules', 'tdc' => 'Theory (TDC)', 'pdc' => 'Practical (PDC)'] as $val => $label)
                        <button wire:click="$set('module', '{{ $val }}')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all border {{ $module === $val ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white shadow-sm' : 'bg-transparent text-zinc-500 border-zinc-300 dark:border-zinc-600 hover:border-zinc-400 dark:hover:border-zinc-500' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Enrollment List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden flex flex-col">
            <div
                class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">My Students List</flux:heading>
                    <flux:text size="sm" class="mt-1">View and track progress of students assigned to you.
                    </flux:text>
                </div>
                <flux:button variant="primary" size="sm" icon="play" wire:click="beginTDC"
                    wire:loading.attr="disabled" wire:target="beginTDC"
                    :disabled="$activeSessionExists || Auth::user()->instructorProfile->isPending()">
                    Begin TDC sessions
                </flux:button>
            </div>
            {{-- Enrollment Cards Grid --}}
            <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 bg-slate-50/30 dark:bg-slate-900/50"
                wire:transition>
                @forelse ($this->enrollments as $enrollment)
                    <x-enrollment-card :enrollment="$enrollment">
                        <x-slot name="actions">
                            <flux:button variant="primary" size="sm" class="w-full sm:flex-1 justify-center"
                                icon="eye" :href="route('instructor.student.show', $enrollment)" wire:navigate
                                :disabled="Auth::user()->instructorProfile->isPending()">
                                View enrollment
                            </flux:button>
                            @if ($enrollment->course->type === 'practical' && $enrollment->status === 'active')
                                <flux:button variant="subtle" size="sm" class="w-full sm:flex-1 justify-center"
                                    icon="play" wire:click="openPDCModal({{ $enrollment->id }})"
                                    wire:loading.attr="disabled" wire:target="openPDCModal({{ $enrollment->id }})"
                                    :disabled="$enrollment->pdc_hours_required <= 0 || $enrollment->pdc_status === 'completed' || Auth::user()->instructorProfile->isPending()">
                                    Start PDC session
                                </flux:button>
                            @endif

                            {{-- TDC Grading Action --}}
                            @if ($enrollment->course->type === 'theoretical' && in_array($enrollment->status, ['active', 'completed']))
                                <div class="w-full sm:flex-1">
                                    <livewire:instructor.update-grade-button :enrollment="$enrollment"
                                        wire:key="grade-btn-{{ $enrollment->id }}" />
                                </div>
                            @endif
                        </x-slot>
                    </x-enrollment-card>
                @empty
                    <div class="col-span-full">
                        <x-empty-state variant="card" icon="user-group" heading="No students found"
                            message="Try adjusting your search filters or check back later for new enrollments." />
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($this->enrollments->hasPages())
                <div
                    class="px-4 sm:px-6 py-3 sm:py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    {{ $this->enrollments->links() }}
                </div>
            @endif
        </div>
    </div>

    <flux:modal name="start-pdc-modal" class="md:w-96">
        <form wire:submit.prevent="confirmBeginPDC" class="space-y-6">
            <div>
                <flux:heading size="lg">Start Practical Session</flux:heading>
                <flux:text size="sm" class="mt-1">Please select the vehicle you will be using for this session.
                </flux:text>
            </div>

            <flux:select wire:model="selectedVehicleId" label="Vehicle" placeholder="Choose a vehicle...">
                @foreach ($this->availableVehicles as $vehicle)
                    <flux:select.option value="{{ $vehicle->id }}">
                        {{ $vehicle->plate_number }} - {{ $vehicle->model }} ({{ ucfirst($vehicle->transmission) }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Start Session</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
