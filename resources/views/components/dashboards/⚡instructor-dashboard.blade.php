<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\InstructorMetric;
use Livewire\Attributes\Computed;

new class extends Component {
    public $is_active;

    public function mount()
    {
        $this->is_active = Auth::user()->instructorProfile->is_active;
    }

    public function updatedIsActive($value)
    {
        Auth::user()->instructorProfile->update(['is_active' => $value]);
    }

    #[Computed]
    public function metrics()
    {
        $instructorId = Auth::user()->instructorProfile->id;
        $metric = InstructorMetric::where('instructor_id', $instructorId)
            ->where('metric_month', now()->startOfMonth()->format('Y-m-d'))
            ->first();

        if (!$metric) {
            return (object) [
                'metric_month' => now()->startOfMonth(),
                'total_sessions' => 0,
                'completed_sessions' => 0,
                'total_hours' => 0,
                'avg_rating' => 0,
                'students_taught' => 0,
                'students_passed' => 0,
                'pass_rate' => 0,
            ];
        }

        return $metric;
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Welcome & Status --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <flux:heading size="xl" class="font-bold tracking-tight">{{ Auth::user()->name }}</flux:heading>
                <flux:badge color="blue" variant="subtle" size="sm" class="uppercase font-bold tracking-widest px-2">
                    {{ \Carbon\Carbon::parse($this->metrics->metric_month)->format('F Y') }}
                </flux:badge>
            </div>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • <flux:text color="{{ $is_active ? 'emerald' : 'rose' }}" weight="medium">{{ $is_active ? 'Accepting Sessions' : 'On Break' }}</flux:text>
            </flux:text>
        </div>
        <div class="flex gap-3">
            <flux:button variant="filled" icon="plus">Log Session</flux:button>
        </div>
    </div>

    {{-- SECTION 1: KEY METRICS (Derived from INSTRUCTOR_METRIC table) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Metric: Sessions --}}
        <x-kpi-cards
            label="Total Sessions"
            :value="$this->metrics->total_sessions"
            icon="calendar-days"
            color="blue"
            :subtext="'Goal: ' . round($this->metrics->total_sessions * 1.2) . ' next month'"
        />

        {{-- Metric: Completed Sessions --}}
        <x-kpi-cards
            label="Completed"
            :value="$this->metrics->completed_sessions"
            icon="check-circle"
            color="emerald"
            :sublabel="$this->metrics->total_sessions > 0 ? round(($this->metrics->completed_sessions / $this->metrics->total_sessions) * 100) . '% completion' : '0% completion'"
        />

        {{-- Metric: Teaching Hours --}}
        <x-kpi-cards
            label="Hours Taught"
            :value="number_format($this->metrics->total_hours, 1) . ' hrs'"
            icon="clock"
            color="orange"
            subtext="Productivity this month"
        />

        {{-- Metric: Rating --}}
        <x-kpi-cards
            label="Avg. Rating"
            :value="number_format($this->metrics->avg_rating, 1)"
            trend="/ 5.0"
            trend-color="zinc"
            icon="star"
            color="yellow"
            subtext="Student feedback"
        />

        {{-- Metric: Students Taught --}}
        <x-kpi-cards
            label="Students Taught"
            :value="$this->metrics->students_taught"
            icon="user-group"
            color="blue"
            subtext="Unique enrollees"
        />

        {{-- Metric: Students Passed --}}
        <x-kpi-cards
            label="Students Passed"
            :value="$this->metrics->students_passed"
            icon="hand-thumb-up"
            color="emerald"
            subtext="Certification ready"
        />

        {{-- Metric: Student Pass Rate --}}
        <x-kpi-cards
            label="Student Pass Rate"
            :value="number_format($this->metrics->pass_rate, 0) . '%'"
            icon="academic-cap"
            color="purple"
            :subtext="'Top ' . (100 - round($this->metrics->pass_rate)) . '% of instructors'"
        />

        {{-- Metric: Efficiency (Custom) --}}
        <x-kpi-cards
            label="Session Density"
            :value="$this->metrics->total_sessions > 0 ? number_format($this->metrics->total_hours / max(1, $this->metrics->total_sessions), 1) : '0.0'"
            icon="bolt"
            color="amber"
            subtext="Hours per session"
        />
    </div>

    {{-- SECTION 2: MAIN WORKSPACE (Split View) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

        {{-- LEFT COLUMN: Operational (Agenda & Tasks) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- TODAY'S AGENDA (From BOOKING_SESSION) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" weight="semibold">Today's Schedule</flux:heading>
                    <flux:button size="sm" variant="ghost" icon="calendar">View Calendar</flux:button>
                </div>

                {{-- Table --}}
                <div class="overflow-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Time</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Student</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Session Details</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                            {{-- Row 1: Completed --}}
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        08:00 AM
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold">
                                            JD
                                        </div>
                                        <flux:text weight="medium">Juan Dela Cruz</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:text size="sm">PDC - Manual <flux:text class="opacity-50">(Toyota Vios)</flux:text></flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="emerald" variant="subtle" size="sm">Completed</flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:menu.item icon="document-text">View Report</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>

                            {{-- Row 2: Up Next (Active) --}}
                            <tr
                                class="bg-blue-50/30 dark:bg-blue-900/10 group hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text color="blue" size="xs" weight="bold" class="font-mono bg-blue-100/50 dark:bg-blue-900/30 px-2 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                                        01:00 PM
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold">
                                            MC
                                        </div>
                                        <flux:text weight="medium">Maria Clara</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm">PDC - Automatic (Honda City)</flux:text>
                                        <flux:text color="blue" size="xs" weight="medium" class="mt-0.5">Starts in 45 mins</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="blue" variant="subtle" size="sm">
                                        <div class="size-1.5 rounded-full bg-blue-500 animate-pulse mr-1.5"></div>
                                        Up Next
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="sm" variant="primary" icon="play">Start</flux:button>
                                </td>
                            </tr>

                            {{-- Row 3: Future --}}
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        03:30 PM
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold">
                                            JR
                                        </div>
                                        <flux:text weight="medium">Jose Rizal</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:text size="sm">Final Practical Exam</flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge variant="subtle" size="sm">Scheduled</flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square">Edit Time</flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger">Cancel</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- RECENT FEEDBACK (From INSTRUCTOR_PERFORMANCE) --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <flux:heading size="lg" weight="bold">Recent Student Feedback</flux:heading>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-4 items-start">
                        <div
                            class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold">
                            JD</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <flux:text size="sm" weight="semibold">Juan D.</flux:text>
                                <div class="flex text-yellow-400 text-xs text-[var(--color-yellow-400)]">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                </div>
                            </div>
                            <flux:text size="sm" class="text-slate-600 dark:text-slate-400 mt-1">"Sir is very patient with my
                                parking skills. Explained the reference points clearly."</flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1 block">2 days ago</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Management --}}
        <div class="space-y-6">

            {{-- PROFILE & AVAILABILITY --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
                <flux:heading size="lg" weight="bold" class="mb-4">Availability</flux:heading>

                {{-- Toggle Logic linked to 'is_active' or specific schedule --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex flex-col">
                        <flux:text size="sm" weight="medium">Accepting New Students</flux:text>
                        <flux:text size="xs" class="text-slate-500">Visible in booking system</flux:text>
                    </div>
                    <flux:switch wire:model="is_active" />
                </div>

                <flux:heading size="xs" weight="semibold" class="text-slate-500 uppercase mb-3">Next Maintenance</flux:heading>
                <div
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <flux:icon icon="wrench" class="size-5 text-slate-400" />
                    <div>
                        <flux:text size="sm" weight="medium">Toyota Vios (ABC-123)</flux:text>
                        <flux:text size="xs" class="text-slate-500">Due: Feb 15, 2026</flux:text>
                    </div>
                </div>
            </div>

            {{-- QUICK LINKS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
                <flux:heading size="lg" weight="bold" class="mb-4">Quick Actions</flux:heading>
                <div class="space-y-2">
                    <flux:button variant="ghost" class="w-full !justify-start" icon="clipboard-document-list">
                        Submit Incident Report
                    </flux:button>
                    <flux:button variant="ghost" class="w-full !justify-start" icon="user-group">
                        View Student List
                    </flux:button>
                    <flux:button variant="ghost" class="w-full !justify-start" icon="document-text">
                        Update License Info
                    </flux:button>
                </div>
            </div>

            {{-- LICENSE EXPIRY WIDGET --}}
            <div class="p-5 rounded-xl border border-blue-100 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/10 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-start gap-3">
                    <flux:icon icon="identification" class="size-6 text-blue-600 dark:text-blue-400" />
                    <div>
                        <flux:heading size="sm" weight="bold" class="text-blue-900 dark:text-blue-100">License Status</flux:heading>
                        <flux:text size="xs" class="text-blue-700 dark:text-blue-300 mt-1">Valid until Oct 2027</flux:text>
                        <flux:button variant="subtle" size="xs" class="mt-2 -ml-2" color="blue">
                            View Digital ID
                        </flux:button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
