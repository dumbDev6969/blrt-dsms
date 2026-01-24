<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    {{-- We must ship. - Taylor Otwell --}}
    <section class="relative pt-12 pb-16 lg:pt-32 lg:pb-40 overflow-hidden bg-white dark:bg-zinc-950">

        {{-- Background Texture --}}
        <div class="absolute inset-0 z-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none"
            style="background-image: radial-gradient(#71717a 1px, transparent 1px); background-size: 32px 32px;">
        </div>

        {{-- Ambient Glow --}}
        <div
            class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-[600px] h-[600px] lg:w-[800px] lg:h-[800px] bg-accent/5 rounded-full blur-3xl pointer-events-none">
        </div>

        <flux:container class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">

                {{-- Left Column: Brand Narrative --}}
                <div class="flex flex-col items-start order-2 lg:order-1">

                    {{-- Badge --}}
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 mb-6 lg:mb-8">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                        </span>
                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 uppercase tracking-wide">Est.
                            2008</span>
                    </div>

                    {{-- Heading --}}
                    <h1 level="1"
                        class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-tighter leading-[0.95] text-zinc-900 dark:text-white mb-6 lg:mb-8">
                        More than just <br>
                        <span class="text-accent dark:text-zinc-700">a permit.</span>
                    </h1>

                    <flux:text class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        We started BLRT with a belief that the road belongs to the disciplined. For over 15 years, we’ve
                        been shaping the driving culture of the Philippines—one student at a time.
                    </flux:text>

                    {{-- CTA Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <flux:button variant="primary" icon="arrow-down" class="w-full sm:w-auto justify-center">
                            Read our story
                        </flux:button>

                        <flux:button variant="ghost" class="w-full sm:w-auto justify-center">
                            Meet the team
                        </flux:button>
                    </div>

                    {{-- Trust Strip --}}
                    <div
                        class="mt-10 lg:mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800 w-full flex items-center gap-6">
                        <div class="flex -space-x-3">
                            <div
                                class="size-10 rounded-full ring-2 ring-white dark:ring-zinc-950 bg-zinc-200 dark:bg-zinc-800">
                            </div>
                            <div
                                class="size-10 rounded-full ring-2 ring-white dark:ring-zinc-950 bg-zinc-300 dark:bg-zinc-700">
                            </div>
                            <div
                                class="size-10 rounded-full ring-2 ring-white dark:ring-zinc-950 bg-zinc-400 dark:bg-zinc-600">
                            </div>
                            <div
                                class="size-10 rounded-full ring-2 ring-white dark:ring-zinc-950 bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center text-xs font-bold text-zinc-500">
                                +15k
                            </div>
                        </div>
                        <div class="text-sm">
                            <p class="font-bold text-zinc-900 dark:text-white">Trusted by thousands</p>
                            <p class="text-zinc-500">From student to pro.</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Image Area --}}
                <div class="order-1 lg:order-2 w-full">

                    {{-- VERSION A: Mobile & Tablet (Static, tidy single image) --}}
                    {{-- Only visible on screens smaller than LG --}}
                    <div
                        class="block lg:hidden relative w-full aspect-[4/3] bg-zinc-200 dark:bg-zinc-800 rounded-3xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-lg">
                        {{-- Place your static mobile image here --}}
                        <div
                            class="absolute inset-0 flex items-center justify-center text-zinc-400 italic bg-zinc-100 dark:bg-zinc-900">
                            [Mobile Hero Image]
                        </div>
                    </div>

                    {{-- VERSION B: Desktop (The Dynamic Floating Stack) --}}
                    {{-- Only visible on LG screens and up --}}
                    <div class="hidden lg:block relative h-[600px] w-full perspective-[1000px]">

                        {{-- Image 1: Main (Back) --}}
                        <div
                            class="absolute top-0 right-0 w-3/4 h-[400px] bg-zinc-200 dark:bg-zinc-800 rounded-2xl overflow-hidden shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500 ease-out border-4 border-white dark:border-zinc-900 origin-bottom-right">
                            <div
                                class="w-full h-full flex items-center justify-center text-zinc-400 italic bg-zinc-100 dark:bg-zinc-900">
                                [Main Desktop Image]
                            </div>
                        </div>

                        {{-- Image 2: Secondary (Front) --}}
                        <div
                            class="absolute bottom-10 left-4 w-2/3 h-[300px] bg-zinc-300 dark:bg-zinc-700 rounded-2xl overflow-hidden shadow-2xl -rotate-2 hover:rotate-0 transition-transform duration-500 ease-out border-4 border-white dark:border-zinc-900 z-10 origin-bottom-left">
                            <div
                                class="w-full h-full flex items-center justify-center text-zinc-500 italic bg-zinc-200 dark:bg-zinc-800">
                                [Secondary Detail Image]
                            </div>
                        </div>

                        {{-- Floating Stat Card --}}
                        <div
                            class="absolute top-1/2 left-0 -translate-y-1/2 -translate-x-4 bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-xl border border-zinc-100 dark:border-zinc-800 z-20 animate-bounce [animation-duration:3s]">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-accent rounded-lg">
                                    <flux:icon name="trophy" variant="solid" class="size-6" />
                                </div>
                                <div>
                                    <p class="text-3xl font-black text-zinc-900 dark:text-white">16</p>
                                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Years of Service
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </flux:container>
    </section>
</div>
