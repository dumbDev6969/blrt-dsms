<?php

use Livewire\Component;
use App\Models\Enrollment;

new class extends Component {
    public Enrollment $enrollment;

    public function mount(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->load(['studentProfile.user', 'course', 'instructorProfile.user']);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100 p-1 sm:p-0">

    {{-- Header & Navigation --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('instructor.my-students')"
                    wire:navigate>Back to Students</flux:button>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mt-2">
                <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Student Details:
                    {{ $enrollment->studentProfile->user->name ?? 'Unknown Student' }}</flux:heading>
                @php
                    $statusConfig = [
                        'pending' => ['color' => 'amber', 'label' => 'Pending'],
                        'active' => ['color' => 'emerald', 'label' => 'Active'],
                        'completed' => ['color' => 'blue', 'label' => 'Completed'],
                        'dropped' => ['color' => 'red', 'label' => 'Dropped'],
                    ];
                    $config = $statusConfig[$enrollment->status] ?? ['color' => 'zinc', 'label' => $enrollment->status];
                @endphp
                <flux:badge :color="$config['color']" variant="subtle" size="sm" class="capitalize w-fit">
                    {{ $config['label'] }}</flux:badge>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="primary" icon="pencil-square" :disabled="Auth::user()->instructorProfile->isPending()">Update Progress</flux:button>
            <flux:dropdown>
                <flux:button variant="ghost" icon="ellipsis-horizontal" :disabled="Auth::user()->instructorProfile->isPending()" />
                <flux:menu>
                    <flux:menu.item icon="calendar-days">Schedule Session</flux:menu.item>
                    <flux:menu.item icon="chat-bubble-left-right">Message Student</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- Main Dashboard Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div
                class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all duration-300">
                <flux:heading size="lg" class="mb-5 sm:mb-6 font-bold text-slate-900 dark:text-white">Enrollment
                    Overview</flux:heading>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div
                        class="flex flex-col p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <flux:text size="xs"
                            class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold mb-1">Code
                        </flux:text>
                        <flux:text size="sm" weight="bold" class="font-mono text-blue-600 dark:text-blue-400">
                            {{ $enrollment->code }}</flux:text>
                    </div>
                    <div
                        class="flex flex-col p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <flux:text size="xs"
                            class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold mb-1">Start
                            Date</flux:text>
                        <flux:text size="sm" weight="bold" class="text-slate-900 dark:text-white">
                            {{ $enrollment->start_date ? $enrollment->start_date->format('M d, Y') : 'Not Set' }}
                        </flux:text>
                    </div>
                    <div
                        class="flex flex-col p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <flux:text size="xs"
                            class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold mb-1">Target
                        </flux:text>
                        <flux:text size="sm" weight="bold" class="text-slate-900 dark:text-white">
                            {{ $enrollment->target_completion_date ? $enrollment->target_completion_date->format('M d, Y') : 'Not Set' }}
                        </flux:text>
                    </div>
                    <div
                        class="flex flex-col p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <flux:text size="xs"
                            class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-bold mb-1">Grade
                        </flux:text>
                        <flux:text size="sm" weight="bold"
                            class="{{ $enrollment->final_grade ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-400' }}">
                            {{ $enrollment->final_grade ?? 'N/A' }}</flux:text>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm font-bold">
                        <flux:text weight="bold" class="text-slate-700 dark:text-slate-300">Total Learning Progress
                        </flux:text>
                        <flux:text weight="bold" class="text-blue-600 dark:text-blue-400 text-lg">
                            {{ $enrollment->progress_percent }}%</flux:text>
                    </div>
                    <div
                        class="relative h-4 sm:h-5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700/50 shadow-inner">
                        <div class="h-full bg-blue-600 dark:bg-blue-500 rounded-full transition-all duration-700 ease-in-out shadow-lg"
                            style="width: {{ $enrollment->progress_percent }}%">
                            <div class="h-full w-full bg-gradient-to-r from-transparent via-white/10 to-transparent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Course Modules Section (Dynamic Grid based on Module Count) --}}
            @php
                $hasTdc = $enrollment->tdc_hours_required > 0;
                $hasPdc = $enrollment->pdc_hours_required > 0;
                $moduleCount = ($hasTdc ? 1 : 0) + ($hasPdc ? 1 : 0);
            @endphp

            <div class="grid grid-cols-1 {{ $moduleCount == 2 ? 'md:grid-cols-2' : '' }} gap-6">
                {{-- TDC Module Card --}}
                @if ($hasTdc)
                    <div
                        class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden group hover:border-amber-500/30 dark:hover:border-amber-500/50 transition-all duration-300">
                        <div class="absolute top-0 right-0 p-4">
                            @php
                                $tdcStatusConfig = [
                                    'not_started' => ['color' => 'zinc', 'label' => 'Pending'],
                                    'in_progress' => ['color' => 'blue', 'label' => 'Active'],
                                    'completed' => ['color' => 'emerald', 'label' => 'Finished'],
                                ];
                                $tdcConf = $tdcStatusConfig[$enrollment->tdc_status] ?? $tdcStatusConfig['not_started'];
                            @endphp
                            <flux:badge :color="$tdcConf['color']" variant="subtle" size="xs" class="font-bold">
                                {{ $tdcConf['label'] }}</flux:badge>
                        </div>

                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="p-3 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-xl border border-amber-100 dark:border-amber-900/40 shadow-sm shrink-0">
                                <flux:icon icon="book-open" class="size-6" />
                            </div>
                            <div>
                                <flux:heading size="md" class="font-bold">Theoretical (TDC)</flux:heading>
                                <flux:text size="xs" class="text-slate-500 dark:text-slate-400 font-medium">
                                    Classroom Knowledge</flux:text>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="flex justify-between items-center px-1 font-bold text-sm">
                                <flux:text class="text-slate-700 dark:text-slate-300">Hours Completed</flux:text>
                                <flux:text class="text-blue-600 dark:text-blue-400">
                                    {{ $enrollment->tdc_hours_completed }} / {{ $enrollment->tdc_hours_required }} hrs
                                </flux:text>
                            </div>
                            <div
                                class="h-2.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700/50 shadow-inner">
                                @php $tdcPerc = $enrollment->tdc_hours_required > 0 ? ($enrollment->tdc_hours_completed / $enrollment->tdc_hours_required) * 100 : 0; @endphp
                                <div class="h-full bg-amber-500 dark:bg-amber-400 rounded-full shadow-sm transition-all duration-500"
                                    style="width: {{ min(100, $tdcPerc) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- PDC Module Card --}}
                @if ($hasPdc)
                    <div
                        class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 dark:hover:border-indigo-500/50 transition-all duration-300">
                        <div class="absolute top-0 right-0 p-4">
                            @php
                                $pdcStatusConfig = [
                                    'not_started' => ['color' => 'zinc', 'label' => 'Pending'],
                                    'in_progress' => ['color' => 'blue', 'label' => 'Active'],
                                    'completed' => ['color' => 'emerald', 'label' => 'Finished'],
                                ];
                                $pdcConf = $pdcStatusConfig[$enrollment->pdc_status] ?? $pdcStatusConfig['not_started'];
                            @endphp
                            <flux:badge :color="$pdcConf['color']" variant="subtle" size="xs" class="font-bold">
                                {{ $pdcConf['label'] }}</flux:badge>
                        </div>

                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="p-3 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-100 dark:border-indigo-900/40 shadow-sm shrink-0">
                                <flux:icon icon="truck" class="size-6" />
                            </div>
                            <div>
                                <flux:heading size="md" class="font-bold">Practical (PDC)</flux:heading>
                                <flux:text size="xs" class="text-slate-500 dark:text-slate-400 font-medium">On-Road
                                    Performance</flux:text>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center px-1 font-bold text-sm">
                                    <flux:text class="text-slate-700 dark:text-slate-300">Practical Hours</flux:text>
                                    <flux:text class="text-blue-600 dark:text-blue-400">
                                        {{ $enrollment->pdc_hours_completed }} / {{ $enrollment->pdc_hours_required }}
                                        hrs</flux:text>
                                </div>
                                <div
                                    class="h-2.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700/50 shadow-inner">
                                    @php $pdcPerc = $enrollment->pdc_hours_required > 0 ? ($enrollment->pdc_hours_completed / $enrollment->pdc_hours_required) * 100 : 0; @endphp
                                    <div class="h-full bg-indigo-500 dark:bg-indigo-400 rounded-full shadow-sm transition-all duration-500"
                                        style="width: {{ min(100, $pdcPerc) }}%"></div>
                                </div>
                            </div>

                            <div
                                class="flex justify-between items-center p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/50">
                                <flux:text size="sm" class="font-bold text-slate-600 dark:text-slate-400">
                                    Distance Logged</flux:text>
                                <flux:text size="sm" weight="bold"
                                    class="text-indigo-600 dark:text-indigo-400 font-mono">
                                    {{ $enrollment->pdc_kms_driven }} km</flux:text>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 3. Course & Financial Ledger (Moved to Main Content) --}}
            <div
                class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-stretch gap-6 lg:gap-8">
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                    <flux:icon icon="credit-card" class="size-32" />
                </div>

                {{-- Left Side: Course Info --}}
                <div class="flex-1 flex flex-col justify-center relative z-10">
                    <flux:heading size="md" class="mb-5 font-bold flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span>
                        Registered Course
                    </flux:heading>

                    <div
                        class="p-4 bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-900/30 rounded-xl border border-slate-100 dark:border-slate-800">
                        <flux:text size="lg" weight="bold"
                            class="text-slate-800 dark:text-slate-200 leading-snug">
                            {{ $enrollment->course->title ?? 'N/A' }}</flux:text>
                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <flux:badge size="sm" variant="solid" color="zinc"
                                class="font-bold tracking-wider px-2">{{ $enrollment->course->code ?? 'N/A' }}
                            </flux:badge>
                            <flux:badge size="sm" color="blue" variant="subtle"
                                class="font-extrabold tracking-wider px-2 uppercase">
                                {{ $enrollment->course->type ?? 'N/A' }}</flux:badge>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="hidden md:block w-px bg-slate-100 dark:bg-slate-800 my-2"></div>

                {{-- Right Side: Ledger --}}
                <div class="flex-1 flex flex-col justify-center relative z-10">
                    <flux:heading size="md" class="mb-5 font-bold flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                        Financial Ledger
                    </flux:heading>

                    <div class="space-y-4 px-1">
                        <div class="flex justify-between items-center text-sm font-medium">
                            <flux:text class="text-slate-500 dark:text-slate-400">Total Course Fee</flux:text>
                            <flux:text weight="bold" class="text-slate-900 dark:text-white">
                                ₱{{ number_format($enrollment->total_amount, 2) }}</flux:text>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <flux:text
                                class="text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wide text-xs">
                                Total Payments</flux:text>
                            <flux:text weight="bold" class="text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($enrollment->amount_paid, 2) }}</flux:text>
                        </div>
                        <div
                            class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <flux:text weight="extrabold"
                                class="text-slate-900 dark:text-white uppercase tracking-tight">Balance Due</flux:text>
                            <flux:text size="xl" weight="bold"
                                class="{{ $enrollment->balance > 0 ? 'text-red-500 dark:text-red-400' : 'text-emerald-600' }} font-bold">
                                ₱{{ number_format($enrollment->balance, 2) }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-6">

            {{-- Student Info --}}
            <div
                class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <flux:icon icon="user" class="size-32" />
                </div>

                <flux:heading size="md" class="mb-6 font-bold flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                    Student Profile
                </flux:heading>

                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="h-14 w-14 sm:h-16 sm:w-16 rounded-full shrink-0 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/20 flex items-center justify-center text-blue-600 dark:text-blue-400 text-2xl font-bold border-2 border-white dark:border-slate-800 shadow-md relative group">
                        {{ substr($enrollment->studentProfile->user->name ?? 'S', 0, 1) }}
                        <div
                            class="absolute -bottom-1 -right-1 h-4 w-4 sm:h-5 sm:w-5 bg-emerald-500 border-2 border-white dark:border-slate-800 rounded-full ring-2 ring-emerald-500/20">
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <flux:text weight="bold"
                            class="text-lg sm:text-xl leading-tight text-slate-900 dark:text-white truncate">
                            {{ $enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                        <flux:text size="xs"
                            class="text-slate-500 dark:text-slate-400 mt-0.5 font-medium tracking-wide uppercase">
                            Active Student</flux:text>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    <div
                        class="group flex flex-col p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-200 cursor-default">
                        <flux:text size="xs"
                            class="text-slate-400 dark:text-slate-500 uppercase tracking-widest font-extrabold mb-1.5">
                            Email Contact</flux:text>
                        <flux:text size="sm" weight="bold" class="text-slate-800 dark:text-slate-200 truncate">
                            {{ $enrollment->studentProfile->user->email ?? 'N/A' }}</flux:text>
                    </div>
                    <div
                        class="group flex flex-col p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-200 cursor-default">
                        <flux:text size="xs"
                            class="text-slate-400 dark:text-slate-500 uppercase tracking-widest font-extrabold mb-1.5">
                            Phone Number</flux:text>
                        <flux:text size="sm" weight="bold" class="text-slate-800 dark:text-slate-200">
                            {{ $enrollment->studentProfile->phone ?? 'Not provided' }}</flux:text>
                    </div>
                </div>
            </div>

            {{-- Instructor Details --}}
            <div
                class="p-5 sm:p-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <flux:icon icon="academic-cap" class="size-20" />
                </div>
                <flux:heading size="md" class="mb-5 font-bold flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                    Assigned Instructor
                </flux:heading>
                <div class="flex items-center gap-4">
                    <div
                        class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-200 dark:border-indigo-800 shrink-0">
                        {{ substr($enrollment->instructorProfile->user->name ?? 'I', 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <flux:text weight="bold" class="text-slate-900 dark:text-white">
                            {{ $enrollment->instructorProfile->user->name ?? 'Unassigned' }}</flux:text>
                        <flux:text size="xs" class="text-slate-500 dark:text-slate-400">Primary Instructor
                        </flux:text>
                    </div>
                </div>
            </div>

            {{-- Audit/Timestamps --}}
            <div class="flex flex-col items-center justify-center gap-1 opacity-60 mt-4">
                <flux:text size="xs" class="text-slate-400 font-mono text-center">
                    Enrolled: {{ $enrollment->created_at ? $enrollment->created_at->format('M d, Y h:i A') : 'N/A' }}
                </flux:text>
                <flux:text size="xs" class="text-slate-400 font-mono text-center">
                    Last Update: {{ $enrollment->updated_at ? $enrollment->updated_at->diffForHumans() : 'N/A' }}
                </flux:text>
            </div>

        </div>
    </div>
</div>
