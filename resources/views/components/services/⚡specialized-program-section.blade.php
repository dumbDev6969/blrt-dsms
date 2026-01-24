<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, 
    so that we may fear less. - Maria Skłodowska-Curie --}}

    <section class="relative py-24 bg-white dark:bg-zinc-950">
    <div class="max-w-7xl mx-auto px-6">
    
        {{-- Section Header: Left-aligned for stronger visual hierarchy --}}
        <div class="max-w-2xl mb-16 space-y-4">
            <h1 level="2" class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                Beyond the <span class="text-accent">basics</span>.
            </h1>
            
            <flux:text size="lg" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                Already licensed but need a confidence boost? We offer specialized training tailored to your specific goals.
            </flux:text>
        </div>

        {{-- 3-Column Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- 1. Refresher Course --}}
            <div class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-2xl hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 transition-all duration-300">
                <div class="mb-6 inline-flex p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent w-fit group-hover:scale-110 transition-transform duration-300">
                    <flux:icon name="arrow-path" variant="outline" class="size-6" />
                </div>
                
                <flux:heading size="lg" class="mb-2 font-bold">Refresher Course</flux:heading>
                <flux:text class="mb-6 flex-1 text-zinc-500 dark:text-zinc-400">
                    Designed for existing license holders who haven't driven in years or lost their confidence on the road.
                </flux:text>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Highway & Expressway Merging
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Advanced Parking Techniques
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Defensive Driving Review
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn -ml-3 px-3">
                    View rates
                    <flux:icon.arrow-right class="size-4 text-zinc-400 group-hover/btn:translate-x-1 transition-transform" />
                </flux:button>
            </div>

            {{-- 2. Executive / VIP --}}
            <div class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-2xl hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 transition-all duration-300">
                <div class="mb-6 inline-flex p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent w-fit group-hover:scale-110 transition-transform duration-300">
                    <flux:icon name="star" variant="outline" class="size-6" />
                </div>
                
                <flux:heading size="lg" class="mb-2 font-bold">Executive VIP</flux:heading>
                <flux:text class="mb-6 flex-1 text-zinc-500 dark:text-zinc-400">
                    Premium door-to-door service. We pick you up from your home or office and drop you off after the session.
                </flux:text>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Home Pickup & Drop-off
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Priority Scheduling
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Premium Sedan Units
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn -ml-3 px-3">
                    Check availability
                    <flux:icon.arrow-right class="size-4 text-zinc-400 group-hover/btn:translate-x-1 transition-transform" />
                </flux:button>
            </div>

            {{-- 3. Assessment & Exam Help --}}
            <div class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-2xl hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 transition-all duration-300">
                <div class="mb-6 inline-flex p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent w-fit group-hover:scale-110 transition-transform duration-300">
                    <flux:icon name="clipboard-document-check" variant="outline" class="size-6" />
                </div>
                
                <flux:heading size="lg" class="mb-2 font-bold">Pre-Exam Assessment</flux:heading>
                <flux:text class="mb-6 flex-1 text-zinc-500 dark:text-zinc-400">
                    Nervous about the LTO Practical Exam? Take our mock assessment to gauge your readiness.
                </flux:text>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Mock Practical Exam
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Car Rental for LTO Exam
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Process Assistance
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn -ml-3 px-3">
                    Book assessment
                    <flux:icon.arrow-right class="size-4 text-zinc-400 group-hover/btn:translate-x-1 transition-transform" />
                </flux:button>
            </div>

        </div>

        {{-- Bottom CTA Strip (Spotlight) --}}
        <div 
            x-data="{ x: 0, y: 0 }" 
            @mousemove="x = $event.clientX - $el.getBoundingClientRect().left; y = $event.clientY - $el.getBoundingClientRect().top"
            class="group relative mt-16 p-8 bg-zinc-900 dark:bg-zinc-800 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden ring-1 ring-white/10"
        >
            {{-- Background Effects --}}
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div 
                class="pointer-events-none absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100"
                :style="`background: radial-gradient(600px circle at ${x}px ${y}px, rgba(255,255,255,0.06), transparent 40%);`"
            ></div>
            
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-xl font-bold text-white mb-1">Unsure which course is right for you?</h3>
                <p class="text-zinc-400 text-sm">Our registrars can assess your current skill level for free.</p>
            </div>
            
            <div class="relative z-10 w-full md:w-auto">
                <flux:button variant="primary" class="w-full md:w-auto">
                    Contact Us
                </flux:button>
            </div>
        </div>
    </div>
</section>
</div>