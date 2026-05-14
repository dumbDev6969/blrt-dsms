@props(['enrollment', 'showInstructor' => false])

@php
    $statusConfig = [
        'pending' => ['color' => 'amber', 'label' => 'Pending'],
        'active' => ['color' => 'emerald', 'label' => 'Active'],
        'completed' => ['color' => 'blue', 'label' => 'Completed'],
        'dropped' => ['color' => 'red', 'label' => 'Dropped'],
    ];
    $config = $statusConfig[$enrollment->status] ?? [
        'color' => 'zinc',
        'label' => $enrollment->status,
    ];
@endphp

<div
    class="group flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-300 overflow-hidden">

    {{-- Card Header: Student & Course --}}
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
        <div class="flex justify-between items-start gap-2 mb-3 sm:mb-4">
            <div class="flex gap-3 items-center min-w-0">
                <div
                    class="h-9 w-9 sm:h-10 sm:w-10 rounded-full shrink-0 bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold border border-blue-200 dark:border-blue-800">
                    {{ substr($enrollment->studentProfile->user->name ?? 'S', 0, 1) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <flux:text weight="bold"
                        class="text-sm sm:text-base text-slate-900 dark:text-white leading-tight truncate">
                        {{ $enrollment->studentProfile->user->name ?? 'N/A' }}
                    </flux:text>
                    <flux:text size="xs" class="text-slate-500 mt-0.5 truncate">
                        {{ $enrollment->code }}
                    </flux:text>
                </div>
            </div>
            <flux:badge :color="$config['color']" variant="subtle" size="xs"
                class="capitalize tracking-wide shrink-0">
                {{ $config['label'] }}
            </flux:badge>
        </div>

        <div class="flex flex-col gap-1">
            <flux:text size="sm" weight="semibold"
                class="text-slate-800 dark:text-slate-200 line-clamp-2 sm:line-clamp-1">
                {{ $enrollment->course->title ?? 'N/A' }}
            </flux:text>
        </div>
    </div>

    {{-- Card Body: Progress & Stats --}}
    <div class="p-4 sm:p-5 flex-1 flex flex-col gap-4 sm:gap-5">

        {{-- Overall Progress --}}
        <div class="space-y-1.5 sm:space-y-2">
            <div class="flex justify-between items-center text-xs sm:text-sm">
                <flux:text class="text-slate-600 dark:text-slate-400">Course Progress</flux:text>
                <flux:text weight="bold"
                    class="{{ $enrollment->progress_percent == 100 ? 'text-emerald-600' : 'text-blue-600' }}">
                    {{ $enrollment->progress_percent }}%
                </flux:text>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 sm:h-2 overflow-hidden">
                <div class="{{ $enrollment->progress_percent == 100 ? 'bg-emerald-500' : 'bg-blue-500' }} h-full rounded-full transition-all duration-500"
                    style="width: {{ $enrollment->progress_percent }}%"></div>
            </div>
        </div>

        {{-- Dynamic Modules --}}
        <div class="grid grid-cols-1 gap-2 sm:gap-3">
            {{-- TDC Module --}}
            @if ($enrollment->tdc_hours_required > 0)
                <div
                    class="flex items-center justify-between p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div
                            class="p-1.5 sm:p-2 bg-amber-50 dark:bg-amber-900/10 text-amber-600 dark:text-amber-400 rounded-lg shrink-0">
                            <flux:icon icon="book-open" class="size-3.5 sm:size-4" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <flux:text size="xs" weight="semibold"
                                class="text-slate-700 dark:text-slate-300 truncate">Theory (TDC)
                            </flux:text>
                            <flux:text size="xs" class="text-slate-500 truncate">
                                {{ $enrollment->tdc_hours_completed }}/{{ $enrollment->tdc_hours_required }}
                                hrs
                            </flux:text>
                        </div>
                    </div>
                    <x-course-status-color :status="$enrollment->tdc_status" class="hidden sm:inline-flex" />
                </div>
            @endif

            {{-- PDC Module --}}
            @if ($enrollment->pdc_hours_required > 0)
                <div
                    class="flex items-center justify-between p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div
                            class="p-1.5 sm:p-2 bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 rounded-lg shrink-0">
                            <flux:icon icon="truck" class="size-3.5 sm:size-4" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <flux:text size="xs" weight="semibold"
                                class="text-slate-700 dark:text-slate-300 truncate">Practical (PDC)
                            </flux:text>
                            <flux:text size="xs" class="text-slate-500 truncate">
                                {{ $enrollment->pdc_hours_completed }}/{{ $enrollment->pdc_hours_required }}
                                hrs
                            </flux:text>
                        </div>
                    </div>
                    <x-course-status-color :status="$enrollment->pdc_status" class="hidden sm:inline-flex" />
                </div>
            @endif
        </div>

        {{-- Instructor Details (Optional for Admin Pages) --}}
        @if ($showInstructor && $enrollment->instructorProfile)
            <div
                class="mt-2 p-2 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100/50 dark:border-blue-800/30">
                <div class="flex items-center gap-2">
                    <flux:icon icon="user-circle" class="size-4 text-blue-600 dark:text-blue-400" />
                    <div class="flex flex-col">
                        <flux:text size="xs" class="text-slate-500">Assigned Instructor</flux:text>
                        <flux:text size="sm" weight="semibold" class="text-blue-700 dark:text-blue-300">
                            {{ $enrollment->instructorProfile->user->name ?? 'None' }}
                        </flux:text>
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline & Grades --}}
        <div
            class="grid grid-cols-2 gap-2 sm:gap-4 mt-auto pt-3 sm:pt-4 border-t border-slate-100 dark:border-slate-800/50">
            <div class="flex flex-col">
                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-semibold mb-0.5 sm:mb-1">
                    Target Date</flux:text>
                <flux:text size="xs" sm:size="sm" weight="medium" class="text-slate-700 dark:text-slate-300">
                    {{ $enrollment->target_completion_date ? $enrollment->target_completion_date->format('M d, y') : 'Not Set' }}
                </flux:text>
            </div>
            <div class="flex flex-col items-end">
                <flux:text size="xs" class="text-slate-400 uppercase tracking-wider font-semibold mb-0.5 sm:mb-1">
                    Final Grade</flux:text>
                <flux:text size="xs" sm:size="sm" weight="bold"
                    class="{{ $enrollment->final_grade ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                    {{ $enrollment->final_grade ?? '---' }}
                </flux:text>
            </div>
        </div>

    </div>

    {{-- Card Footer: Finances & Actions --}}
    <div class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/20">

        {{-- Financial Context --}}
        <div
            class="px-4 sm:px-5 py-2.5 sm:py-3 flex justify-between items-center border-b border-slate-100 dark:border-slate-800/50">
            <flux:text size="xs" class="text-slate-500">Paid:
                ₱{{ number_format($enrollment->amount_paid, 0) }}</flux:text>
            <flux:text size="xs" class="text-slate-500 font-medium">
                Bal: <span
                    class="{{ $enrollment->balance > 0 ? 'text-red-500 font-bold' : 'text-emerald-500 font-bold' }}">₱{{ number_format($enrollment->balance, 0) }}</span>
            </flux:text>
        </div>

        {{-- Actions --}}
        @if (isset($actions))
            <div class="px-4 sm:px-5 py-3 flex flex-col sm:flex-row gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
