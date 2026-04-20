<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\InstructorProfile;

use App\Services\InstructorPerformanceService;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

new class extends Component {
    #[Computed]
    public function pendingDocsCount()
    {
        return Document::where('status', 'pending')->count();
    }

    #[Computed]
    public function revenueData()
    {
        $now = Carbon::now();
        $thisMonth = Enrollment::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount_paid');

        $lastMonth = Enrollment::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('amount_paid');

        $difference = $thisMonth - $lastMonth;
        $trend = $lastMonth > 0 ? ($difference / $lastMonth) * 100 : 0;

        return [
            'value' => $thisMonth,
            'trend' => number_format($trend, 1) . '%',
            'trend_color' => $trend >= 0 ? 'emerald' : 'rose',
            'subtext' => 'vs last month: ₱' . number_format($lastMonth, 2),
        ];
    }

    #[Computed]
    public function enrollmentStats()
    {
        $active = Enrollment::where('status', 'active')->count();
        $tdc = Enrollment::where('status', 'active')
            ->whereHas('course', function ($q) {
                $q->where('type', 'theoretical');
            })
            ->count();
        $pdc = Enrollment::where('status', 'active')
            ->whereHas('course', function ($q) {
                $q->whereIn('type', ['practical', 'comprehensive']);
            })
            ->count();

        return [
            'total' => $active,
            'tdc' => $tdc,
            'pdc' => $pdc,
        ];
    }

    #[Computed]
    public function pendingActions()
    {
        $forms = EnrollmentForm::where('status', 'submitted')->count();
        $docs = $this->pendingDocsCount;

        return [
            'total' => $forms + $docs,
            'forms' => $forms,
            'docs' => $docs,
        ];
    }

    #[Computed]
    public function passedStudentsCount()
    {
        $tdc = Enrollment::where('final_result', 'pass')
            ->whereHas('course', function ($query) {
                $query->where('type', 'theoretical');
            })
            ->count();

        $pdc = Enrollment::where('final_result', 'pass')
            ->whereHas('course', function ($query) {
                $query->whereIn('type', ['practical', 'comprehensive']);
            })
            ->count();

        return [
            'tdc' => $tdc,
            'pdc' => $pdc,
        ];
    }

    #[Computed]
    public function instructorsPerformances()
    {
        $service = app(InstructorPerformanceService::class);
        $allInstructors = InstructorProfile::with('user')->where('status', 'approved')->where('is_active', true)->get();

        $preview = collect();

        // 1. Find the best TDC instructor (theoretical)
        $tdcInstructor = $allInstructors->first(function ($instructor) use ($service) {
            $perf = $service->getPerformancesByCourse($instructor->id);
            if ($perf->contains(fn($p) => $p->course->type === 'theoretical')) {
                $instructor->coursePerformances = $perf->take(2);
                return true;
            }
            return false;
        });

        if ($tdcInstructor) {
            $preview->push($tdcInstructor);
        }

        // 2. Find a different PDC instructor (practical/comprehensive)
        $pdcInstructor = $allInstructors->where('id', '!=', $tdcInstructor?->id)->first(function ($instructor) use ($service) {
            $perf = $service->getPerformancesByCourse($instructor->id);
            if ($perf->contains(fn($p) => in_array($p->course->type, ['practical', 'comprehensive']))) {
                $instructor->coursePerformances = $perf->take(2);
                return true;
            }
            return false;
        });

        // Fallback: If no different PDC instructor, check if the TDC instructor also has PDC courses
        if (!$pdcInstructor && $tdcInstructor) {
            $perf = $service->getPerformancesByCourse($tdcInstructor->id);
            if ($perf->contains(fn($p) => in_array($p->course->type, ['practical', 'comprehensive']))) {
                // If we already added them as TDC, we don't need to add them again, but we ensure their PDC performances are visible if possible
                // For simplicity, if we only found one instructor, the list will just have one.
            }
        } elseif ($pdcInstructor) {
            $preview->push($pdcInstructor);
        }

        return $preview;
    }


};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Admin Overview --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Admin Dashboard</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • System Overview
            </flux:text>
        </div>
        <div class="flex gap-3">
            <flux:button variant="ghost" icon="arrow-down-tray">Export Report</flux:button>
            <flux:button variant="filled" icon="cog-6-tooth">System Settings</flux:button>
        </div>
    </div>

    {{-- CRITICAL ALERTS SECTION --}}
    <div class="space-y-3">
        {{-- Pending Enrollment Forms Alert --}}
        <flux:callout icon="exclamation-triangle" variant="warning" class="w-full">
            <div class="flex justify-between items-center w-full">
                <div>
                    <flux:callout.heading>5 Enrollment Forms Pending Review</flux:callout.heading>
                    <flux:callout.text>Students are waiting for approval to start their courses.</flux:callout.text>
                </div>
                <flux:button size="sm" variant="primary">Review Now</flux:button>
            </div>
        </flux:callout>

        {{-- Document Verification Alert --}}
        @if ($this->pendingDocsCount > 0)
            <flux:callout icon="document-text" variant="info" class="w-full">
                <div class="flex justify-between items-center w-full">
                    <div>
                        <flux:callout.heading>{{ $this->pendingDocsCount }} Documents Awaiting Verification
                        </flux:callout.heading>
                        <flux:callout.text>Medical certificates, IDs, and other compliance documents need review.
                        </flux:callout.text>
                    </div>
                    <flux:button size="sm" variant="ghost" href="{{ route('admin.pending-documents') }}">View Queue
                    </flux:button>
                </div>
            </flux:callout>
        @endif
    </div>

    {{-- SECTION 1: KEY PERFORMANCE INDICATORS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- KPI: Total Revenue (This Month) --}}
        <x-kpi-cards label="Monthly Revenue" value="₱{{ number_format($this->revenueData['value'], 2) }}"
            trend="{{ $this->revenueData['trend'] }}" trend-color="{{ $this->revenueData['trend_color'] }}"
            icon="banknotes" color="emerald" subtext="{{ $this->revenueData['subtext'] }}" />

        {{-- KPI: Active Enrollments --}}
        <x-kpi-cards label="Active Enrollments" value="{{ $this->enrollmentStats['total'] }}" trend="students"
            trend-color="zinc" icon="academic-cap" color="blue">
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">TDC: {{ $this->enrollmentStats['tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">PDC: {{ $this->enrollmentStats['pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Pending Actions --}}
        <x-kpi-cards label="Pending Actions" value="{{ $this->pendingActions['total'] }}" trend="items"
            trend-color="zinc" icon="clock" color="amber">
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">Forms: {{ $this->pendingActions['forms'] }}
                </flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">Docs: {{ $this->pendingActions['docs'] }}</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Passed Students --}}
        <x-kpi-cards label="Passed Students"
            value="{{ $this->passedStudentsCount['tdc'] + $this->passedStudentsCount['pdc'] }}" trend="Total"
            trend-color="emerald" icon="check-badge" color="emerald">
            <div class="flex gap-2 mt-2">
                <flux:text color="emerald" size="xs">TDC: {{ $this->passedStudentsCount['tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text color="emerald" size="xs">PDC: {{ $this->passedStudentsCount['pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>
    </div>

    {{-- SECTION 2: OPERATIONAL DASHBOARD (Split View) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2/3 width): Trends & Charts --}}
        <div class="lg:col-span-2 space-y-6">
            
            <livewire:system-metrics />

            {{-- INSTRUCTOR PERFORMANCE SNAPSHOT --}}
            <div class="relative overflow-hidden rounded-2xl p-6 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{-- Decorative Glow --}}
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative flex flex-col gap-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <flux:heading size="lg" weight="bold">Instructor Performance</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Top instructor performance overview</flux:text>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="arrow-right" :href="route('admin.instructor-performances')" wire:navigate>All</flux:button>
                    </div>

                    @forelse ($this->instructorsPerformances as $instructor)
                        @php
                            $perfs = $instructor->coursePerformances;
                            $totalReviews = $perfs->sum('totalReviews');
                            $coursesTaught = $perfs->count();
                            $avgRating = $totalReviews > 0 ? $perfs->sum(fn($p) => $p->avgRating * $p->totalReviews) / $totalReviews : 0;
                            
                            $criteriaAvg = ['teaching_quality' => 0, 'communication' => 0, 'punctuality' => 0, 'professionalism' => 0];
                            foreach($perfs as $p) {
                                foreach(['teaching_quality', 'communication', 'punctuality', 'professionalism'] as $k) {
                                    $criteriaAvg[$k] += ($p->avgCriteria[$k] ?? 0) * $p->totalReviews;
                                }
                            }
                            if($totalReviews > 0) {
                                foreach($criteriaAvg as $k => $v) { $criteriaAvg[$k] = round($v / $totalReviews, 1); }
                            }
                            
                            $trend = $totalReviews > 0 ? $perfs->sum(fn($p) => $p->trend * $p->totalReviews) / $totalReviews : 0;
                            $topStrength = $perfs->pluck('topStrengths')->flatten()->first();
                            $lastEval = $perfs->max('lastEvaluationDate');
                        @endphp
                        
                        <div class="relative rounded-xl border border-slate-200 bg-white/80 dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-sm shadow-sm overflow-hidden p-5 hover:border-emerald-500/30 transition-colors group">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <flux:avatar src="{{ $instructor->user->avatar_url ?? '' }}" :initials="$instructor->user->initials()" size="sm" />
                                    <div>
                                        <flux:heading size="sm" weight="bold">{{ $instructor->user->name }}</flux:heading>
                                        <flux:text size="xs" variant="subtle">ID: {{ $instructor->id }}</flux:text>
                                    </div>
                                </div>
                                <flux:button variant="ghost" size="xs" icon="arrow-top-right-on-square" :href="route('admin.instructor.evaluations', $instructor->id)" wire:navigate class="opacity-0 group-hover:opacity-100 transition-opacity">View</flux:button>
                            </div>

                            @if($totalReviews > 0)
                                <div class="space-y-4">
                                    {{-- KPI Strip --}}
                                    <div class="flex items-center justify-between bg-white dark:bg-slate-800/50 rounded-lg p-3 border border-slate-100 dark:border-slate-800">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Avg Rating</span>
                                            <div class="flex items-end gap-1">
                                                <span class="text-xl font-black text-amber-500 leading-none">{{ number_format($avgRating, 1) }}</span>
                                                <flux:icon icon="star" variant="solid" class="size-4 text-amber-500 mb-0.5" />
                                                @if($trend != 0)
                                                    <span class="ml-1 text-xs font-bold leading-none mb-1 {{ $trend > 0 ? 'text-emerald-500' : 'text-rose-500' }} flex items-center">
                                                        <flux:icon icon="{{ $trend > 0 ? 'arrow-small-up' : 'arrow-small-down' }}" class="size-3" stroke-width="3" />
                                                        {{ number_format(abs($trend), 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                                        <div class="flex flex-col text-center">
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Reviews</span>
                                            <span class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ $totalReviews }}</span>
                                        </div>
                                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                                        <div class="flex flex-col text-center">
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Courses</span>
                                            <span class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ $coursesTaught }}</span>
                                        </div>
                                    </div>

                                    {{-- Course Pills & Criteria Wrapper --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        {{-- Compact Criteria Bars --}}
                                        <div class="flex flex-col gap-2">
                                            @foreach(['teaching_quality' => 'Teaching', 'communication' => 'Comms', 'punctuality' => 'Punctual', 'professionalism' => 'Prof.'] as $key => $label)
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex justify-between items-end leading-none">
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $label }}</span>
                                                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">{{ number_format($criteriaAvg[$key], 1) }}</span>
                                                    </div>
                                                    <div class="h-1 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ ($criteriaAvg[$key] / 5) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Course Badges --}}
                                        <div class="flex flex-col items-start gap-2">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase">Courses</span>
                                            <div class="flex sm:flex-col flex-wrap gap-2">
                                                @foreach($perfs as $p)
                                                    <div class="px-2 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md flex items-center gap-2 shadow-sm w-full max-w-[140px]">
                                                        <flux:badge size="sm" color="zinc" class="text-[9px] px-1 py-0!">{{ strtoupper($p->course->type) }}</flux:badge>
                                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-auto">{{ number_format($p->avgRating, 1) }}★</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Footer Info --}}
                                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5">
                                            <flux:icon icon="clock" class="size-3 text-slate-400" />
                                            <span class="text-[10px] uppercase font-bold text-slate-400">Eval: {{ $lastEval ? $lastEval->format('M j, Y') : 'N/A' }}</span>
                                        </div>
                                        @if($topStrength)
                                            <div class="flex items-center gap-1">
                                                <flux:icon icon="hand-thumb-up" class="size-3 text-emerald-500" />
                                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">{{ ucwords($topStrength) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <flux:text size="sm" class="text-center py-4 text-slate-500">No evaluations available yet.</flux:text>
                            @endif
                        </div>
                    @empty
                        <flux:text size="sm" class="text-center py-4">No active instructors found.</flux:text>
                    @endforelse
                </div>
            </div>
            </div>

        {{-- RIGHT COLUMN (1/3 width): Performance & Queue --}}
        <div class="space-y-6">

            {{-- DOCUMENT VERIFICATION QUEUE --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" weight="bold">Document Queue</flux:heading>
                    <flux:badge color="amber" variant="subtle" size="sm">{{ $this->pendingDocsCount }} Pending
                    </flux:badge>
                </div>

                <div class="space-y-3">
                    {{-- Doc 1 --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                            <flux:icon icon="document-text" class="size-4 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="xs" weight="semibold">Medical Cert.</flux:heading>
                            <flux:text size="xs" class="text-slate-500">Juan Dela Cruz</flux:text>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>

                    {{-- Doc 2 --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                            <flux:icon icon="identification" class="size-4 text-purple-600" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="xs" weight="semibold">Valid ID</flux:heading>
                            <flux:text size="xs" class="text-slate-500">Maria Santos</flux:text>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>
                </div>

                <div class="mt-4">
                    <flux:button size="sm" variant="ghost" icon="arrow-right" class="w-full" href="">
                        All Docs</flux:button>
                </div>
            </div>

            {{-- VEHICLE MAINTENANCE ALERTS --}}
            <div
                class="p-5 rounded-xl border border-amber-100 bg-amber-50 dark:border-amber-900 dark:bg-amber-900/10 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon icon="wrench-screwdriver" class="size-5 text-amber-600 dark:text-amber-400" />
                    <div>
                        <flux:heading size="sm" weight="bold" class="text-amber-900 dark:text-amber-100">
                            Maintenance Due</flux:heading>
                        <div class="mt-2 space-y-2 text-xs">
                            <flux:text class="text-amber-800 dark:text-amber-200 font-medium">Toyota Vios (ABC-123)
                            </flux:text>
                            <flux:text class="text-amber-800 dark:text-amber-200 font-medium">Honda City (XYZ-789)
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
