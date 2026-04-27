<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\BookingSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\InstructorMetric;
use Livewire\Attributes\Computed;

new class extends Component {
    public $accepting_sessions;

    public function mount()
    {
        $profile = Auth::user()->instructorProfile;
        if ($profile) {
            $this->accepting_sessions = $profile->status === 'verified';
        }
    }

    public function updatedAcceptingSessions($value)
    {

        $profile = Auth::user()->instructorProfile;
        if ($profile && in_array($profile->status, ['verified', 'not_accepting', 'on_leave'])) {
            $profile->update([
                'status' => $value ? 'verified' : 'not_accepting',
            ]);
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
    public function courses()
    {
        $profile = Auth::user()->instructorProfile;
        if (!$profile) {
            return collect();
        }
        return \App\Models\Course::whereHas('enrollments', function($q) use ($profile) {
            $q->where('instructor_id', $profile->id)
              ->whereHas('instructorPerformances');
        })->take(2)->get();
    }

    #[Computed]
    public function todaySessions()
    {
        $profile = Auth::user()->instructorProfile;
        if (!$profile) return collect();

        return BookingSession::where('instructor_id', $profile->id)
            ->whereDate('start_time', today())
            ->with(['enrollment.studentProfile.user', 'enrollment.course'])
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function licenseInfo()
    {
        $profile = Auth::user()->instructorProfile;
        if (!$profile) return null;

        $expiry = $profile->license_expiry;
        $now = now();
        $isExpired = $expiry ? $expiry->isPast() : false;
        $daysRemaining = $expiry ? (int) $now->diffInDays($expiry, false) : null;

        return (object) [
            'number' => $profile->license_number,
            'expiry' => $expiry,
            'is_expired' => $isExpired,
            'days_remaining' => $daysRemaining,
            'is_expiring_soon' => $daysRemaining !== null && $daysRemaining <= 90 && $daysRemaining > 0,
        ];
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
                {{ now()->format('l, F j, Y') }} • <flux:text color="{{ $accepting_sessions ? 'emerald' : 'rose' }}" weight="medium">{{ $accepting_sessions ? 'Accepting Sessions' : 'Not Accepting' }}</flux:text>
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
                            @forelse ($this->todaySessions as $session)
                                @php
                                    $isUpcoming = $session->status === 'scheduled' && $session->start_time->isFuture();
                                    $isCompleted = $session->status === 'completed';
                                    $isActive = $session->status === 'active' || $session->status === 'in_progress';
                                    $studentName = $session->enrollment?->studentProfile?->user?->name ?? 'Unknown';
                                    $initials = collect(explode(' ', $studentName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                                    $courseTitle = $session->enrollment?->course?->title ?? 'N/A';
                                    $courseType = $session->enrollment?->course?->type ?? '';
                                @endphp

                                <tr class="group transition-colors {{ $isUpcoming ? 'bg-blue-50/30 dark:bg-blue-900/10 hover:bg-blue-50/50 dark:hover:bg-blue-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }} cursor-default">
                                    <td class="px-6 py-4">
                                        @if($isUpcoming)
                                            <flux:text color="blue" size="xs" weight="bold" class="font-mono bg-blue-100/50 dark:bg-blue-900/30 px-2 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                                                {{ $session->start_time->format('h:i A') }}
                                            </flux:text>
                                        @else
                                            <flux:text size="xs" weight="bold" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400">
                                                {{ $session->start_time->format('h:i A') }}
                                            </flux:text>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center size-8 rounded-full {{ $isUpcoming ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700' }} text-[10px] font-bold">
                                                {{ $initials }}
                                            </div>
                                            <flux:text weight="{{ $isUpcoming ? 'bold' : 'medium' }}">{{ $studentName }}</flux:text>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <flux:text size="sm" weight="{{ $isUpcoming ? 'semibold' : 'medium' }}">{{ $courseTitle }}</flux:text>
                                            @if($isUpcoming && $session->start_time->diffInMinutes(now()) <= 120)
                                                <flux:text color="blue" size="xs" weight="black" class="mt-0.5 uppercase tracking-tighter animate-pulse text-[10px]">Starts in {{ $session->start_time->diffForHumans() }}</flux:text>
                                            @else
                                                <flux:text size="xs" class="text-zinc-400 mt-0.5 uppercase tracking-tighter text-[10px] font-bold">{{ ucfirst($courseType) }} • {{ ucfirst($session->type ?? 'session') }}</flux:text>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($isCompleted)
                                            <flux:badge color="emerald" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">Completed</flux:badge>
                                        @elseif($isActive)
                                            <flux:badge color="amber" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">
                                                <div class="size-1.5 rounded-full bg-amber-500 animate-pulse mr-1.5"></div>
                                                In Progress
                                            </flux:badge>
                                        @elseif($isUpcoming)
                                            <flux:badge color="blue" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">
                                                <div class="size-1.5 rounded-full bg-blue-500 animate-pulse mr-1.5"></div>
                                                Up Next
                                            </flux:badge>
                                        @else
                                            <flux:badge color="zinc" variant="subtle" size="sm" class="font-bold tracking-widest uppercase">{{ ucfirst($session->status) }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($isUpcoming)
                                            <flux:button size="sm" variant="primary" icon="play" class="shadow-sm shadow-blue-500/20">Start Session</flux:button>
                                        @else
                                            <flux:button variant="ghost" size="xs" icon="document-text" inset="top bottom">View</flux:button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state 
                                    variant="table" 
                                    :colspan="5"
                                    icon="calendar"
                                    heading="No sessions scheduled for today"
                                    message="Enjoy your free time or check upcoming sessions."
                                />
                            @endforelse
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
                    @if($this->courses->count() > 0)
                        <flux:button href="{{ route('instructor.performance-reviews') }}" variant="subtle" size="xs" icon-trailing="arrow-right" wire:navigate>
                            View All Reviews
                        </flux:button>
                    @endif
                </div>

                @if ($this->courses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($this->courses as $course)
                            <livewire:instructor-performance-card 
                                :instructor="Auth::user()->instructorProfile"
                                :courseId="$course->id"
                                :courseTitle="$course->title"
                                :courseCode="$course->code"
                                :courseType="$course->type"
                                :key="'course-perf-' . $course->id"
                            />
                        @endforeach
                    </div>
                @else
                    <x-empty-state 
                        variant="card" 
                        icon="star"
                        heading="No Performance Data Yet"
                        message="Complete more sessions and receive student feedback to see your analytics here."
                    />
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
                        <div class="absolute -bottom-1 -right-1 size-3.5 rounded-full border-2 border-white dark:border-slate-900 {{ $accepting_sessions ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                    </div>
                    <div>
                        <flux:text size="sm" weight="bold">{{ Auth::user()->name }}</flux:text>
                        <flux:text size="xs" class="text-slate-500">{{ $accepting_sessions ? 'Accepting Sessions' : 'Not Accepting' }}</flux:text>
                    </div>
                </div>
                <flux:switch wire:model.live="accepting_sessions" size="sm" />
            </div>

            {{-- LICENSE EXPIRY WIDGET --}}
            @if($this->licenseInfo)
                @php
                    $license = $this->licenseInfo;
                    $isWarning = $license->is_expired || $license->is_expiring_soon;
                    $borderColor = $license->is_expired ? 'border-red-100 dark:border-red-900/30' : ($license->is_expiring_soon ? 'border-amber-100 dark:border-amber-900/30' : 'border-blue-100 dark:border-blue-900/30');
                    $bgColor = $license->is_expired ? 'bg-red-50/50 dark:bg-red-900/10' : ($license->is_expiring_soon ? 'bg-amber-50/50 dark:bg-amber-900/10' : 'bg-blue-50/50 dark:bg-blue-900/10');
                    $iconBg = $license->is_expired ? 'bg-red-500' : ($license->is_expiring_soon ? 'bg-amber-500' : 'bg-blue-500');
                    $headingColor = $license->is_expired ? 'text-red-900 dark:text-red-100' : ($license->is_expiring_soon ? 'text-amber-900 dark:text-amber-100' : 'text-blue-900 dark:text-blue-100');
                    $textColor = $license->is_expired ? 'text-red-800/70 dark:text-red-300' : ($license->is_expiring_soon ? 'text-amber-800/70 dark:text-amber-300' : 'text-blue-800/70 dark:text-blue-300');
                    $glowColor = $license->is_expired ? 'bg-red-500/5 group-hover:bg-red-500/10' : ($license->is_expiring_soon ? 'bg-amber-500/5 group-hover:bg-amber-500/10' : 'bg-blue-500/5 group-hover:bg-blue-500/10');
                @endphp
                <div class="p-6 rounded-[2rem] border {{ $borderColor }} {{ $bgColor }} shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 size-32 {{ $glowColor }} rounded-full blur-2xl transition-colors duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 {{ $iconBg }} text-white rounded-xl shadow-lg shadow-{{ $license->is_expired ? 'red' : ($license->is_expiring_soon ? 'amber' : 'blue') }}-500/20">
                                <flux:icon icon="identification" class="size-5" />
                            </div>
                            <flux:heading size="sm" weight="bold" class="{{ $headingColor }}">License Verification</flux:heading>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <flux:text size="xs" class="{{ $textColor }} uppercase font-bold tracking-widest">License No.</flux:text>
                                <flux:text size="sm" weight="bold" class="{{ $headingColor }}">{{ $license->number }}</flux:text>
                            </div>

                            @if($license->is_expired)
                                <flux:text size="sm" class="{{ $textColor }} font-medium leading-relaxed">
                                    Your license <span class="font-bold {{ $headingColor }}">expired on {{ $license->expiry->format('F j, Y') }}</span>. Please renew immediately to continue teaching.
                                </flux:text>
                            @elseif($license->is_expiring_soon)
                                <flux:text size="sm" class="{{ $textColor }} font-medium leading-relaxed">
                                    Your license expires on <span class="font-bold {{ $headingColor }}">{{ $license->expiry->format('F j, Y') }}</span> — <span class="font-bold">{{ $license->days_remaining }} days remaining</span>. Consider renewing soon.
                                </flux:text>
                            @else
                                <flux:text size="sm" class="{{ $textColor }} font-medium leading-relaxed">
                                    Your professional instructor license is verified and valid until <span class="font-bold {{ $headingColor }}">{{ $license->expiry->format('F j, Y') }}</span>.
                                </flux:text>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- VEHICLES --}}
            <livewire:vehicles />
        </div>
    </div>
</div>

