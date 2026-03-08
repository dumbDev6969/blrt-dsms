<?php

use Livewire\Component;
use App\Models\EnrollmentForm;
use Livewire\Attributes\Computed;

new class extends Component {
    #[Computed]
    public function enrollmentStatus()
    {
        return [
            'pending' => EnrollmentForm::where('status', 'submitted')->count(),
            'approved' => EnrollmentForm::where('status', 'approved')->count(),
            'rejected' => EnrollmentForm::where('status', 'rejected')->count(),
        ];
    }

    
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Operational Overview --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Staff Operations Dashboard
            </flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Daily Operational Oversight
            </flux:text>
        </div>
        <div class="flex gap-3">
            <flux:button variant="ghost" icon="calendar-days">Master Schedule</flux:button>
            <flux:button variant="filled" icon="plus" color="primary">New Enrollment</flux:button>
        </div>
    </div>

    {{-- SECTION 1: ENROLLMENT STATUS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Pending Review --}}
        <div
            class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Pending Review
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentStatus['pending'] }}</flux:heading>
                <flux:text>forms awaiting validation</flux:text>
            </div>
        </div>

        {{-- Rejected --}}
        <div
            class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Rejected
                </flux:text>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentStatus['rejected'] }}</flux:heading>
                <flux:text>requires correction</flux:text>
            </div>
        </div>

        {{-- Ongoing --}}
        <div
            class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Approved
                </flux:text>
                <div class="p-2 bg-green-50 text-green-600 rounded-lg dark:bg-green-900/20 dark:text-green-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentStatus['approved'] }}</flux:heading>
                <flux:text>active students</flux:text>
            </div>
        </div>
    </div>

    {{-- SECTION 2: MAIN OPERATIONS (Split View) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2/3 width): Main Operations --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ENROLLMENT VALIDATION QUEUE (Sample Records) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900">
                    <div>
                        <flux:heading size="lg" weight="bold">Enrollment Validation</flux:heading>
                        <flux:text size="xs" class="mt-1">Review recently submitted applications</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" :href="route('staff.manage-enrollments')" icon="arrow-right" wire:navigate>View All Queue</flux:button>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{-- Sample Form 1 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-12 h-12 bg-blue-50 rounded-lg dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900">
                            <flux:text size="xs" weight="bold" class="text-blue-600">TDC</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Maria Santos</flux:heading>
                                <flux:badge size="sm" color="zinc" variant="subtle">TDC-2026-0234</flux:badge>
                            </div>
                            <flux:text size="xs" class="mt-1">
                                Course: <flux:text weight="medium">Theoretical Driving Course</flux:text> •
                                Submitted: 2 hours ago
                            </flux:text>
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary">Validate</flux:button>
                            <flux:button size="sm" variant="ghost" icon="eye"></flux:button>
                        </div>
                    </div>

                    {{-- Sample Form 2 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-12 h-12 bg-purple-50 rounded-lg dark:bg-purple-900/20 border border-purple-100 dark:border-purple-900">
                            <flux:text size="xs" weight="bold" class="text-purple-600">PDC</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Juan Dela Cruz</flux:heading>
                                <flux:badge size="sm" color="zinc" variant="subtle">PDC-2026-0451</flux:badge>
                            </div>
                            <flux:text size="xs" class="mt-1">
                                Course: <flux:text weight="medium">Practical Driving Course (Manual)</flux:text> •
                                Submitted: 5 hours ago
                            </flux:text>
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary">Validate</flux:button>
                            <flux:button size="sm" variant="ghost" icon="eye"></flux:button>
                        </div>
                    </div>

                    {{-- Sample Form 3 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-12 h-12 bg-emerald-50 rounded-lg dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900">
                            <flux:text size="xs" weight="bold" class="text-emerald-600">REF</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Ana Reyes</flux:heading>
                                <flux:badge size="sm" color="zinc" variant="subtle">REF-2026-0089</flux:badge>
                            </div>
                            <flux:text size="xs" class="mt-1">
                                Course: <flux:text weight="medium">Refresher Course (Motorcycle)</flux:text> •
                                Submitted: 1 day ago
                            </flux:text>
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary">Validate</flux:button>
                            <flux:button size="sm" variant="ghost" icon="eye"></flux:button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MASTER SCHEDULE SNAPSHOT (Sample Records) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900">
                    <div>
                        <flux:heading size="lg" weight="bold">Today's Schedule Monitor</flux:heading>
                        <flux:text size="xs" class="mt-1">Real-time session oversight</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:badge color="emerald" variant="subtle" size="sm">Active</flux:badge>
                        <flux:badge color="blue" variant="subtle" size="sm">Scheduled</flux:badge>
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-zinc-900 dark:text-white">Time</th>
                                <th class="px-6 py-3 font-semibold text-zinc-900 dark:text-white">Session</th>
                                <th class="px-6 py-3 font-semibold text-zinc-900 dark:text-white">Instructor & Vehicle
                                </th>
                                <th class="px-6 py-3 font-semibold text-zinc-900 dark:text-white">Status</th>
                                <th class="px-6 py-3 font-semibold text-zinc-900 dark:text-white"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            {{-- Sample Session 1 --}}
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="xs" weight="bold" class="font-mono text-emerald-600">
                                            08:00 AM</flux:text>
                                        <flux:text size="xs" class="text-slate-400">10:00 AM</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm" weight="medium">Maria Clara</flux:text>
                                        <flux:text size="xs" class="text-slate-500">PDC - Manual</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="xs" weight="medium">Alex Cruz</flux:text>
                                        <flux:text size="xs" class="text-slate-500">Toyota Vios (ABC-123)
                                        </flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="emerald" size="sm" variant="subtle">Active</flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button>
                                </td>
                            </tr>

                            {{-- Sample Session 2 --}}
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="xs" weight="bold" class="font-mono text-blue-600">
                                            01:00 PM</flux:text>
                                        <flux:text size="xs" class="text-slate-400">03:00 PM</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm" weight="medium">Jose Rizal</flux:text>
                                        <flux:text size="xs" class="text-slate-500">PDC - Automatic</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="xs" weight="medium">Beth Tan</flux:text>
                                        <flux:text size="xs" class="text-slate-500">Honda City (XYZ-789)
                                        </flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="blue" size="sm" variant="subtle">Scheduled</flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- RIGHT COLUMN (1/3 width): Quick Actions & Alerts --}}
        <div class="space-y-6">

            {{-- DAILY PROGRESS SNAPSHOT --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <flux:heading size="sm" weight="bold" class="mb-4">Today's Progress</flux:heading>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <flux:text size="xs" weight="medium" class="text-slate-500">Validated Enrollments
                            </flux:text>
                            <flux:text size="xs" weight="bold">12 / 18</flux:text>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: 66%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <flux:text size="xs" weight="medium" class="text-slate-500">Completed Sessions
                            </flux:text>
                            <flux:text size="xs" weight="bold">24 / 42</flux:text>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: 57%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
