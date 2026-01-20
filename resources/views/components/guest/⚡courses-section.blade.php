<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    {{-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi --}}
    <section class="relative bg-white dark:bg-zinc-950 py-24 lg:py-32">
        <flux:container>
            {{-- Section Header: Matching Hero Alignment --}}
            <div class="max-w-3xl mb-16 lg:mb-20">
                <flux:badge size="sm" color="blue">
                    <flux:icon.book-open class="w-5 h-5 mr-1" />
                    Our courses
                </flux:badge>

                <h1
                    class="mb-8 text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                    Comprehensive <span class="text-accent">training</span> for every driver.
                </h1>

                <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                    Whether you're just starting your journey or looking to master the wheel, our LTO-accredited courses
                    are designed for safety, skill, and success.
                </flux:text>
            </div>

            {{-- Courses Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12  ">

                {{-- Course 1: TDC --}}
                <div class="flex flex-col p-8 lg:p-10 hover:border-blue-500/50 transition-colors duration-300 border rounded-lg">
                    <div class="flex justify-between items-start mb-8">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-accent">
                            <flux:icon name="book-open" variant="outline" class="size-8" />
                        </div>
                        <flux:badge variant="pill">15 Hours</flux:badge>
                    </div>

                    <div class="flex-1">
                        <flux:heading size="xl" class="mb-4">Theoretical Driving Course (TDC)</flux:heading>
                        <flux:text class="mb-8 leading-relaxed">
                            The foundation of your driving journey. This mandatory classroom-based course covers traffic
                            rules, road safety, and defensive driving techniques required for your Student Permit.
                        </flux:text>

                        <div class="space-y-3 mb-10">
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">LTO-Compliant Curriculum</flux:text>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">Smart Classroom Environment</flux:text>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">Certificate of Completion provided</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:button variant="primary" icon-trailing="chevron-right"
                        class="w-full shadow-lg shadow-blue-500/10">
                        Enroll in TDC
                    </flux:button>
                </div>

                {{-- Course 2: PDC --}}
                <div class="flex flex-col p-8 lg:p-10 hover:border-blue-500/50 transition-colors duration-300 border rounded-lg">
                    <div class="flex justify-between items-start mb-8">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-accent">
                            <flux:icon name="identification" variant="outline" class="size-8" />
                        </div>
                        <flux:badge variant="pill">8+ Hours</flux:badge>
                    </div>

                    <div class="flex-1">
                        <flux:heading size="xl" class="mb-4">Practical Driving Course (PDC)</flux:heading>
                        <flux:text class="mb-8 leading-relaxed">
                            Hands-on training with our expert instructors. Master the art of driving in real-world
                            conditions. Required for converting your permit to a Non-Professional License.
                        </flux:text>

                        <div class="space-y-3 mb-10">
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">Manual & Automatic Transmissions</flux:text>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">1-on-1 Personalized Coaching</flux:text>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:icon name="check" variant="mini" class="text-accent" />
                                <flux:text size="sm">Dual-Control Safety Vehicles</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:button variant="primary" icon-trailing="chevron-right"
                        class="w-full shadow-lg shadow-blue-500/10">
                        Enroll in PDC
                    </flux:button>
                </div>

            </div>

            {{-- Footer Note --}}
            <div class="mt-16 text-center">
                <flux:text variant="subtle" class="text-sm">
                    Need a custom package? <flux:link href="#" class="font-bold text-accent">Contact our
                        registrars</flux:link> for bulk enrollment discounts.
                </flux:text>
            </div>
        </flux:container>
    </section>
</div>
