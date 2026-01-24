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

    <section class="relative py-24">
    <div class="max-w-7xl mx-auto px-6">
    
        {{-- Section Header: Centered for variety --}}
        <div class="max-w-3xl mx-auto mb-16">
            <h2 class="text-start text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                Beyond the <span class="text-accent">basics</span>.
            </h2>
            <flux:text variant="subtle" class="text-lg text-zinc-600 dark:text-zinc-400">
                Already have a license? Need a confidence boost? We offer specialized training modules tailored to specific driving needs.
            </flux:text>
        </div>

        {{-- 3-Column Grid for Add-ons --}}
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
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Highway & Expressway Merging
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Advanced Parking Techniques
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Defensive Driving Review
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn">
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
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Home Pickup & Drop-off
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Priority Scheduling
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Premium Sedan Units
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn">
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
                    Nervous about the LTO Practical Exam? Take our mock assessment to gauge your readiness and rent our car for the test.
                </flux:text>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Mock Practical Exam
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Car Rental for LTO Exam
                    </li>
                    <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon.check class="size-4 text-accent" />
                        Process Assistance
                    </li>
                </ul>

                <flux:button variant="ghost" class="w-full justify-between group/btn">
                    Book assessment
                    <flux:icon.arrow-right class="size-4 text-zinc-400 group-hover/btn:translate-x-1 transition-transform" />
                </flux:button>
            </div>

        </div>

        {{-- Bottom CTA Strip --}}
       <div 
    {{-- 1. Initialize coordinates --}}
    x-data="{ x: 0, y: 0 }" 
    {{-- 2. Update coordinates on mouse move --}}
    @mousemove="
        const rect = $el.getBoundingClientRect();
        x = $event.clientX - rect.left;
        y = $event.clientY - rect.top;
    "
    {{-- Added 'group' class to trigger hover state --}}
    class="group mt-16 p-8 bg-zinc-900 dark:bg-zinc-800 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden relative"
>
    
    {{-- Existing Static Decorative blob (Base Ambiance) --}}
    <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>

    {{-- 3. THE SPOTLIGHT LAYER --}}
    <div 
        class="pointer-events-none absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100"
        :style="`background: radial-gradient(600px circle at ${x}px ${y}px, var(--color-accent) 0%, transparent 40%); opacity: 0.25;`"
    ></div>
    
    {{-- Content --}}
    <div class="relative z-10">
        <h3 class="text-xl font-bold text-white mb-2">Unsure which course is right for you?</h3>
        <p class="text-zinc-400">Our registrars can assess your current skill level for free.</p>
    </div>
    
    <div class="relative z-10">
        <flux:button variant="filled" class="bg-white text-zinc-900 hover:bg-zinc-100 border-none">
            Contact Us
        </flux:button>
    </div>
</div>
    </div>
</section>
</div>