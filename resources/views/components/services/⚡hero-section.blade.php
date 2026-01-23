<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca --}}
   <section class="relative overflow-hidden">
    {{-- Added a subtle background pattern or gradient here could differentiate it from home, 
         but kept clean to match your request exactly. --}}
         
    <div class="relative z-10 max-w-7xl mx-auto px-6 pt-20 pb-16 lg:pt-32 lg:pb-24">
        
        {{-- Content Wrapper --}}
        <div class="flex flex-col w-full">
            
            {{-- 1. Badge: Contextualized for Services --}}
            {{-- Using the 'book-open' icon similar to your courses header for consistency --}}
            <div class="mb-6 flex animate-fade-in-up">
                <flux:badge size="sm" color="blue">
                    <flux:icon.book-open class="w-5 h-5 mr-1" />
                    Our Programs
                </flux:badge>
            </div>

            {{-- 2. Headline: Massive scale, focused on the breadth of offering --}}
            <div class="mb-8 w-full">
                <h1 level="1" class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                    Expert training for <br />
                    every <span class="text-accent">milestone</span>.
                </h1>
            </div>

            {{-- 3. Sub-headline: Sets expectations for what's below --}}
            <div class="mb-10 max-w-2xl">
                <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                    From securing your student permit to mastering defensive driving. Explore our LTO-accredited curriculums designed for every skill level.
                </flux:text>
            </div>

            {{-- 4. CTAs: Action oriented towards selection --}}
            <div class="flex flex-wrap gap-4">
                {{-- Primary action: help them choose --}}
                <flux:button variant="primary" class="min-w-[140px] shadow-lg shadow-blue-500/20">
                    Talk to an advisor
                </flux:button>

                 {{-- Secondary action: leads down to the service cards --}}
                 {{-- Changed icon to 'arrow-down' to indicate scrolling to content below --}}
                <flux:button variant="ghost" icon-trailing="arrow-down" class="group">
                    Browse courses below
                </flux:button>
            </div>
        </div>
    </div>
</section>
</div>