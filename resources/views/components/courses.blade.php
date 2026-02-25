@props(['isComplete' => false])

{{-- Be present above all else. - Naval Ravikant --}}
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Available Courses</h2>
    <p class="text-sm text-slate-500">Select a program to start your driving journey.</p>
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
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Recommended
                        </span>
                    @endif

                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $course->title }}
                </h3>
                <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $course->description }}</p>

                <div class="mt-6 flex items-center justify-between">
                    <span class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $course->price }}</span>
                    <flux:button variant="primary" size="sm" icon="arrow-right" :disabled="!$isComplete">
                        <a href="{{ route('enrollment.create', $course->id) }}">Enroll</a>
                    </flux:button>
                </div>
            </div>
        </div>
    @endforeach
</div>



