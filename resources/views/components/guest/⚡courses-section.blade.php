<?php

use Livewire\Component;
use App\Models\Course;
use Livewire\Attributes\Computed;

new class extends Component {
    #[Computed]
    public function courses()
    {
        return Course::where('is_active', true)->get();
    }
};
?>

<div>
    <section class="relative py-24 lg:py-32">
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                @foreach($this->courses as $course)
                    <div class="flex flex-col p-8 lg:p-10 hover:border-blue-500/50 transition-colors duration-300 border rounded-2xl bg-white dark:bg-zinc-900/50 shadow-sm">
                        <div class="flex justify-between items-start mb-8">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-accent">
                                <flux:icon name="{{ $course->type === 'theoretical' ? 'book-open' : 'identification' }}" variant="outline" class="size-8" />
                            </div>
                            <flux:badge variant="pill" color="zinc" size="sm" class="font-mono">{{ $course->duration_hours }} Hours</flux:badge>
                        </div>

                        <div class="flex-1">
                            <flux:heading size="xl" class="mb-2">{{ $course->title }}</flux:heading>
                            <flux:text size="sm" class="text-accent font-medium mb-4 uppercase tracking-widest">{{ $course->code }}</flux:text>
                            
                            <flux:text class="mb-8 leading-relaxed text-zinc-600 dark:text-zinc-400">
                                {{ $course->description }}
                            </flux:text>

                            @if($course->prerequisites && count($course->prerequisites) > 0)
                                <div class="space-y-3 mb-10">
                                    @foreach($course->prerequisites as $prereq)
                                        <div class="flex items-center gap-3">
                                            <flux:icon name="check" variant="mini" class="text-accent" />
                                            <flux:text size="sm">{{ $prereq }}</flux:text>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mt-auto pt-8 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between mb-6">
                                <flux:text size="xs" class="uppercase tracking-widest font-bold text-zinc-400">Course Fee</flux:text>
                                <flux:text size="lg" weight="bold" class="text-accent">₱{{ number_format($course->price, 2) }}</flux:text>
                            </div>
                            
                            <flux:button href="{{ route('login') }}" variant="primary" icon-trailing="chevron-right"
                                class="w-full shadow-lg shadow-blue-500/10">
                                Enroll Now
                            </flux:button>
                        </div>
                    </div>
                @endforeach
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
