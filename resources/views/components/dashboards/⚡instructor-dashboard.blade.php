<?php

use Livewire\Component;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
new class extends Component
{
    //
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER: Welcome & Status --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">{{ Auth::user()->name }}</flux:heading>
            <flux:text >
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
            <div class="">
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
            <div >
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
            {{-- Only show if there are logs where instructor_signed = false --}}
            <flux:callout variant="danger" icon="pencil-square" class="w-full">
                <div class="flex justify-between items-center w-full">
                    <div>
                        <flux:callout.heading>Signatures Required</flux:callout.heading>
                        <flux:callout.text>You have 3 PDC Session Logs pending your digital signature.</flux:callout.text>
                    </div>
                    <flux:button size="sm" variant="ghost">Review Logs</flux:button>
                </div>
            </flux:callout>

            {{-- TODAY'S AGENDA (From BOOKING_SESSION) --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Today's Schedule</h3>
                    <flux:button size="sm" variant="ghost" icon="calendar">View Calendar</flux:button>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{-- Session 1: Completed --}}
                    <div class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-col items-center justify-center w-14 h-14 bg-slate-100 rounded-lg dark:bg-slate-800 text-slate-500">
                            <span class="text-xs font-bold uppercase">08:00</span>
                            <span class="text-[10px] uppercase">AM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">Juan Dela Cruz</h4>
                            <p class="text-sm text-slate-500">PDC - Manual Transmission (Toyota Vios)</p>
                        </div>
                        <div class="text-right">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Completed
                            </span>
                        </div>
                    </div>

                    {{-- Session 2: Up Next --}}
                    <div class="p-4 flex items-center gap-4 bg-blue-50/50 dark:bg-blue-900/10 border-l-4 border-l-[var(--color-accent)]">
                        <div class="flex flex-col items-center justify-center w-14 h-14 bg-white rounded-lg dark:bg-slate-800 text-[var(--color-accent)] border border-blue-100 dark:border-blue-900">
                            <span class="text-xs font-bold uppercase">01:00</span>
                            <span class="text-[10px] uppercase">PM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">Maria Clara</h4>
                            <p class="text-sm text-slate-500">PDC - Automatic (Honda City)</p>
                            <p class="text-xs text-[var(--color-accent)] mt-1 font-medium">Starts in 45 mins</p>
                        </div>
                        <div class="text-right">
                            <flux:button size="sm" variant="primary">Start Session</flux:button>
                        </div>
                    </div>

                    {{-- Session 3: Future --}}
                    <div class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-col items-center justify-center w-14 h-14 bg-slate-100 rounded-lg dark:bg-slate-800 text-slate-500">
                            <span class="text-xs font-bold uppercase">03:30</span>
                            <span class="text-[10px] uppercase">PM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">Assessment: Jose Rizal</h4>
                            <p class="text-sm text-slate-500">Final Practical Exam</p>
                        </div>
                        <div class="text-right">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                Scheduled
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RECENT FEEDBACK (From INSTRUCTOR_PERFORMANCE) --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-lg">Recent Student Feedback</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-4 items-start">
                        <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold">JD</div>
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
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">"Sir is very patient with my parking skills. Explained the reference points clearly."</p>
                            <span class="text-xs text-slate-400 mt-1 block">2 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Management --}}
        <div class="space-y-6">

            {{-- PROFILE & AVAILABILITY --}}
            <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
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
                <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <flux:icon icon="wrench" class="size-5 text-slate-400" />
                    <div>
                        <p class="text-sm font-medium">Toyota Vios (ABC-123)</p>
                        <p class="text-xs text-slate-500">Due: Feb 15, 2026</p>
                    </div>
                </div>
            </div>

            {{-- QUICK LINKS --}}
            <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <button class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="clipboard-document-list" class="size-5 text-slate-400" />
                        Submit Incident Report
                    </button>
                    <button class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
                        <flux:icon icon="user-group" class="size-5 text-slate-400" />
                        View Student List
                    </button>
                    <button class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300">
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
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium cursor-pointer hover:underline">View Digital ID</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>