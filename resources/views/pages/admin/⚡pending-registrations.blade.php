<?php

use Livewire\Component;

new class extends Component {
    //
};
?>


{{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Pending Registrations</flux:heading>
            <flux:text>
                {{ now()->format('l, F j, Y') }} • Review and verify new user applications
            </flux:text>
        </div>
        <div class="flex gap-3">
            <flux:input 
                placeholder="Search by name or email..." 
                icon="magnifying-glass"
                class="w-64"
            />
            <flux:button variant="ghost" icon="funnel">Filter</flux:button>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Pending --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">12</span>
                <span class="text-xs text-slate-400">applicants</span>
            </div>
        </div>

        {{-- Verified --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Verified</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">8</span>
                <span class="text-xs text-emerald-600 font-medium">+3 today</span>
            </div>
        </div>

        {{-- Issues --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Issues</span>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">2</span>
                <span class="text-xs text-slate-400">incomplete docs</span>
            </div>
        </div>

        {{-- Efficiency --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Avg. Time</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="chart-bar" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">4.2</span>
                <span class="text-xs text-slate-400">hours</span>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

        {{-- LEFT: APPLICANTS LIST (2/3 width) --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Filter Tabs --}}
            <div class="flex gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg w-fit">
                <button class="px-4 py-2 text-sm font-medium rounded-md transition-colors bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm">
                    Pending (12)
                </button>
                <button class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    Verified
                </button>
                <button class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    Rejected
                </button>
            </div>

            {{-- Applicants List Container --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-lg">Applicants Queue</h3>
                    <p class="text-xs text-slate-500 mt-1">Select an applicant to view full details and approve/reject.</p>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    
                    {{-- 
                        ITEM 1: MARIA SANTOS
                        CHANGE: Added <a> tag with href and wire:navigate for SPA feel
                    --}}
                    <a href="/admin/registrations/1" wire:navigate class="group block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors bg-blue-50/50 dark:bg-blue-900/10 border-l-4 border-l-blue-600">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="size-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                                MS
                            </div>

                            {{-- User Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-slate-900 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Maria Santos</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 flex-shrink-0">New</span>
                                </div>
                                <p class="text-sm text-slate-500 truncate">maria.santos@email.com</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-slate-400"><flux:icon icon="calendar" class="size-3 inline" /> 2h ago</span>
                                    <span class="text-xs text-amber-600 font-medium"><flux:icon icon="document-text" class="size-3 inline" /> 4 docs</span>
                                </div>
                            </div>

                            {{-- Chevron / Action --}}
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    Pending Review
                                </span>
                                <flux:button size="sm" variant="ghost" icon="arrow-right" class="group-hover:translate-x-1 transition-transform">View</flux:button>
                            </div>
                        </div>
                    </a>

                    {{-- ITEM 2: JUAN DELA CRUZ --}}
                    <a href="/admin/registrations/2" wire:navigate class="group block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                                JD
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-slate-900 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Juan Dela Cruz</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 flex-shrink-0">Complete</span>
                                </div>
                                <p class="text-sm text-slate-500 truncate">juan.delacruz@email.com</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-slate-400"><flux:icon icon="calendar" class="size-3 inline" /> 5h ago</span>
                                    <span class="text-xs text-emerald-600 font-medium"><flux:icon icon="check-circle" class="size-3 inline" /> Ready</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Pending Review</span>
                                <flux:icon icon="chevron-right" class="size-5 text-slate-400 group-hover:text-slate-600" />
                            </div>
                        </div>
                    </a>

                     {{-- ITEM 3: ANA REYES --}}
                     <a href="/admin/registrations/3" wire:navigate class="group block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                                AR
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-slate-900 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Ana Reyes</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 flex-shrink-0">Incomplete</span>
                                </div>
                                <p class="text-sm text-slate-500 truncate">ana.reyes@email.com</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-slate-400"><flux:icon icon="calendar" class="size-3 inline" /> 1d ago</span>
                                    <span class="text-xs text-red-600 font-medium"><flux:icon icon="exclamation-triangle" class="size-3 inline" /> Missing Med Cert</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Action Required</span>
                                <flux:icon icon="chevron-right" class="size-5 text-slate-400 group-hover:text-slate-600" />
                            </div>
                        </div>
                    </a>

                </div>

                {{-- Pagination --}}
                <div class="p-5 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Showing 1-5 of 12 applicants</span>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="ghost" icon="chevron-left" disabled>Previous</flux:button>
                            <flux:button size="sm" variant="ghost">Next</flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: DASHBOARD WIDGETS (1/3 width) --}}
        {{-- Replaced "Specific User Details" with "General Admin Tools" since we navigate away for details --}}
        <div class="space-y-6">

            {{-- Quick Actions --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm p-5">
                <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 mb-4">Quick Actions</h4>
                <div class="space-y-2">
                    <button class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                        <flux:icon icon="arrow-down-tray" class="size-5 text-slate-400" />
                        Export CSV
                    </button>
                    <button class="w-full flex items-center gap-3 p-3 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-slate-700 dark:text-slate-300 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                        <flux:icon icon="envelope" class="size-5 text-slate-400" />
                        Email All Pending
                    </button>
                </div>
            </div>

            {{-- Recent Activity Log --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100">Recent Activity</h4>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-3">
                        <div class="mt-1 size-2 rounded-full bg-emerald-500"></div>
                        <div>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">AdminUser</span> approved <span class="font-semibold">Pedro P.</span></p>
                            <p class="text-xs text-slate-500">10 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="mt-1 size-2 rounded-full bg-red-500"></div>
                        <div>
                            <p class="text-sm text-slate-700 dark:text-slate-300">System rejected document for <span class="font-semibold">Ana R.</span></p>
                            <p class="text-xs text-slate-500">1 hour ago</p>
                        </div>
                    </div>
                     <div class="flex gap-3">
                        <div class="mt-1 size-2 rounded-full bg-blue-500"></div>
                        <div>
                            <p class="text-sm text-slate-700 dark:text-slate-300">New registration: <span class="font-semibold">Kim L.</span></p>
                            <p class="text-xs text-slate-500">2 hours ago</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                    <flux:button variant="ghost" size="sm" class="w-full text-xs">View Full Log</flux:button>
                </div>
            </div>

            {{-- Verification Stats --}}
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm p-5">
                <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 mb-4">Verification Stats</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">This Week</span>
                        <span class="font-bold">42 verified</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Rejection Rate</span>
                        <span class="font-bold text-red-600">8.5%</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>LTO Compliance</span>
                        <span class="text-emerald-500 font-medium">Good</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>