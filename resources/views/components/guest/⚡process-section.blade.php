<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    <section class="relative py-24 lg:py-32 bg-zinc-50 dark:bg-zinc-900/50">
        <flux:container>
            <div class="max-w-3xl mb-16 lg:mb-20">
                <flux:badge size="sm" color="blue">
                    <flux:icon.arrow-path class="w-5 h-5 mr-1" />
                    How it works
                </flux:badge>

                <h2 class="mb-8 text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                    Your path to <span class="text-accent">freedom</span> in 5 steps.
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                {{-- Step 1 --}}
                <div class="relative flex flex-col items-center text-center group">
                    <div class="mb-6 flex items-center justify-center size-16 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 text-accent font-black text-2xl group-hover:scale-110 transition-transform">
                        1
                    </div>
                    <flux:heading size="lg" class="mb-3">Register Online</flux:heading>
                    <flux:text size="sm" variant="subtle">Create your account and submit your required documents securely.</flux:text>
                </div>

                {{-- Step 2 --}}
                <div class="relative flex flex-col items-center text-center group">
                    <div class="mb-6 flex items-center justify-center size-16 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 text-accent font-black text-2xl group-hover:scale-110 transition-transform">
                        2
                    </div>
                    <flux:heading size="lg" class="mb-3">Get Verified</flux:heading>
                    <flux:text size="sm" variant="subtle">Our team reviews your documents to ensure LTO compliance.</flux:text>
                </div>

                {{-- Step 3 --}}
                <div class="relative flex flex-col items-center text-center group">
                    <div class="mb-6 flex items-center justify-center size-16 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 text-accent font-black text-2xl group-hover:scale-110 transition-transform">
                        3
                    </div>
                    <flux:heading size="lg" class="mb-3">Enroll in Course</flux:heading>
                    <flux:text size="sm" variant="subtle">Select your desired TDC or PDC package and set your schedule.</flux:text>
                </div>

                {{-- Step 4 --}}
                <div class="relative flex flex-col items-center text-center group">
                    <div class="mb-6 flex items-center justify-center size-16 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 text-accent font-black text-2xl group-hover:scale-110 transition-transform">
                        4
                    </div>
                    <flux:heading size="lg" class="mb-3">Start Driving</flux:heading>
                    <flux:text size="sm" variant="subtle">Begin your sessions with our patient, LTO-certified instructors.</flux:text>
                </div>

                {{-- Step 5 --}}
                <div class="relative flex flex-col items-center text-center group">
                    <div class="mb-6 flex items-center justify-center size-16 rounded-2xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200 dark:border-zinc-700 text-accent font-black text-2xl group-hover:scale-110 transition-transform">
                        5
                    </div>
                    <flux:heading size="lg" class="mb-3">Get Certified</flux:heading>
                    <flux:text size="sm" variant="subtle">Pass your assessment and receive your LTO-recognized certificate.</flux:text>
                </div>
            </div>
        </flux:container>
    </section>
</div>
