<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    {{-- Well begun is half done. - Aristotle --}}
    <section class="relative  py-24 lg:py-32">
        <flux:container>
            {{-- Section Header --}}
            <div class="mb-20">
                <flux:badge size="sm" color="blue" class="mb-6">
                    <flux:icon.star class="w-5 h-5 mr-1" />
                    The BLRT Advantage
                </flux:badge>

                <h2
                    class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white max-w-5xl">
                    The standard for <span class="text-accent">modern</span> driver education.
                </h2>
            </div>

            {{-- Features Grid --}}
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-zinc-200 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-3xl overflow-hidden">

                {{-- Feature 1: Modern Fleet --}}
                <div
                    class="bg-white dark:bg-zinc-950 p-8 lg:p-10 flex flex-col gap-6 group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl w-fit text-accent">
                        <flux:icon name="truck" variant="outline" class="size-8" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="mb-3">Versatile Fleet</flux:heading>
                        <flux:text variant="subtle" class="leading-relaxed">
                            Train on Sedans (Manual & Automatic), Tricycles, or Motorcycles. All vehicles are modern and maintained for safety.
                        </flux:text>
                    </div>
                </div>

                {{-- Feature 2: Flexible Sched --}}
                <div
                    class="bg-white dark:bg-zinc-950 p-8 lg:p-10 flex flex-col gap-6 group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl w-fit text-accent">
                        <flux:icon name="calendar" variant="outline" class="size-8" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="mb-3">Flexible Scheduling</flux:heading>
                        <flux:text variant="subtle" class="leading-relaxed">
                            We respect your time. Book your sessions on weekends or evenings to fit your busy work or
                            school lifestyle.
                        </flux:text>
                    </div>
                </div>

                {{-- Feature 3: Success Rate --}}
                <div
                    class="bg-white dark:bg-zinc-950 p-8 lg:p-10 flex flex-col gap-6 group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl w-fit text-accent">
                        <flux:icon name="academic-cap" variant="outline" class="size-8" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="mb-3">Expert Mentorship</flux:heading>
                        <flux:text variant="subtle" class="leading-relaxed">
                            Our instructors are DOTR-LTO accredited professionals dedicated to teaching road discipline and safe driving practices.
                        </flux:text>
                    </div>
                </div>

                {{-- Feature 4: LTO Compliance --}}
                <div
                    class="bg-white dark:bg-zinc-950 p-8 lg:p-10 flex flex-col gap-6 group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl w-fit text-accent">
                        <flux:icon name="shield-check" variant="outline" class="size-8" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="mb-3">Full LTO Compliance</flux:heading>
                        <flux:text variant="subtle" class="leading-relaxed">
                            Skip the stress. We handle the direct uploading of your certificates to the LTO portal for a
                            seamless license application.
                        </flux:text>
                    </div>
                </div>

            </div>

            {{-- Bottom CTA Context --}}
            <div
                class="mt-20 bg-gradient-to-br from-accent/10 to-transparent dark:from-accent/20 flex flex-col md:flex-row items-center justify-between gap-8 border border-zinc-100 dark:border-zinc-800 p-12 rounded-3xl">
                <div class="max-w-xl">
                    <flux:heading size="xl" class="mb-2">Ready to start your journey?</flux:heading>
                    <flux:text variant="subtle">Join over 15,000+ successful drivers who started their journey with BLRT
                        Academy.</flux:text>
                </div>
                <div class="flex gap-4">
                    <flux:button variant="primary" icon-trailing="arrow-right">
                        <a href="{{ route('login') }}">
                            Get Started Today
                        </a>
                    </flux:button>
                </div>
            </div>
        </flux:container>
    </section>
</div>
