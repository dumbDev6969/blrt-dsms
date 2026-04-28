<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <section class="relative py-24 lg:py-32">
        <flux:container>
            <div class="max-w-4xl mb-20">
                <flux:badge size="sm" color="blue" class="mb-6">
                    <flux:icon.user-group class="w-5 h-5 mr-1" />
                    Our Story
                </flux:badge>

                <div class="space-y-6">
                    <flux:text variant="subtle" class="text-xl md:text-2xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Established in March 2021, <span class="text-zinc-900 dark:text-white font-bold">BLRT Driving School Inc.</span> (Brotherly Love Relief & Truth) began its journey with a commitment to teaching the safest way to drive. Located in San Carlos City, Pangasinan, we are the only and most successful driving academy in the region.
                    </flux:text>

                    <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Our academy provides comprehensive training courses for various vehicle categories. With a relentless dedication to road safety, we transform students into disciplined and responsible drivers, ensuring they master road traffic rules, defensive driving, and safety measures.
                    </flux:text>
                </div>
            </div>

            {{-- Mission, Vision, Policy Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Mission --}}
                <div class="p-8 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-2xl w-fit text-accent mb-6">
                        <flux:icon name="rocket-launch" variant="outline" class="size-8" />
                    </div>
                    <flux:heading size="lg" class="mb-4">Our Mission</flux:heading>
                    <flux:text variant="subtle" class="leading-relaxed italic">
                        "We strive to improve & enhance safety on road by providing motor driving training service that complies with the highest standards & best practice."
                    </flux:text>
                </div>

                {{-- Vision --}}
                <div class="p-8 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-2xl w-fit text-accent mb-6">
                        <flux:icon name="eye" variant="outline" class="size-8" />
                    </div>
                    <flux:heading size="lg" class="mb-4">Our Vision</flux:heading>
                    <flux:text variant="subtle" class="leading-relaxed">
                        To build on our reputation as the leading learner and driver training organization in the region and to remain at the forefront of road safety awareness.
                    </flux:text>
                </div>

                {{-- Policy --}}
                <div class="p-8 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-2xl w-fit text-accent mb-6">
                        <flux:icon name="shield-check" variant="outline" class="size-8" />
                    </div>
                    <flux:heading size="lg" class="mb-4">Safety Policy</flux:heading>
                    <flux:text variant="subtle" class="leading-relaxed">
                        Delivering efficient training under LTO guidance to ensure customers become safe, disciplined drivers equipped to obtain their license in their desired category.
                    </flux:text>
                </div>
            </div>

        </flux:container>
    </section>
</div>