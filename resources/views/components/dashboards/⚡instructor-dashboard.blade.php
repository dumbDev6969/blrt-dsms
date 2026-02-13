<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
new class extends Component {
    //
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Welcome & Status --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">{{ Auth::user()->name }}</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • <span class="text-emerald-600 font-medium">Active Status</span>
            </flux:text>
        </div>
        <div class="flex gap-3">
            {{-- Quick Action: Log a Session manually if needed --}}
            <flux:button variant="filled" icon="plus">Log Session</flux:button>
        </div>
    </div>

    {{-- SECTION 1: KEY METRICS (Derived from INSTRUCTOR_METRIC table) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Metric: Revenue --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kilometers Guided</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="currency-dollar" class="size-5" />
                </div>
            </div>
            <div>
                <span class="text-2xl font-bold">10k Kilometers</span>
            </div>
        </div>

        {{-- Metric: Teaching Hours --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Hours Taught</span>
                <div class="p-2 bg-orange-50 text-orange-600 rounded-lg dark:bg-orange-900/20 dark:text-orange-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div>
                <span class="text-2xl font-bold">42.5 hrs</span>
            </div>
        </div>

        {{-- Metric: Student Pass Rate --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Student Pass Rate</span>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg dark:bg-purple-900/20 dark:text-purple-400">
                    <flux:icon icon="academic-cap" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">94%</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">16 of 17 students passed</p>
        </div>

        {{-- Metric: Rating --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Avg. Rating</span>
                <div class="p-2 bg-yellow-50 text-yellow-600 rounded-lg dark:bg-yellow-900/20 dark:text-yellow-400">
                    <flux:icon icon="star" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">4.8</span>
                <span class="text-xs text-slate-400">/ 5.0</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Based on 28 reviews</p>
        </div>
    </div>

    {{-- SECTION 2: MAIN WORKSPACE (Split View) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

        {{-- LEFT COLUMN: Operational (Agenda & Tasks) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ALERT: Pending Signatures (Critical for PDC_SESSION_LOG) --}}
            <flux:callout variant="danger" icon="pencil-square" class="w-full">
                <div class="flex justify-between items-center w-full">
                    <div>
                        <flux:callout.heading>You haven't uploaded documents yet</flux:callout.heading>
                        <flux:callout.text> Please upload the remaining documents to proceed with your teaching journey.
                        </flux:callout.text>
                    </div>
                    <flux:button size="sm" variant="ghost">Upload documents</flux:button>
                </div>
            </flux:callout>

            {{-- TODAY'S AGENDA (From BOOKING_SESSION) --}}
            <div
                class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Today's Schedule</h3>
                    <flux:button size="sm" variant="ghost" icon="calendar">View Calendar</flux:button>
                </div>

                {{-- Table --}}
                <div class="overflow-auto">
                    {{-- FIXED: Removed 'whitespace-nowrap' so text wraps and table fits width --}}
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
                                    <span
                                        class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        08:00 AM
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold">
                                            JD
                                        </div>
                                        <span class="font-medium text-zinc-900 dark:text-white">Juan Dela Cruz</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                    PDC - Manual <span class="text-zinc-400">(Toyota Vios)</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">
                                        Completed
                                    </div>
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
                                    <span
                                        class="font-mono text-xs text-blue-600 bg-blue-100/50 dark:bg-blue-900/30 px-2 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                                        01:00 PM
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold">
                                            MC
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">Maria Clara</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-zinc-900 dark:text-zinc-100">PDC - Automatic (Honda
                                            City)</span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-0.5">Starts
                                            in 45 mins</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50">
                                        <div class="size-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                                        Up Next
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="sm" variant="primary" icon="play">Start</flux:button>
                                </td>
                            </tr>

                            {{-- Row 3: Future --}}
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                        03:30 PM
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold">
                                            JR
                                        </div>
                                        <span class="font-medium text-zinc-900 dark:text-white">Jose Rizal</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                    Final Practical Exam
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700">
                                        Scheduled
                                    </div>
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
                    <h3 class="font-bold text-lg">Recent Student Feedback</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-4 items-start">
                        <div
                            class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold">
                            JD</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold">Juan D.</span>
                                <div class="flex text-yellow-400 text-xs">
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                    <flux:icon icon="star" variant="solid" class="size-3" />
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">"Sir is very patient with my
                                parking skills. Explained the reference points clearly."</p>
                            <span class="text-xs text-slate-400 mt-1 block">2 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Management --}}
        <div class="space-y-6">

            {{-- PROFILE & AVAILABILITY --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4">Availability</h3>

                {{-- Toggle Logic linked to 'is_active' or specific schedule --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium">Accepting New Students</span>
                        <span class="text-xs text-slate-500">Visible in booking system</span>
                    </div>
                    <flux:switch wire:model="is_active" />
                </div>

                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-3">Next Maintenance</h4>
                <div
                    class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <flux:icon icon="wrench" class="size-5 text-slate-400" />
                    <div>
                        <p class="text-sm font-medium">Toyota Vios (ABC-123)</p>
                        <p class="text-xs text-slate-500">Due: Feb 15, 2026</p>
                    </div>
                </div>
            </div>

            {{-- QUICK LINKS --}}
            <div
                class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="clipboard-document-list" class="size-5 text-slate-400" />
                        Submit Incident Report
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="user-group" class="size-5 text-slate-400" />
                        View Student List
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="document-text" class="size-5 text-slate-400" />
                        Update License Info
                    </button>
                </div>
            </div>

            {{-- LICENSE EXPIRY WIDGET --}}
            <div class="p-5 rounded-xl border border-blue-100 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/10">
                <div class="flex items-start gap-3">
                    <flux:icon icon="identification" class="size-6 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h4 class="font-bold text-blue-900 dark:text-blue-100 text-sm">License Status</h4>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Valid until Oct 2027</p>
                        <p
                            class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium cursor-pointer hover:underline">
                            View Digital ID</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
