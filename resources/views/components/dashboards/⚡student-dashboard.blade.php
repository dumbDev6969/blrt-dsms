<?php

use Livewire\Component;
use App\Models\Document;
use Livewire\Attributes\Computed;
new class extends Component {
    // Check if the student uploaded at least one document
    #[Computed]
    public function hasDocument()
    {
        if (Document::where('user_id', Auth::user()->id)->exists()) {
            return true;
        }
    }

    #[Computed]
    public function isComplete()
    {
        if (Document::where('user_id', Auth::user()->id)->where('status', 'approved')->exists()) {
            return true;
        }
    }
};
?>


    {{-- People find pleasure in different ways. I find it in keeping my mind clear. - Marcus Aurelius --}}

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <flux:callout icon="exclamation-triangle" variant="warning" class="w-full">
            @if ($this->isComplete)
                {{-- STATE 1: COMPLETE --}}
                <flux:callout.heading class="text-green-600">
                    Documents Complete
                </flux:callout.heading>
                <flux:callout.text>
                    You have submitted all required documents. We are now verifying your application.
                </flux:callout.text>
            @elseif ($this->hasDocument())
                {{-- STATE 2: INCOMPLETE (User has started, but not finished) --}}
                <flux:callout.heading class="text-yellow-600">
                    Your documents are incomplete
                </flux:callout.heading>
                <flux:callout.text>
                    Please upload the remaining documents to proceed with your driving journey.
                </flux:callout.text>
            @else
                {{-- STATE 3: EMPTY (User hasn't started) --}}
                <flux:callout.heading>
                    You haven't uploaded documents yet
                </flux:callout.heading>
                <flux:callout.text>
                    Upload your documents to start your driving journey.
                </flux:callout.text>
            @endif

            <x-slot name="actions">
                <flux:button size="sm">
                    <a href="{{ route('document.upload') }}" wire:navigate>Upload Documents</a>
                </flux:button>
            </x-slot>
        </flux:callout>
        {{-- Top Stats / Status Grid --}}
        <div class="grid auto-rows-min gap-6 md:grid-cols-3">

            {{-- CARD 1: COMPLIANCE STATUS --}}
            {{-- Purpose: Tells the user if they are legally allowed to drive/enroll yet --}}
            <div
                class="relative overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex items-center justify-center size-10 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <flux:icon icon="check-circle" class="size-6" />
                        {{-- Or use <svg> if flux not available --}}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Account Status</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Compliance & Requirements</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Profile Completion</span>
                        <span
                            class="text-emerald-600 font-medium text-xs bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-full">100%
                            Complete</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Documents</span>
                        {{-- Static "Pending" state for demo --}}
                        <span
                            class="text-amber-600 font-medium text-xs bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-full">Under
                            Review</span>
                    </div>
                </div>
            </div>

            {{-- CARD 2: CURRENT ENROLLMENT --}}
            {{-- Purpose: The "Empty State" prompting them to take action --}}
            <div
                class="relative overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex items-center justify-center size-10 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <flux:icon icon="academic-cap" class="size-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Current Enrollment</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Active Courses</p>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center h-20 text-center">
                    <span class="text-sm font-medium text-slate-400 dark:text-slate-500">No active courses</span>
                    <p class="text-xs text-slate-400 mt-1">Select a course below to begin.</p>
                </div>
            </div>

            {{-- CARD 3: SUPPORT / WALLET --}}
            {{-- Purpose: Quick help or Balance check --}}
            <div
                class="relative overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex items-center justify-center size-10 rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                        <flux:icon icon="chat-bubble-left-right" class="size-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Need Help?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Support & Inquiries</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <button
                        class="w-full text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 py-2 rounded-lg transition-colors">
                        Contact Admin
                    </button>
                    <button
                        class="w-full text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 py-2 rounded-lg transition-colors">
                        View FAQs
                    </button>
                </div>
            </div>
        </div>

        {{-- Bottom Section: COURSE CATALOG --}}
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Available Courses</h2>
                <p class="text-sm text-slate-500">Select a program to start your driving journey.</p>
            </div>

            {{-- Course Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- COURSE 1: TDC (Highlighted) --}}
                <div
                    class="group relative rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 hover:border-[var(--color-accent)] dark:hover:border-[var(--color-accent)] transition-all duration-300">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                                <flux:icon icon="book-open" class="size-6 text-[var(--color-accent)]" />
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Recommended
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Theoretical Driving Course
                        </h3>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2">Required 15-hour seminar for Student Permit
                            applicants. Covers traffic laws and road safety.</p>

                        <div class="mt-6 flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-900 dark:text-slate-100">₱1,500</span>
                            <flux:button variant="primary" size="sm" icon="arrow-right">Enroll</flux:button>
                        </div>
                    </div>
                </div>

                {{-- COURSE 2: PDC --}}
                <div
                    class="group relative rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <flux:icon icon="truck" class="size-6 text-slate-600 dark:text-slate-400" />
                            </div>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Practical Driving Course</h3>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2">8-hour hands-on driving instruction.
                            Prerequisite for Non-Professional Driver's License.</p>

                        <div class="mt-6 flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-900 dark:text-slate-100">₱4,500</span>
                            <flux:button variant="ghost" size="sm" icon="arrow-right">Details</flux:button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Your Roadmap to a Driver's License
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Track your progress from student permit to
                        non-professional license.</p>
                </div>

                {{-- Roadmap Container --}}
                <div class="relative">
                    {{-- Connecting Line --}}
                    <div
                        class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 dark:bg-slate-800 -translate-y-1/2 rounded-full hidden md:block">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">

                        {{-- Step 1: TDC (Current Step) --}}
                        <div class="relative group">
                            <div
                                class="flex flex-col items-center text-center p-4 bg-white dark:bg-slate-900 rounded-xl border-2 border-[var(--color-accent)] shadow-sm z-10 relative">
                                <div
                                    class="flex items-center justify-center size-10 rounded-full bg-blue-50 text-[var(--color-accent)] mb-3">
                                    <flux:icon icon="book-open" class="size-5" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">1. Theoretical (TDC)
                                </h4>
                                <p class="text-xs text-slate-500 mt-1">15-hr Seminar</p>
                                <span
                                    class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    You are here
                                </span>
                            </div>
                        </div>

                        {{-- Step 2: Student Permit --}}
                        <div class="relative group opacity-60">
                            <div
                                class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                                <div
                                    class="flex items-center justify-center size-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 mb-3">
                                    <flux:icon icon="identification" class="size-5" />
                                </div>
                                <h4 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">2. Student Permit
                                </h4>
                                <p class="text-xs text-slate-500 mt-1">Apply at LTO</p>
                            </div>
                        </div>

                        {{-- Step 3: PDC --}}
                        <div class="relative group opacity-60">
                            <div
                                class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                                <div
                                    class="flex items-center justify-center size-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 mb-3">
                                    <flux:icon icon="truck" class="size-5" />
                                </div>
                                <h4 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">3. Practical (PDC)
                                </h4>
                                <p class="text-xs text-slate-500 mt-1">8-hr Driving</p>
                            </div>
                        </div>

                        {{-- Step 4: License --}}
                        <div class="relative group opacity-60">
                            <div
                                class="flex flex-col items-center text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 z-10 relative">
                                <div
                                    class="flex items-center justify-center size-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 mb-3">
                                    <flux:icon icon="star" class="size-5" />
                                </div>
                                <h4 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">4. Driver's
                                    License
                                </h4>
                                <p class="text-xs text-slate-500 mt-1">Final Exam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



