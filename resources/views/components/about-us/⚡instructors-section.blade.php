<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius --}}
    <section class="py-24 bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-900">
    <flux:container>
        
        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
            <div class="max-w-2xl space-y-4">
                <h1 level="2" class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-tighter leading-[0.95] text-zinc-900 dark:text-white mb-6 lg:mb-8">
                    Master the road <br /> with the <span class="text-accent">masters</span>.
                </h1>
                
                <flux:subheading size="lg" class="max-w-lg">
                    Our instructors aren't just drivers—they are certified educators with decades of combined experience in road safety and vehicle control.
                </flux:subheading>
            </div>

            {{-- Desktop CTA --}}
            <div class="hidden md:block">
                <flux:button variant="ghost" icon-trailing="arrow-right">
                    View all certifications
                </flux:button>
            </div>
        </div>

        {{-- Instructor Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10 lg:gap-y-12">
            
            {{-- Instructor 1: Ramon --}}
            <div class="group flex flex-col items-start">
                <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 mb-6">
                    <img 
                        src="https://images.unsplash.com/photo-1566492031773-4f4e44671857?q=80&w=600&auto=format&fit=crop" 
                        alt="Ramon Bautista" 
                        class="w-full h-full object-cover transition-all duration-500 ease-out grayscale group-hover:grayscale-0 group-hover:scale-105"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white uppercase tracking-wider">
                            <flux:icon name="clock" variant="solid" class="size-3 text-accent" />
                            15 years exp.
                        </span>
                    </div>
                </div>
                <div class="w-full space-y-1">
                    <div class="flex items-center justify-between w-full">
                        <flux:heading size="lg" class="font-bold group-hover:text-accent transition-colors">
                            Ramon Bautista
                        </flux:heading>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 -translate-x-2 group-hover:translate-x-0">
                            <flux:icon name="check-badge" variant="solid" class="text-blue-500 size-5" />
                        </div>
                    </div>
                    <flux:text class="text-zinc-500 font-medium">Head Instructor</flux:text>
                    <div class="pt-2">
                         <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                            Defensive Driving
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instructor 2: Sarah --}}
            <div class="group flex flex-col items-start">
                <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 mb-6">
                    <img 
                        src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop" 
                        alt="Sarah Mendoza" 
                        class="w-full h-full object-cover transition-all duration-500 ease-out grayscale group-hover:grayscale-0 group-hover:scale-105"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white uppercase tracking-wider">
                            <flux:icon name="clock" variant="solid" class="size-3 text-accent" />
                            8 years exp.
                        </span>
                    </div>
                </div>
                <div class="w-full space-y-1">
                    <div class="flex items-center justify-between w-full">
                        <flux:heading size="lg" class="font-bold group-hover:text-accent transition-colors">
                            Sarah Mendoza
                        </flux:heading>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 -translate-x-2 group-hover:translate-x-0">
                            <flux:icon name="check-badge" variant="solid" class="text-blue-500 size-5" />
                        </div>
                    </div>
                    <flux:text class="text-zinc-500 font-medium">Senior Instructor</flux:text>
                    <div class="pt-2">
                         <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                            Manual Transmission
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instructor 3: Mark --}}
            <div class="group flex flex-col items-start">
                <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 mb-6">
                    <img 
                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=600&auto=format&fit=crop" 
                        alt="Mark Delos Reyes" 
                        class="w-full h-full object-cover transition-all duration-500 ease-out grayscale group-hover:grayscale-0 group-hover:scale-105"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white uppercase tracking-wider">
                            <flux:icon name="clock" variant="solid" class="size-3 text-accent" />
                            10 years exp.
                        </span>
                    </div>
                </div>
                <div class="w-full space-y-1">
                    <div class="flex items-center justify-between w-full">
                        <flux:heading size="lg" class="font-bold group-hover:text-accent transition-colors">
                            Mark Delos Reyes
                        </flux:heading>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 -translate-x-2 group-hover:translate-x-0">
                            <flux:icon name="check-badge" variant="solid" class="text-blue-500 size-5" />
                        </div>
                    </div>
                    <flux:text class="text-zinc-500 font-medium">PDC Specialist</flux:text>
                    <div class="pt-2">
                         <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                            Parallel Parking
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instructor 4: Jenny --}}
            <div class="group flex flex-col items-start">
                <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 mb-6">
                    <img 
                        src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=600&auto=format&fit=crop" 
                        alt="Jenny Lim" 
                        class="w-full h-full object-cover transition-all duration-500 ease-out grayscale group-hover:grayscale-0 group-hover:scale-105"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white uppercase tracking-wider">
                            <flux:icon name="clock" variant="solid" class="size-3 text-accent" />
                            6 years exp.
                        </span>
                    </div>
                </div>
                <div class="w-full space-y-1">
                    <div class="flex items-center justify-between w-full">
                        <flux:heading size="lg" class="font-bold group-hover:text-accent transition-colors">
                            Jenny Lim
                        </flux:heading>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 -translate-x-2 group-hover:translate-x-0">
                            <flux:icon name="check-badge" variant="solid" class="text-blue-500 size-5" />
                        </div>
                    </div>
                    <flux:text class="text-zinc-500 font-medium">Theory Instructor</flux:text>
                    <div class="pt-2">
                         <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                            LTO Guidelines
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Mobile CTA --}}
        <div class="mt-12 block md:hidden">
            <flux:button variant="outline" class="w-full justify-center">
                View all certifications
            </flux:button>
        </div>

    </flux:container>
</section>
</div>