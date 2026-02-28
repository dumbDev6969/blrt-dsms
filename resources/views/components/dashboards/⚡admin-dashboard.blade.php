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
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Monthly Revenue</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="banknotes" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">₱285,400</span>
                <span class="text-xs text-emerald-600 font-medium">+12.5%</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">vs last month: ₱253,200</p>
        </div>

        {{-- KPI: Active Enrollments --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Active Enrollments</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">127</span>
                <span class="text-xs text-slate-400">students</span>
            </div>
            <div class="flex gap-2 mt-2 text-xs">
                <span class="text-slate-500">TDC: 52</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500">PDC: 75</span>
            </div>
        </div>

        {{-- KPI: Pending Actions --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Actions</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">17</span>
                <span class="text-xs text-slate-400">items</span>
            </div>
            <div class="flex gap-2 mt-2 text-xs">
                <span class="text-slate-500">Forms: 5</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500">Docs: {{ $this->pendingDocsCount }}</span>
            </div>
        </div>

        {{-- KPI: Fleet Utilization --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Fleet Status</span>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg dark:bg-purple-900/20 dark:text-purple-400">
                    <flux:icon icon="truck" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">8/12</span>
                <span class="text-xs text-slate-400">in use</span>
            </div>
            <div class="flex gap-2 mt-2 text-xs">
                <span class="text-emerald-600">Available: 4</span>
                <span class="text-slate-300">|</span>
                <span class="text-red-600">Maintenance: 0</span>
            </div>
        </div>
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
                        <h3 class="font-bold text-lg">Enrollment Forms - Pending Review</h3>
                        <p class="text-xs text-slate-500 mt-1">Students awaiting course approval</p>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="arrow-right" href="">View All</flux:button>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{-- Form 1 --}}
                    <div
                        class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div
                            class="flex flex-col items-center justify-center w-16 h-16 bg-blue-50 rounded-lg dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900">
                            <span class="text-xs font-bold text-blue-600">TDC</span>
                            <span class="text-[10px] text-slate-500 uppercase">PDC</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Maria Santos</h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Package: TDC
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">Control No: <span class="font-mono">TDC-2026-0234</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Submitted: 2 hours ago • Student Permit Holder</p>
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
                            <span class="text-xs font-bold text-purple-600">PDC</span>
                            <span class="text-[10px] text-slate-500 uppercase">4W/MT</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Juan Dela Cruz</h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    4-Wheel Manual
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">Control No: <span class="font-mono">PDC-2026-0451</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Submitted: 5 hours ago • Has TDC Certificate</p>
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
                            <span class="text-xs font-bold text-amber-600">REF</span>
                            <span class="text-[10px] text-slate-500 uppercase">2W</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Ana Reyes</h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                    Refresher Course
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">Control No: <span class="font-mono">REF-2026-0089</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Submitted: 1 day ago • Motorcycle (2-Wheel)</p>
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
                        <h3 class="font-bold text-lg">Today's Sessions</h3>
                        <p class="text-xs text-slate-500 mt-1">Real-time session monitoring</p>
                    </div>
                    <div class="flex gap-2">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            18 Completed
                        </span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            5 In Progress
                        </span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            12 Scheduled
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    {{-- Active Session Snapshot --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Session 1: In Progress --}}
                        <div
                            class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/10">
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-600 text-white">
                                    IN PROGRESS
                                </span>
                                <span class="text-xs text-slate-500">Started 1:00 PM</span>
                            </div>
                            <h4 class="font-semibold text-sm">Juan D. → Instructor: Alex Cruz</h4>
                            <p class="text-xs text-slate-500 mt-1">Vehicle: Toyota Vios (ABC-123)</p>
                            <div class="mt-3 flex items-center gap-2 text-xs text-blue-600">
                                <flux:icon icon="clock" class="size-3" />
                                <span>45 mins elapsed</span>
                            </div>
                        </div>

                        {{-- Session 2: In Progress --}}
                        <div
                            class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/10">
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-600 text-white">
                                    IN PROGRESS
                                </span>
                                <span class="text-xs text-slate-500">Started 2:30 PM</span>
                            </div>
                            <h4 class="font-semibold text-sm">Maria C. → Instructor: Beth Tan</h4>
                            <p class="text-xs text-slate-500 mt-1">Vehicle: Honda City (XYZ-789)</p>
                            <div class="mt-3 flex items-center gap-2 text-xs text-blue-600">
                                <flux:icon icon="clock" class="size-3" />
                                <span>15 mins elapsed</span>
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
                    <h3 class="font-bold text-lg">Instructor Performance (This Month)</h3>
                    <p class="text-xs text-slate-500 mt-1">Based on sessions, ratings, and pass rates</p>
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
                                <h4 class="font-semibold text-sm">Alex Cruz</h4>
                                <p class="text-xs text-slate-500">42.5 hrs taught • 17 students</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <span class="font-bold">4.9</span>
                                </div>
                                <span class="text-xs text-emerald-600 font-medium">96% Pass Rate</span>
                            </div>
                        </div>

                        {{-- Instructor 2 --}}
                        <div
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div
                                class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-bold">
                                BT
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-sm">Beth Tan</h4>
                                <p class="text-xs text-slate-500">38 hrs taught • 14 students</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <span class="font-bold">4.8</span>
                                </div>
                                <span class="text-xs text-slate-600 font-medium">92% Pass Rate</span>
                            </div>
                        </div>

                        {{-- Instructor 3 --}}
                        <div
                            class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div
                                class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-bold">
                                JR
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-sm">Jose Ramos</h4>
                                <p class="text-xs text-slate-500">35 hrs taught • 12 students</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1 text-yellow-500 text-xs mb-1">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <span class="font-bold">4.7</span>
                                </div>
                                <span class="text-xs text-slate-600 font-medium">89% Pass Rate</span>
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
                    <h3 class="font-bold text-slate-900 dark:text-slate-100">Document Queue</h3>
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                        {{ $this->pendingDocsCount }} Pending
                    </span>
                </div>

                <div class="space-y-3">
                    {{-- Doc 1: Medical Certificate --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded">
                            <flux:icon icon="document-text" class="size-4 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold">Medical Certificate</h4>
                            <p class="text-xs text-slate-500">Juan Dela Cruz</p>
                            <p class="text-[10px] text-slate-400 mt-1">2 hours ago</p>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>

                    {{-- Doc 2: Valid ID --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded">
                            <flux:icon icon="identification" class="size-4 text-purple-600" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold">Valid ID</h4>
                            <p class="text-xs text-slate-500">Maria Santos</p>
                            <p class="text-[10px] text-slate-400 mt-1">4 hours ago</p>
                        </div>
                        <flux:button size="xs" variant="ghost">View</flux:button>
                    </div>

                    {{-- Doc 3: TDC Certificate --}}
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-white dark:bg-slate-900 rounded">
                            <flux:icon icon="academic-cap" class="size-4 text-emerald-600" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold">TDC Certificate</h4>
                            <p class="text-xs text-slate-500">Ana Reyes</p>
                            <p class="text-[10px] text-slate-400 mt-1">1 day ago</p>
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
            <div class="p-5 rounded-xl border border-amber-100 bg-amber-50 dark:border-amber-900 dark:bg-amber-900/10">
                <div class="flex items-start gap-3">
                    <flux:icon icon="wrench-screwdriver" class="size-6 text-amber-600 dark:text-amber-400" />
                    <div>
                        <h4 class="font-bold text-amber-900 dark:text-amber-100 text-sm">Maintenance Due</h4>
                        <div class="mt-3 space-y-2">
                            <div class="text-xs">
                                <p class="font-semibold text-amber-800 dark:text-amber-200">Toyota Vios (ABC-123)</p>
                                <p class="text-amber-700 dark:text-amber-300">Due: Feb 15, 2026 (12 days)</p>
                            </div>
                            <div class="text-xs">
                                <p class="font-semibold text-amber-800 dark:text-amber-200">Honda City (XYZ-789)</p>
                                <p class="text-amber-700 dark:text-amber-300">Due: Feb 28, 2026 (25 days)</p>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" class="mt-3 w-full">Schedule Maintenance
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- SYSTEM STATISTICS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4">System Stats</h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Total Students</span>
                        <span class="text-sm font-bold">342</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Active Instructors</span>
                        <span class="text-sm font-bold">8</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Completion Rate</span>
                        <span class="text-sm font-bold text-emerald-600">87%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Avg. Rating</span>
                        <div class="flex items-center gap-1">
                            <flux:icon icon="star" variant="solid" class="size-3 text-yellow-500" />
                            <span class="text-sm font-bold">4.6</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- QUICK ACTIONS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="user-plus" class="size-5 text-slate-400" />
                        Add New Instructor
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="truck" class="size-5 text-slate-400" />
                        Register Vehicle
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="document-duplicate" class="size-5 text-slate-400" />
                        Generate Reports
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="chart-bar" class="size-5 text-slate-400" />
                        View Analytics
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
