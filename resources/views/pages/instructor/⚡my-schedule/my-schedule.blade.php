<div class="flex h-full w-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">My Schedule</flux:heading>
            <flux:text>Track your upcoming classes, driving sessions, and assessments.</flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total Sessions
                </flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="calendar-days" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->bookingCounts['total'] }}</flux:heading>
                <flux:text>assigned</flux:text>
            </div>
        </div>

        {{-- Scheduled --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Upcoming
                </flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->bookingCounts['scheduled'] }}</flux:heading>
                <flux:text>scheduled</flux:text>
            </div>
        </div>

        {{-- Completed --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Finished
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="check-badge" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->bookingCounts['completed'] }}</flux:heading>
                <flux:text>completed</flux:text>
            </div>
        </div>

        {{-- Cancelled/No-show --}}
        <div
            class="p-4 sm:p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Missed/Cancelled
                </flux:text>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="no-symbol" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->bookingCounts['cancelled'] + $this->bookingCounts['no-show'] }}
                </flux:heading>
                <flux:text>records</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-4 sm:gap-5">
        {{-- Filter Tabs & Search --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-3 lg:gap-0">
            <div class="flex flex-wrap gap-1 p-1 w-full lg:w-auto">
                @foreach (['all', 'scheduled', 'completed', 'cancelled', 'no-show'] as $tab)
                    <button wire:click="$set('status', '{{ $tab }}')"
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-md transition-colors capitalize {{ $status === $tab ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                        {{ str_replace('-', ' ', $tab) }} <span
                            class="hidden sm:inline">({{ $this->bookingCounts[$tab] ?? $this->bookingCounts['total'] }})</span>
                    </button>
                @endforeach
            </div>
            <div class="px-2 pb-2 lg:p-0 w-full lg:w-72">
                <x-live-search placeholder="Search student or course..." class="w-full" />
            </div>
        </div>

        {{-- Booking List Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden flex flex-col">
            <div
                class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">Session Schedule</flux:heading>
                    <flux:text size="sm" class="mt-1">Detailed list of your assigned teaching and driving
                        sessions.</flux:text>
                </div>
                <flux:button size="sm" variant="primary" icon="play" wire:click="endTDC"
                    wire:loading.attr="disabled" wire:target="endTDC"
                    :disabled="Auth::user()->instructorProfile->isPending()">End TDC sessions</flux:button>
            </div>

            {{-- Booking Cards Grid --}}
            <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 bg-slate-50/30 dark:bg-slate-900/50"
                wire:transition>
                @forelse ($this->bookings as $booking)
                    <div
                        class="group flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-300 overflow-hidden">

                        {{-- Card Header: Date & Time --}}
                        <div
                            class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex justify-between items-center mb-1">
                                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                    <flux:icon icon="calendar" class="size-4" />
                                    <flux:text size="sm" weight="bold" class="text-blue-600 dark:text-blue-400">
                                        {{ $booking->start_time->format('M d, Y') }}
                                    </flux:text>
                                </div>
                                @php
                                    $statusConfig = [
                                        'scheduled' => ['color' => 'blue', 'label' => 'Scheduled'],
                                        'completed' => ['color' => 'emerald', 'label' => 'Completed'],
                                        'cancelled' => ['color' => 'red', 'label' => 'Cancelled'],
                                        'no-show' => ['color' => 'zinc', 'label' => 'No-show'],
                                    ];
                                    $config = $statusConfig[$booking->status] ?? [
                                        'color' => 'zinc',
                                        'label' => $booking->status,
                                    ];
                                @endphp
                                <flux:badge :color="$config['color']" variant="subtle" size="xs"
                                    class="capitalize tracking-wide">
                                    {{ $config['label'] }}
                                </flux:badge>
                            </div>
                            <div class="flex items-center gap-2 text-slate-500">
                                <flux:icon icon="clock" class="size-4" />
                                <flux:text size="xs" weight="medium">
                                    {{ $booking->start_time->format('h:i A') }} -
                                    @if (!empty($booking->end_time))
                                        {{ $booking->end_time->format('h:i A') }}
                                    @else
                                        Not Set
                                    @endif
                                </flux:text>
                            </div>
                        </div>

                        {{-- Card Body: Student & Course --}}
                        <div class="p-4 sm:p-5 flex-1 flex flex-col gap-4">
                            <div class="flex gap-3 items-center">
                                <div
                                    class="h-10 w-10 rounded-full shrink-0 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 font-bold border border-slate-200 dark:border-slate-700">
                                    {{ substr($booking->enrollment->studentProfile->user->name ?? 'S', 0, 1) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <flux:text size="xs"
                                        class="text-slate-500 font-semibold uppercase tracking-wider">Student
                                    </flux:text>
                                    <flux:text weight="bold" class="text-slate-900 dark:text-white truncate">
                                        {{ $booking->enrollment->studentProfile->user->name ?? 'N/A' }}
                                    </flux:text>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <flux:text size="xs"
                                        class="text-slate-500 font-semibold uppercase tracking-wider">Course & Type
                                    </flux:text>
                                    <div class="flex items-center gap-2 mt-1">
                                        <flux:text size="sm" weight="semibold"
                                            class="text-slate-800 dark:text-slate-200">
                                            {{ $booking->enrollment->course->title ?? 'N/A' }}
                                        </flux:text>
                                        <flux:badge size="xs" variant="outline" class="uppercase">
                                            {{ $booking->type }}
                                        </flux:badge>
                                    </div>
                                </div>

                                @if ($booking->vehicle)
                                    <div
                                        class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50 flex items-center gap-3">
                                        <div
                                            class="p-2 bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                            <flux:icon icon="truck" class="size-4" />
                                        </div>
                                        <div class="flex flex-col">
                                            <flux:text size="xs" weight="semibold"
                                                class="text-slate-700 dark:text-slate-300">Vehicle Assigned</flux:text>
                                            <flux:text size="xs" class="text-slate-500">
                                                {{ $booking->vehicle->model }} ({{ $booking->vehicle->plate_number }})
                                            </flux:text>
                                        </div>
                                    </div>
                                @endif

                                @if ($booking->type === 'assessment' && $booking->status === 'completed')
                                    <div
                                        class="p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-900/20 flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <flux:icon icon="academic-cap"
                                                class="size-4 text-emerald-600 dark:text-emerald-400" />
                                            <flux:text size="xs" weight="bold"
                                                class="text-emerald-700 dark:text-emerald-300">Assessment Score
                                            </flux:text>
                                        </div>
                                        <flux:text size="sm" weight="black"
                                            class="text-emerald-600 dark:text-emerald-400">
                                            {{ $booking->score }}% - {{ $booking->is_passed ? 'PASSED' : 'FAILED' }}
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Footer: Actions --}}
                        <div
                            class="px-4 sm:px-5 py-3 bg-slate-50/80 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-2">
                            <flux:button variant="ghost" size="sm" class="flex-1 min-w-[80px]"
                                :href="route('instructor.student.show', $booking->enrollment)" wire:navigate
                                :disabled="Auth::user()->instructorProfile->isPending()">
                                View Student
                            </flux:button>
                            @if ($booking->status === 'scheduled' && $booking->type === 'pdc')
                                <flux:button variant="primary" size="sm" class="flex-1 min-w-[80px]"
                                    :disabled="Auth::user()->instructorProfile->isPending()"
                                    :href="route('instructor.assessment', ['enrollment' => $booking->enrollment_id, 'bookingSession' => $booking->id])"
                                    wire:navigate>
                                    Manage Assessment
                                </flux:button>
                            @endif
                            @if ($booking->status === 'scheduled')
                                <flux:button variant="danger" size="sm" class="flex-1 min-w-[80px]"
                                    wire:click="openCancelModal({{ $booking->id }})"
                                    :disabled="Auth::user()->instructorProfile->isPending()">
                                    Cancel
                                </flux:button>
                            @endif
                        </div>
                    </div>
                    

                @empty
                    <div class="col-span-full">
                        <x-empty-state 
                            variant="card" 
                            icon="calendar"
                            heading="No sessions found"
                            message="Try adjusting your filters or search terms."
                        />
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($this->bookings->hasPages())
                <div
                    class="px-4 sm:px-6 py-3 sm:py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    {{ $this->bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Cancel Session Modal --}}
    <flux:modal name="cancel-session" class="sm:max-w-md">
        <form wire:submit="cancelSession" class="space-y-6">
            <div>
                <flux:heading size="lg" weight="bold">Cancel Session</flux:heading>
                <flux:text size="sm" class="text-slate-500 dark:text-slate-400 mt-1">
                    This action cannot be undone. Please specify the reason for cancelling this session.
                </flux:text>
            </div>

            <flux:textarea wire:model="cancelReason" 
                label="Reason for Cancellation" 
                placeholder="Please enter a detailed reason (minimum 10 characters)..." 
                rows="3" 
                required />

            <div class="flex gap-2 justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Go Back</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger">Confirm Cancellation</flux:button>
            </div>
        </form>
    </flux:modal>
</div>