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
        $profile = Auth::user()->instructorProfile;
        if ($profile) {
            $this->is_active = $profile->is_active;
        }
    }

    public function updatedIsActive($value)
    {
        $profile = Auth::user()->instructorProfile;
        if ($profile) {
            $profile->update(['is_active' => $value]);
        }
    }

    #[Computed]
    public function metrics()
    {
        $profile = Auth::user()->instructorProfile;
        if (!$profile) {
            return (object) []; // or sensible default
        }
        $instructorId = $profile->id;
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

    #[Computed]
    public function performancesByCourse()
    {
        $profile = Auth::user()->instructorProfile;
        if (!$profile) {
            return collect();
        }
        $service = app(\App\Services\InstructorPerformanceService::class);
        return $service->getPerformancesByCourse($profile->id, 2);
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

        {{-- LEFT COLUMN: Operational & Analytics --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- TODAY'S AGENDA (From BOOKING_SESSION) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden font-sans">
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <flux:heading size="sm" weight="semibold">Today's Schedule</flux:heading>
                    <flux:button size="sm" variant="ghost" icon="calendar" href="{{ route('instructor.my-schedule') }}" wire:navigate>View Calendar</flux:button>
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
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors cursor-default">
                                <td class="px-6 py-4">
                                    <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400">
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
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                    <flux:text size="sm">PDC - Manual <flux:text class="opacity-50 text-[10px] font-bold uppercase ml-1 tracking-tighter">(Toyota Vios)</flux:text></flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="emerald" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">Completed</flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button variant="ghost" size="xs" icon="document-text" inset="top bottom">View</flux:button>
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
                                <td class="px-6 py-4 text-blue-900 dark:text-blue-100">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold">
                                            MC
                                        </div>
                                        <flux:text weight="bold">Maria Clara</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm" weight="semibold">PDC - Automatic (Honda City)</flux:text>
                                        <flux:text color="blue" size="xs" weight="black" class="mt-0.5 uppercase tracking-tighter animate-pulse text-[10px]">Starts in 45 mins</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge color="blue" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">
                                        <div class="size-1.5 rounded-full bg-blue-500 animate-pulse mr-1.5"></div>
                                        Up Next
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="sm" variant="primary" icon="play" class="shadow-sm shadow-blue-500/20">Start Session</flux:button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- COURSE PERFORMANCE OVERVIEW (Grouped per course) --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                            <flux:icon icon="presentation-chart-line" class="size-5" />
                        </div>
                        <flux:heading size="lg" weight="bold">Course Performance</flux:heading>
                    </div>
                    @if($this->performancesByCourse->count() > 0)
                        <flux:button href="{{ route('instructor.performance-reviews') }}" variant="subtle" size="xs" icon-trailing="arrow-right" wire:navigate>
                            View All Reviews
                        </flux:button>
                    @endif
                </div>

                @if ($this->performancesByCourse->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($this->performancesByCourse as $group)
                            <x-instructor-performance 
                                :courseTitle="$group->course->title"
                                :courseCode="$group->course->code"
                                :courseType="$group->course->type"
                                :avgRating="$group->avgRating"
                                :totalReviews="$group->totalReviews"
                                :avgCriteria="$group->avgCriteria"
                                :performances="$group->performances"
                                :lastEvaluationDate="$group->lastEvaluationDate"
                                :trend="$group->trend"
                                :ratingDistribution="$group->ratingDistribution"
                                :topStrengths="$group->topStrengths"
                                :topImprovements="$group->topImprovements"
                            />
                        @endforeach
                    </div>
                @else
                    <div class="p-10 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-center">
                        <flux:icon icon="star" class="size-10 text-slate-300 mx-auto mb-4" />
                        <flux:heading size="lg" class="text-slate-400 font-bold mb-2">No Performance Data Yet</flux:heading>
                        <flux:text class="text-slate-500 max-w-xs mx-auto">Complete more sessions and receive student feedback to see your analytics here.</flux:text>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: Profile & Compliance --}}
        <div class="space-y-6">
            {{-- STATUS WIDGET (Condensed version of earlier status) --}}
            <div class="p-5 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-500">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 size-3.5 rounded-full border-2 border-white dark:border-slate-900 {{ $is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                    </div>
                    <div>
                        <flux:text size="sm" weight="bold">{{ Auth::user()->name }}</flux:text>
                        <flux:text size="xs" class="text-slate-500">{{ $is_active ? 'Accepting Sessions' : 'On Leave' }}</flux:text>
                    </div>
                </div>
                <flux:switch wire:model.live="is_active" size="sm" />
            </div>

            {{-- LICENSE EXPIRY WIDGET --}}
            <div class="p-6 rounded-[2rem] border border-blue-100 bg-blue-50/50 dark:border-blue-900/30 dark:bg-blue-900/10 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 size-32 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors duration-500"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-500/20">
                            <flux:icon icon="identification" class="size-5" />
                        </div>
                        <flux:heading size="sm" weight="bold" class="text-blue-900 dark:text-blue-100">License Verification</flux:heading>
                    </div>
                    <flux:text size="sm" class="text-blue-800/70 dark:text-blue-300 font-medium leading-relaxed">
                        Your professional instructor license is verified and valid until <span class="font-bold text-blue-900 dark:text-white">October 2027</span>.
                    </flux:text>
                    <div class="mt-6">
                        <flux:button variant="primary" size="sm" class="w-full rounded-xl shadow-sm">View Digital Badge</flux:button>
                    </div>
                </div>
            </div>

            {{-- MAINTENANCE WIDGET --}}
            <div class="p-5 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <flux:heading size="xs" weight="bold" class="text-slate-400 uppercase tracking-widest mb-4">Upcoming Log</flux:heading>
                <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50">
                    <div class="p-2 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg">
                        <flux:icon icon="wrench" class="size-4" />
                    </div>
                    <div>
                        <flux:text size="xs" weight="bold" class="text-slate-800 dark:text-white">Toyota Vios (ABC-123)</flux:text>
                        <flux:text size="xs" class="text-slate-500">PMS Due: Feb 15, 2026</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

