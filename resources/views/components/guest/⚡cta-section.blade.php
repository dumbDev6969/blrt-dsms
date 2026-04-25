<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    <section class="relative py-24 lg:py-32 overflow-hidden">
        {{-- Background Accents --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full z-0 opacity-10 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-b from-accent to-transparent"></div>
        </div>

        <flux:container class="relative z-10 text-center">
            <div class="max-w-4xl mx-auto">
                <h2 class="mb-6 text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                    Start your <span class="text-accent">driving journey</span> today.
                </h2>
                
                <flux:text variant="subtle" class="mb-12 text-lg md:text-xl max-w-2xl mx-auto">
                    Don't wait to gain the skills and confidence you need. Join the region's premier driving academy and master the road with BLRT.
                </flux:text>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <flux:button variant="primary" class="w-full sm:w-auto px-12 py-6 text-lg font-bold shadow-2xl shadow-blue-500/20">
                        <a href="{{ route('register') }}">Create Account</a>
                    </flux:button>
                    
                    <flux:button variant="ghost" class="w-full sm:w-auto px-12 py-6 text-lg font-bold">
                        <a href="{{ route('login') }}">Member Login</a>
                    </flux:button>
                </div>

                <div class="mt-16 flex items-center justify-center gap-8 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                    <flux:text class="font-bold text-sm tracking-widest uppercase">Certified by</flux:text>
                    <div class="h-8 w-px bg-zinc-200 dark:border-zinc-800"></div>
                    <flux:badge color="blue" variant="outline" size="sm">LTO Accredited</flux:badge>
                    <flux:badge color="zinc" variant="outline" size="sm">ISO 9001:2015</flux:badge>
                </div>
            </div>
        </flux:container>
    </section>
</div>
