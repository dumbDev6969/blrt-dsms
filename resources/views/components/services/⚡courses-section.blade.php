<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- Simplicity is the ultimate sophistication. - Leonardo da Vinci --}}
    <section class="relative py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-6">
        
        {{-- Section Header --}}
        {{-- Kept simpler than the Hero to let the content breathe, but still massive --}}
        <div class="max-w-3xl mb-16">
            <h1 class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                Core <span class="text-accent">Curriculum</span>
            </h2>
            <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                The two essential steps to getting your license in the Philippines. Start with theory, finish with practice.
            </flux:text>
        </div>

        {{-- Courses Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">

            {{-- 1. TDC Card --}}
            <div class="flex flex-col h-full p-8 lg:p-10 border border-zinc-200 dark:border-zinc-800 rounded-2xl hover:border-blue-500/50 transition-all duration-300 bg-white dark:bg-zinc-900/50 shadow-sm">
                
                {{-- Card Header --}}
                <div class="flex justify-between items-start mb-8">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent">
                        {{-- Icon: Book/Theory --}}
                        <flux:icon name="book-open" variant="outline" class="size-8" />
                    </div>
                    <flux:badge size="sm" color="zinc" variant="pill" class="font-mono">
                        15 Hours
                    </flux:badge>
                </div>

                {{-- Content --}}
                <div class="flex-1 flex flex-col">
                    <div class="mb-6">
                        <flux:heading size="xl" class="mb-2 font-bold">Theoretical Driving Course (TDC)</flux:heading>
                        <flux:text size="sm" class="text-accent font-medium mb-4">Step 1: Get your Student Permit</flux:text>
                        <flux:text class="leading-relaxed text-zinc-600 dark:text-zinc-400">
                            The mandatory classroom session for all aspiring drivers. We cover traffic laws, road signs, and essential defensive driving safety protocols required by the LTO.
                        </flux:text>
                    </div>

                    {{-- Feature List --}}
                    <div class="space-y-4 mb-10 mt-auto">
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">LTO Validated Certificate</span>
                                <span class="text-xs text-zinc-500">Uploaded directly to LTO portal</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Comprehensive Review</span>
                                <span class="text-xs text-zinc-500">Includes mock exams for the real test</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Air-conditioned Classrooms</span>
                                <span class="text-xs text-zinc-500">Or accredited online sessions available</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <flux:button variant="primary" class="w-full justify-center shadow-lg shadow-blue-500/10">
                        Enroll in TDC
                    </flux:button>
                </div>
            </div>

            {{-- 2. PDC Card --}}
            <div class="flex flex-col h-full p-8 lg:p-10 border border-zinc-200 dark:border-zinc-800 rounded-2xl hover:border-blue-500/50 transition-all duration-300 bg-white dark:bg-zinc-900/50 shadow-sm">
                
                {{-- Card Header --}}
                <div class="flex justify-between items-start mb-8">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent">
                        {{-- Icon: Car/Practical --}}
                        <flux:icon name="truck" variant="outline" class="size-8" /> 
                        {{-- Note: 'truck' is used in your library for vehicles, otherwise use 'car' --}}
                    </div>
                    <flux:badge size="sm" color="zinc" variant="pill" class="font-mono">
                        8 Hours
                    </flux:badge>
                </div>

                {{-- Content --}}
                <div class="flex-1 flex flex-col">
                    <div class="mb-6">
                        <flux:heading size="xl" class="mb-2 font-bold">Practical Driving Course (PDC)</flux:heading>
                        <flux:text size="sm" class="text-accent font-medium mb-4">Step 2: Get your Non-Pro License</flux:text>
                        <flux:text class="leading-relaxed text-zinc-600 dark:text-zinc-400">
                            Get behind the wheel with certified instructors. This hands-on course teaches you vehicle handling, parking, and maneuvering in real city traffic conditions.
                        </flux:text>
                    </div>

                    {{-- Feature List --}}
                    <div class="space-y-4 mb-10 mt-auto">
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Flexible Scheduling</span>
                                <span class="text-xs text-zinc-500">Choose your slots (Weekends available)</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Choice of Vehicle</span>
                                <span class="text-xs text-zinc-500">Sedan, SUV, or Hatchback (MT/AT)</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">1-on-1 Instruction</span>
                                <span class="text-xs text-zinc-500">Focused attention on your skills</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <flux:button variant="primary" class="w-full justify-center shadow-lg shadow-blue-500/10">
                        Enroll in PDC
                    </flux:button>
                </div>
            </div>

        </div>
    </div>
</section>
</div>