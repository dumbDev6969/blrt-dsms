<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\InstructorProfile;
use App\Models\SystemMetric;
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
        $thisMonth = Enrollment::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount_paid');
            
        $lastMonth = Enrollment::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('amount_paid');

        $difference = $thisMonth - $lastMonth;
        $trend = $lastMonth > 0 ? ($difference / $lastMonth) * 100 : 0;

        return [
            'value' => $thisMonth,
            'trend' => number_format($trend, 1) . '%',
            'trend_color' => $trend >= 0 ? 'emerald' : 'rose',
            'subtext' => 'vs last month: ₱' . number_format($lastMonth, 2)
        ];
    }

    #[Computed]
    public function enrollmentStats()
    {
        $active = Enrollment::where('status', 'active')->count();
        $tdc = Enrollment::where('status', 'active')->whereHas('course', function ($q) {
            $q->where('type', 'theoretical');
        })->count();
        $pdc = Enrollment::where('status', 'active')->whereHas('course', function ($q) {
            $q->whereIn('type', ['practical', 'comprehensive']);
        })->count();

        return [
            'total' => $active,
            'tdc' => $tdc,
            'pdc' => $pdc
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
            'docs' => $docs
        ];
    }

    #[Computed]
    public function passedStudentsCount()
    {
        $tdc = Enrollment::where('final_result', 'pass')->whereHas('course', function ($query) {
            $query->where('type', 'theoretical');
        })->count();

        $pdc = Enrollment::where('final_result', 'pass')->whereHas('course', function ($query) {
            $query->whereIn('type', ['practical', 'comprehensive']);
        })->count();

        return [
            'tdc' => $tdc,
            'pdc' => $pdc,
        ];
    }

    #[Computed]
    public function instructorsPerformances()
    {
        $service = app(InstructorPerformanceService::class);
        $allInstructors = InstructorProfile::with('user')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get();

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

    #[Computed]
    public function latestMetrics()
    {
        return SystemMetric::latest('metric_date')->first() ?? new SystemMetric([
            'new_students' => 0,
            'active_enrollments' => 0,
            'completed_courses' => 0,
            'total_bookings' => 0,
            'revenue' => 0,
        ]);
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
        <x-kpi-cards
            label="Monthly Revenue"
            value="₱{{ number_format($this->revenueData['value'], 2) }}"
            trend="{{ $this->revenueData['trend'] }}"
            trend-color="{{ $this->revenueData['trend_color'] }}"
            icon="banknotes"
            color="emerald"
            subtext="{{ $this->revenueData['subtext'] }}"
        />

        {{-- KPI: Active Enrollments --}}
        <x-kpi-cards
            label="Active Enrollments"
            value="{{ $this->enrollmentStats['total'] }}"
            trend="students"
            trend-color="zinc"
            icon="academic-cap"
            color="blue"
        >
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">TDC: {{ $this->enrollmentStats['tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">PDC: {{ $this->enrollmentStats['pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Pending Actions --}}
        <x-kpi-cards
            label="Pending Actions"
            value="{{ $this->pendingActions['total'] }}"
            trend="items"
            trend-color="zinc"
            icon="clock"
            color="amber"
        >
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">Forms: {{ $this->pendingActions['forms'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">Docs: {{ $this->pendingActions['docs'] }}</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Passed Students --}}
        <x-kpi-cards
            label="Passed Students"
            value="{{ $this->passedStudentsCount['tdc'] + $this->passedStudentsCount['pdc'] }}"
            trend="Total"
            trend-color="emerald"
            icon="check-badge"
            color="emerald"
        >
            <div class="flex gap-2 mt-2">
                <flux:text color="emerald" size="xs">TDC: {{ $this->passedStudentsCount['tdc'] }}</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text color="emerald" size="xs">PDC: {{ $this->passedStudentsCount['pdc'] }}</flux:text>
            </div>
        </x-kpi-cards>
    </div>

    {{-- SECTION 2: OPERATIONAL DASHBOARD (Split View) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2/3 width): Main Operations --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ENROLLMENT FORMS QUEUE (EnrollmentForm table - status: submitted) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <flux:heading size="lg" weight="bold">Enrollment Forms - Pending Review</flux:heading>
                        <flux:text size="xs" class="text-slate-500 mt-1">Students awaiting course approval</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="arrow-right" href="">View All</flux:button>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{-- Form 1 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-16 h-16 bg-blue-50 rounded-lg dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900">
                            <flux:text size="xs" weight="bold" color="blue">TDC</flux:text>
                            <flux:text size="xs" class="text-slate-500 uppercase opacity-50" style="font-size: 10px;">PDC</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Maria Santos</flux:heading>
                                <flux:badge color="blue" size="sm">Package: TDC</flux:badge>
                            </div>
                            <flux:text size="sm" class="text-slate-500">Control No: <flux:text class="font-mono">TDC-2026-0234</flux:text>
                            </flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1">Submitted: 2 hours ago • Student Permit Holder</flux:text>
                        </div>
                        <div class="text-right space-x-2">
                            <flux:button size="sm" variant="primary">Approve</flux:button>
                            <flux:button size="sm" variant="ghost">Review</flux:button>
                        </div>
                    </div>

                    {{-- Form 2 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-16 h-16 bg-purple-50 rounded-lg dark:bg-purple-900/20 border border-purple-100 dark:border-purple-900">
                            <flux:text size="xs" weight="bold" color="purple">PDC</flux:text>
                            <flux:text size="xs" class="text-slate-500 uppercase opacity-50" style="font-size: 10px;">4W/MT</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Juan Dela Cruz</flux:heading>
                                <flux:badge color="purple" size="sm">4-Wheel Manual</flux:badge>
                            </div>
                            <flux:text size="sm" class="text-slate-500">Control No: <flux:text class="font-mono">PDC-2026-0451</flux:text>
                            </flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1">Submitted: 5 hours ago • Has TDC Certificate</flux:text>
                        </div>
                        <div class="text-right space-x-2">
                            <flux:button size="sm" variant="primary">Approve</flux:button>
                            <flux:button size="sm" variant="ghost">Review</flux:button>
                        </div>
                    </div>

                    {{-- Form 3 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-16 h-16 bg-amber-50 rounded-lg dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900">
                            <flux:text size="xs" weight="bold" color="amber">REF</flux:text>
                            <flux:text size="xs" class="text-slate-500 uppercase opacity-50" style="font-size: 10px;">2W</flux:text>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" weight="semibold">Ana Reyes</flux:heading>
                                <flux:badge color="amber" size="sm">Refresher Course</flux:badge>
                            </div>
                            <flux:text size="sm" class="text-slate-500">Control No: <flux:text class="font-mono">REF-2026-0089</flux:text>
                            </flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1">Submitted: 1 day ago • Motorcycle (2-Wheel)</flux:text>
                        </div>
                        <div class="text-right space-x-2">
                            <flux:button size="sm" variant="primary">Approve</flux:button>
                            <flux:button size="sm" variant="ghost">Review</flux:button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INSTRUCTOR PERFORMANCE SNAPSHOT --}}
            <div class="flex flex-col gap-6">
                <div class="flex justify-between items-center">
                    <div>
                        <flux:heading size="lg" weight="bold">Instructor Performance (Preview)</flux:heading>
                        <flux:text size="xs" class="text-slate-500 mt-1">Snapshot of TDC & PDC performance leads</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="arrow-right" :href="route('admin.instructor-performances')" wire:navigate>View All Analytics</flux:button>
                </div>

                @forelse ($this->instructorsPerformances as $instructor)
                    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden p-6">
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <div class="flex items-center gap-3">
                                <flux:avatar src="{{ $instructor->user->avatar_url ?? '' }}" :initials="$instructor->user->initials()" size="lg" />
                                <div>
                                    <flux:heading size="md" weight="bold">{{ $instructor->user->name }}</flux:heading>
                                    <flux:text size="xs" variant="subtle">Instructor ID: {{ $instructor->id }}</flux:text>
                                </div>
                            </div>
                            <flux:button variant="ghost" size="xs" icon="eye" :href="route('admin.instructor.evaluations', $instructor->id)" wire:navigate>View Evaluations</flux:button>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            @forelse ($instructor->coursePerformances as $perf)
                                <x-instructor-performance 
                                    :courseTitle="$perf->course->title"
                                    :courseCode="$perf->course->code"
                                    :courseType="strtoupper($perf->course->type)"
                                    :avgRating="$perf->avgRating"
                                    :totalReviews="$perf->totalReviews"
                                    :avgCriteria="$perf->avgCriteria"
                                    :performances="$perf->performances"
                                    :lastEvaluationDate="$perf->lastEvaluationDate"
                                    :trend="$perf->trend"
                                    :ratingDistribution="$perf->ratingDistribution"
                                    :topStrengths="$perf->topStrengths"
                                    :topImprovements="$perf->topImprovements"
                                />
                            @empty
                                <div class="col-span-full py-8 text-center text-slate-500">
                                    <flux:icon icon="star" class="size-8 mx-auto mb-2 opacity-50" />
                                    <flux:text>No performance data available for this instructor yet.</flux:text>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
                        <flux:text>No active instructors found.</flux:text>
                    </div>
                @endforelse
            </div>

        </div>

        {{-- RIGHT COLUMN (1/3 width): Quick Actions & Alerts --}}
        <div class="space-y-6">

            {{-- DOCUMENT VERIFICATION QUEUE --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" weight="bold">Document Queue</flux:heading>
                    <flux:badge color="amber" variant="subtle" size="sm">{{ $this->pendingDocsCount }} Pending</flux:badge>
                </div>

                <div class="space-y-3">
                    {{-- Doc 1: Medical Certificate --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                            <flux:icon icon="document-text" class="size-4 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="xs" weight="semibold">Medical Certificate</flux:heading>
                            <flux:text size="xs" class="text-slate-500">Juan Dela Cruz</flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1 opacity-70" style="font-size: 10px;">2 hours ago</flux:text>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>

                    {{-- Doc 2: Valid ID --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                            <flux:icon icon="identification" class="size-4 text-purple-600" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="xs" weight="semibold">Valid ID</flux:heading>
                            <flux:text size="xs" class="text-slate-500">Maria Santos</flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1 opacity-70" style="font-size: 10px;">4 hours ago</flux:text>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>

                    {{-- Doc 3: TDC Certificate --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded shadow-sm">
                            <flux:icon icon="academic-cap" class="size-4 text-emerald-600" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="xs" weight="semibold">TDC Certificate</flux:heading>
                            <flux:text size="xs" class="text-slate-500">Ana Reyes</flux:text>
                            <flux:text size="xs" class="text-slate-400 mt-1 opacity-70" style="font-size: 10px;">1 day ago</flux:text>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>
                </div>

                <div class="mt-4">
                    <flux:button size="sm" variant="ghost" icon="arrow-right" class="w-full" href="">
                        View All Documents
                    </flux:button>
                </div>
            </div>

            {{-- VEHICLE MAINTENANCE ALERTS --}}
            <div class="p-5 rounded-xl border border-amber-100 bg-amber-50 dark:border-amber-900 dark:bg-amber-900/10 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon icon="wrench-screwdriver" class="size-6 text-amber-600 dark:text-amber-400" />
                    <div>
                        <flux:heading size="sm" weight="bold" class="text-amber-900 dark:text-amber-100">Maintenance Due</flux:heading>
                        <div class="mt-3 space-y-2">
                            <div>
                                <flux:heading size="xs" weight="semibold" class="text-amber-800 dark:text-amber-200">Toyota Vios (ABC-123)</flux:heading>
                                <flux:text size="xs" class="text-amber-700 dark:text-amber-300">Due: Feb 15, 2026 (12 days)</flux:text>
                            </div>
                            <div>
                                <flux:heading size="xs" weight="semibold" class="text-amber-800 dark:text-amber-200">Honda City (XYZ-789)</flux:heading>
                                <flux:text size="xs" class="text-amber-700 dark:text-amber-300">Due: Feb 28, 2026 (25 days)</flux:text>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" class="mt-3 w-full" color="amber">Schedule Maintenance
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- SYSTEM STATISTICS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <flux:heading size="lg" weight="bold" class="mb-4">System Stats</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">New Students (Today)</flux:text>
                        <flux:heading size="sm" weight="bold">{{ $this->latestMetrics->new_students }}</flux:heading>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Active Enrollments</flux:text>
                        <flux:heading size="sm" weight="bold">{{ $this->latestMetrics->active_enrollments }}</flux:heading>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Completed Courses</flux:text>
                        <flux:heading size="sm" weight="bold">{{ $this->latestMetrics->completed_courses }}</flux:heading>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Total Bookings</flux:text>
                        <flux:heading size="sm" weight="bold">{{ $this->latestMetrics->total_bookings }}</flux:heading>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100 dark:border-slate-800">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Completion Rate</flux:text>
                        <flux:text size="sm" weight="bold" color="emerald">87%</flux:text>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Avg. Rating</flux:text>
                        <div class="flex items-center gap-1">
                            <flux:icon icon="star" variant="solid" class="size-3 text-yellow-500" />
                            <flux:heading size="sm" weight="bold">4.6</flux:heading>
                        </div>
                    </div>
                </div>
            </div>
            {{-- QUICK ACTIONS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <flux:heading size="lg" weight="bold" class="mb-4">Quick Actions</flux:heading>
                <div class="space-y-2">
                    <flux:button variant="ghost" class="w-full !justify-start" icon="user-plus">
                        Add New Instructor
                    </flux:button>
                    <flux:button variant="ghost" class="w-full !justify-start" icon="truck">
                        Register Vehicle
                    </flux:button>
                    <flux:button variant="ghost" class="w-full !justify-start" icon="document-duplicate">
                        Generate Reports
                    </flux:button>
                    <flux:button variant="ghost" class="w-full !justify-start" icon="chart-bar">
                        View Analytics
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
