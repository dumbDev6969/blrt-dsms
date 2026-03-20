<?php

use Livewire\Component;
use App\Models\Document;
use Livewire\Attributes\Computed;
new class extends Component {
    #[Computed]
    public function pendingDocsCount()
    {
        return Document::where('status', 'pending')->get()->count();
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
            value="₱285,400"
            trend="+12.5%"
            trend-color="emerald"
            icon="banknotes"
            color="emerald"
            subtext="vs last month: ₱253,200"
        />

        {{-- KPI: Active Enrollments --}}
        <x-kpi-cards
            label="Active Enrollments"
            value="127"
            trend="students"
            trend-color="zinc"
            icon="academic-cap"
            color="blue"
        >
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">TDC: 52</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">PDC: 75</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Pending Actions --}}
        <x-kpi-cards
            label="Pending Actions"
            value="17"
            trend="items"
            trend-color="zinc"
            icon="clock"
            color="amber"
        >
            <div class="flex gap-2 mt-2">
                <flux:text size="xs" class="text-slate-500">Forms: 5</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text size="xs" class="text-slate-500">Docs: {{ $this->pendingDocsCount }}</flux:text>
            </div>
        </x-kpi-cards>

        {{-- KPI: Fleet Utilization --}}
        <x-kpi-cards
            label="Fleet Status"
            value="8/12"
            trend="in use"
            trend-color="zinc"
            icon="truck"
            color="purple"
        >
            <div class="flex gap-2 mt-2">
                <flux:text color="emerald" size="xs">Available: 4</flux:text>
                <flux:text size="xs" class="text-slate-300">|</flux:text>
                <flux:text color="red" size="xs">Maintenance: 0</flux:text>
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

            {{-- TODAY'S SESSIONS OVERVIEW (BookingSession table) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <flux:heading size="lg" weight="bold">Today's Sessions</flux:heading>
                        <flux:text size="xs" class="text-slate-500 mt-1">Real-time session monitoring</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:badge color="emerald" variant="subtle" size="sm">18 Completed</flux:badge>
                        <flux:badge color="blue" variant="subtle" size="sm">5 In Progress</flux:badge>
                        <flux:badge variant="subtle" size="sm">12 Scheduled</flux:badge>
                    </div>
                </div>

                <div class="p-5">
                    {{-- Active Session Snapshot --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Session 1: In Progress --}}
                        <div
                            class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/10">
                            <div class="flex items-center justify-between mb-3">
                                <flux:badge color="blue" size="sm">IN PROGRESS</flux:badge>
                                <flux:text size="xs" class="text-slate-500">Started 1:00 PM</flux:text>
                            </div>
                            <flux:heading size="sm" weight="semibold">Juan D. → Instructor: Alex Cruz</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Vehicle: Toyota Vios (ABC-123)</flux:text>
                            <div class="mt-3 flex items-center gap-2">
                                <flux:icon icon="clock" class="size-3 text-blue-600" />
                                <flux:text size="xs" color="blue">45 mins elapsed</flux:text>
                            </div>
                        </div>

                        {{-- Session 2: In Progress --}}
                        <div
                            class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/10">
                            <div class="flex items-center justify-between mb-3">
                                <flux:badge color="blue" size="sm">IN PROGRESS</flux:badge>
                                <flux:text size="xs" class="text-slate-500">Started 2:30 PM</flux:text>
                            </div>
                            <flux:heading size="sm" weight="semibold">Maria C. → Instructor: Beth Tan</flux:heading>
                            <flux:text size="xs" class="text-slate-500 mt-1">Vehicle: Honda City (XYZ-789)</flux:text>
                            <div class="mt-3 flex items-center gap-2">
                                <flux:icon icon="clock" class="size-3 text-blue-600" />
                                <flux:text size="xs" color="blue">15 mins elapsed</flux:text>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <flux:button size="sm" variant="ghost" icon="arrow-right" class="w-full">View Full
                            Schedule</flux:button>
                    </div>
                </div>
            </div>

            {{-- INSTRUCTOR PERFORMANCE SNAPSHOT (InstructorMetric table) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <flux:heading size="lg" weight="bold">Instructor Performance (This Month)</flux:heading>
                    <flux:text size="xs" class="text-slate-500 mt-1">Based on sessions, ratings, and pass rates</flux:text>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        {{-- Top Instructor --}}
                        <div
                            class="flex items-center gap-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900">
                            <div
                                class="size-10 rounded-full bg-emerald-200 dark:bg-emerald-800 flex items-center justify-center text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                AC
                            </div>
                            <div class="flex-1">
                                <flux:heading size="sm" weight="semibold">Alex Cruz</flux:heading>
                                <flux:text size="xs" class="text-slate-500">42.5 hrs taught • 17 students</flux:text>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:heading size="xs" weight="bold">4.9</flux:heading>
                                </div>
                                <flux:text size="xs" color="emerald" weight="medium">96% Pass Rate</flux:text>
                            </div>
                        </div>

                        {{-- Instructor 2 --}}
                        <div
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div
                                class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-500 dark:text-slate-400">
                                BT
                            </div>
                            <div class="flex-1">
                                <flux:heading size="sm" weight="semibold">Beth Tan</flux:heading>
                                <flux:text size="xs" class="text-slate-500">38 hrs taught • 14 students</flux:text>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:heading size="xs" weight="bold">4.8</flux:heading>
                                </div>
                                <flux:text size="xs" color="blue" weight="medium">92% Pass Rate</flux:text>
                            </div>
                        </div>

                        {{-- Instructor 3 --}}
                        <div
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div
                                class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-500 dark:text-slate-400">
                                JR
                            </div>
                            <div class="flex-1">
                                <flux:heading size="sm" weight="semibold">Jose Ramos</flux:heading>
                                <flux:text size="xs" class="text-slate-500">35 hrs taught • 12 students</flux:text>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:heading size="xs" weight="bold">4.7</flux:heading>
                                </div>
                                <flux:text size="xs" weight="medium" class="opacity-70">89% Pass Rate</flux:text>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Total Students</flux:text>
                        <flux:heading size="sm" weight="bold">342</flux:heading>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-400">Active Instructors</flux:text>
                        <flux:heading size="sm" weight="bold">8</flux:heading>
                    </div>
                    <div class="flex justify-between items-center">
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
