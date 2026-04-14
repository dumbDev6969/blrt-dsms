@props([
    'isComplete' => false,
    'isEnrollBlocked' => false,
    'enrollBlockReason' => null,
    'hasCompletedTdc' => false,
])

{{-- Be present above all else. - Naval Ravikant --}}
<div class="mb-6">
    <flux:heading size="lg">Available Courses</flux:heading>
    <flux:text>Select a program to start your driving journey.</flux:text>

    @if (!$isComplete)
        <flux:text color="red" class="mt-2 font-semibold italic">Submit your documents before you can enroll.
        </flux:text>
    @elseif ($isEnrollBlocked)
        <flux:text color="amber" class="mt-2 font-semibold italic">
            {{ $enrollBlockReason ?? 'You cannot enroll right now because you already have an ongoing or pending enrollment.' }}
        </flux:text>
    @endif
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($this->courses as $course)
        <div
            class="group relative rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 hover:border-[var(--color-accent)] dark:hover:border-[var(--color-accent)] transition-all duration-300">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                        <flux:icon icon="book-open" class="size-6 text-[var(--color-accent)]" />
                    </div>
                    @if (str_contains($course->title, 'Theoritical'))
                        <flux:badge color="blue" variant="subtle" size="sm" class="rounded-full">
                            Recommended
                        </flux:badge>
                    @endif
                </div>
                <flux:heading size="sm" class="font-bold text-slate-900 dark:text-slate-100">{{ $course->title }}</flux:heading>
                <flux:text size="sm" class="mt-2 line-clamp-2 text-slate-500">{{ $course->description }}</flux:text>

                <div class="mt-6 flex items-center justify-between">
                    <flux:text size="lg" weight="bold" class="text-slate-900 dark:text-slate-100">{{ $course->price }}</flux:text>
                    
                    @php
                        // Determine if this is a practical course and the user hasn't completed TDC yet
                        $isPdcBlock = $course->type === 'practical' && !$hasCompletedTdc;
                    @endphp

                    @if ($isEnrollBlocked)
                        <flux:tooltip :content="$enrollBlockReason ?? 'Your enrollment is currently locked.'">
                            <span>
                                <flux:button
                                    variant="primary"
                                    size="sm"
                                    icon-trailing="arrow-right"
                                    disabled
                                >
                                    Enroll
                                </flux:button>
                            </span>
                        </flux:tooltip>
                    @elseif ($isPdcBlock)
                        <flux:tooltip content="You must complete a Theoretical Driving Course (TDC) before enrolling in a Practical course.">
                            <span>
                                <flux:button
                                    variant="primary"
                                    size="sm"
                                    icon-trailing="arrow-right"
                                    disabled
                                >
                                    Enroll
                                </flux:button>
                            </span>
                        </flux:tooltip>
                    @else
                        <flux:button
                            variant="primary"
                            size="sm"
                            icon-trailing="arrow-right"
                            :disabled="!$isComplete"
                            :href="$isComplete ? route('enrollment.create', $course->id) : null"
                        >
                            Enroll
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
