<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
    <section class="relative overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40 dark:opacity-20 [mask-image:radial-gradient(ellipse_at_center,black_70%,transparent_100%)]">
            <div class="absolute inset-0 h-full w-full bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 pt-20 pb-24 lg:pt-32">
            
            {{-- Content Wrapper --}}
            <div class="flex flex-col w-full">
                
                {{-- 1. Badge --}}
                <div class="mb-6 flex animate-fade-in-up duration-500">
                    <flux:badge size="sm" color="blue">
                        <flux:icon.check-badge class="w-6 h-6 mr-1" />
                        LTO Accredited
                    </flux:badge>
                </div>

                {{-- 2. Headline --}}
                <div class="mb-8 w-full animate-fade-in-up delay-100 duration-700">
                    <h1 level="1" class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                        Master the <span class="text-accent">road</span> <br />
                        and drive with <span class="text-accent">confidence</span>.
                    </h1>
                </div>

                {{-- 3. Sub-headline (Crucial for professional look) --}}
                <div class="mb-10 max-w-2xl animate-fade-in-up delay-200 duration-700">
                    <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        San Carlos City's premier LTO-accredited academy. Dedicated to producing disciplined and responsible road users through comprehensive Theoretical and Practical training since 2021.
                    </flux:text>
                </div>

                {{-- 4. CTAs --}}
                <div class="flex flex-wrap gap-4 mb-20 animate-fade-in-up delay-300 duration-700">
                    <flux:button variant="primary" class="min-w-[140px] shadow-lg shadow-blue-500/20">
                        <a href="{{ route('register') }}">
                            Enroll now
                        </a>
                    </flux:button>

                    <flux:button variant="ghost" icon-trailing="arrow-right" class="group">
                        <a href="{{ route('guest.services') }}">
                            View curriculum
                        </a>
                    </flux:button>
                </div>
            </div>

            {{-- 5. Trust/Stats Section (Separated into a grid) --}}
            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-8">
                    <flux:text variant="subtle" class="text-sm font-semibold uppercase tracking-wider text-zinc-500">
                        Why students choose BLRT
                    </flux:text>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    
                    {{-- Stat 1 --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-accent dark:text-blue-400">
                                <flux:icon name="trophy" variant="mini" class="size-6" />
                            </div>
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">98%</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Passing Rate</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">First-time success</p>
                        </div>
                    </div>

                    {{-- Stat 2 --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-accent dark:text-blue-400">
                                <flux:icon name="calendar-days" variant="mini" class="size-6" />
                            </div>
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">Flex</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Schedule</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Nights & Weekends</p>
                        </div>
                    </div>

                    {{-- Stat 3 --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-accent dark:text-blue-400">
                                <flux:icon name="truck" variant="mini" class="size-6" />
                            </div>
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">Fleet</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Modern Cars</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Manual & Automatic</p>
                        </div>
                    </div>

                    {{-- Stat 4 --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-accent dark:text-blue-400">
                                <flux:icon name="document-check" variant="mini" class="size-6" />
                            </div>
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">LTO</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Accredited</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">TDC & PDC Certs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>