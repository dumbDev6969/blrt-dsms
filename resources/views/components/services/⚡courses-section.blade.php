<?php

use Livewire\Component;
use App\Models\Course;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function courses()
    {
        return Course::where('is_active', true)->get();
    }
};
?>

<div>
    <section class="relative py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-6">
        
        {{-- Section Header --}}
        <div class="max-w-3xl mb-16">
            <h1 class="text-7xl md:text-8xl lg:text-9xl font-black tracking-tight leading-[1.1] text-zinc-900 dark:text-white">
                Core <span class="text-accent">Curriculum</span>
            </h1>
            <flux:text variant="subtle" class="text-lg md:text-xl leading-relaxed text-zinc-600 dark:text-zinc-400">
                The essential steps to getting your license in the Philippines. From theoretical foundations to practical mastery.
            </flux:text>
        </div>

        {{-- Courses Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            @foreach($this->courses as $course)
                <div class="flex flex-col h-full p-8 lg:p-10 border border-zinc-200 dark:border-zinc-800 rounded-2xl hover:border-blue-500/50 transition-all duration-300 bg-white dark:bg-zinc-900/50 shadow-sm">
                    
                    {{-- Card Header --}}
                    <div class="flex justify-between items-start mb-8">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-accent">
                            <flux:icon name="{{ $course->type === 'theoretical' ? 'book-open' : 'truck' }}" variant="outline" class="size-8" />
                        </div>
                        <flux:badge size="sm" color="zinc" variant="pill" class="font-mono">
                            {{ $course->duration_hours }} Hours
                        </flux:badge>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 flex flex-col">
                        <div class="mb-6">
                            <flux:heading size="xl" class="mb-2 font-bold">{{ $course->title }}</flux:heading>
                            <flux:text size="sm" class="text-accent font-medium mb-4 uppercase tracking-widest">{{ $course->code }}</flux:text>
                            <flux:text class="leading-relaxed text-zinc-600 dark:text-zinc-400">
                                {{ $course->description }}
                            </flux:text>
                        </div>

                        {{-- Feature List (Prerequisites as Features) --}}
                        @if($course->prerequisites && count($course->prerequisites) > 0)
                            <div class="space-y-4 mb-10 mt-auto">
                                @foreach($course->prerequisites as $prereq)
                                    <div class="flex items-start gap-3">
                                        <flux:icon.check-circle class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $prereq }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Action --}}
                        <div class="mt-auto">
                             <div class="flex items-center justify-between mb-6">
                                <flux:text size="xs" class="uppercase tracking-widest font-bold text-zinc-400">Fee</flux:text>
                                <flux:text size="lg" weight="bold" class="text-accent">₱{{ number_format($course->price, 2) }}</flux:text>
                            </div>
                            <flux:button href="{{ route('login') }}" variant="primary" class="w-full justify-center shadow-lg shadow-blue-500/10">
                                Enroll in {{ $course->type === 'theoretical' ? 'TDC' : 'PDC' }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
</div>