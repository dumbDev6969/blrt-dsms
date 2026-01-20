<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- Life is available only in the present moment. - Thich Nhat Hanh --}}
    <section class="relative bg-white dark:bg-zinc-950 py-24 lg:py-32">
   

    <flux:container>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
            
            {{-- Left Side: Narrative --}}
            <div class="lg:col-span-7">
                <flux:badge size="sm" color="blue" class="mb-6">
                    <flux:icon.user-group class="w-5 h-5 mr-1" />
                    Our Story
                </flux:badge>

                <h2 class="mb-8 text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                    Driven by <span class="text-accent">excellence</span>, committed to <span class="text-accent">safety</span>.
                </h2>

                <div class="space-y-6">
                    <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Established in 2008, BLRT Driving Academy was born out of a simple mission: to transform the way Filipinos learn to drive. We believe that a driver's license isn't just a permit—it's a responsibility.
                    </flux:text>

                    <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Our academy stands at the forefront of road safety education in the region. By combining LTO-standard curriculum with modern defensive driving techniques, we ensure our students don't just pass the exam—they become masters of the road.
                    </flux:text>
                </div>

                {{-- Key Pillars --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-12">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm">
                            <flux:icon name="shield-check" class="text-accent size-6" />
                        </div>
                        <div>
                            <flux:text class="font-bold text-zinc-900 dark:text-white">Safety First</flux:text>
                            <flux:text size="sm" variant="subtle">Zero-accident training record across all practical sessions.</flux:text>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm">
                            <flux:icon name="academic-cap" class="text-accent size-6" />
                        </div>
                        <div>
                            <flux:text class="font-bold text-zinc-900 dark:text-white">Expert Led</flux:text>
                            <flux:text size="sm" variant="subtle">Certified instructors with over 10+ years of road experience.</flux:text>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Side: Impact Card --}}
            <div class="lg:col-span-5">
                <div class="relative group">
                    {{-- Decorative border effect --}}
                    <div class="absolute  rounded-2xl "></div>
                    
                    <div class="relative flex flex-col p-8 lg:p-12 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl">
                        <flux:heading size="xl" class="mb-6">Our Mission</flux:heading>
                        
                        <flux:text class="text-lg italic mb-10 text-zinc-500 dark:text-zinc-400">
                            "To produce the next generation of disciplined, skilled, and safety-conscious drivers through innovative education and personalized mentorship."
                        </flux:text>

                        <div class="space-y-8">
                            <div>
                                <flux:text class="text-3xl font-black text-accent mb-1">15,000+</flux:text>
                                <flux:text variant="subtle" class="uppercase tracking-widest text-xs font-bold">Graduated Students</flux:text>
                            </div>
                            
                            <div class="pt-8 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:text class="text-3xl font-black text-zinc-900 dark:text-white mb-1">9/10</flux:text>
                                <flux:text variant="subtle" class="uppercase tracking-widest text-xs font-bold">Passing Rate on 1st Attempt</flux:text>
                            </div>

                            <flux:button variant="ghost" icon-trailing="arrow-up-right" class="mt-4">
                                Meet the Instructors
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </flux:container>
</section>
</div>