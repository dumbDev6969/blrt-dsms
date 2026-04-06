@props([
    'courseTitle',
    'courseCode',
    'courseType' => 'TDC',
    'avgRating',
    'totalReviews',
    'avgCriteria',
    'performances',
    'lastEvaluationDate' => null,
    'trend' => 0,
    'ratingDistribution' => [],
    'topStrengths' => [],
    'topImprovements' => [],
])

<div x-data="{ open: false }" class="relative group flex flex-col p-6 md:p-8 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden transition-all hover:shadow-lg duration-300">
    {{-- Decorative Background Glow --}}
    <div class="absolute -right-20 -top-20 size-64 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/15 transition-colors duration-500"></div>

    <div class="relative z-10 flex-1 flex flex-col">
        {{-- Header: Course Info & Primary Score --}}
        <div class="flex items-start justify-between gap-4 mb-8">
            <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-1">
                    <flux:badge size="sm" color="emerald" variant="subtle" class="font-bold tracking-widest uppercase">{{ $courseType }}</flux:badge>
                    <flux:text size="xs" class="font-mono text-slate-400">{{ $courseCode }}</flux:text>
                </div>
                <flux:heading size="xl" class="font-black tracking-tight text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 transition-colors">
                    {{ $courseTitle }}
                </flux:heading>
                <flux:text size="xs" class="text-slate-400 mt-2 font-bold uppercase tracking-widest">{{ $totalReviews }} Total Reviews</flux:text>
            </div>
            
            {{-- Course Rating Summary & Trend --}}
            <div class="flex flex-col items-end shrink-0">
                <div class="flex flex-col items-center gap-1">
                    <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/30 px-5 py-3 rounded-2xl border border-amber-200 dark:border-amber-800 shadow-sm relative">
                        {{-- Trend Indicator --}}
                        @if($trend != 0)
                            <div class="absolute -top-3 -right-3 flex items-center justify-center p-1.5 rounded-full {{ $trend > 0 ? 'bg-emerald-100 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/50 dark:text-emerald-400 dark:border-emerald-800' : 'bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-900/50 dark:text-rose-400 dark:border-rose-800' }} shadow-sm">
                                <flux:icon icon="{{ $trend > 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-3" stroke-width="3" />
                                <flux:text size="min" weight="black" class="tracking-tighter ml-0.5 text-current">{{ $trend > 0 ? '+' : '' }}{{ number_format($trend, 1) }}</flux:text>
                            </div>
                        @endif

                        <flux:text size="3xl" weight="black" class="text-amber-700 dark:text-amber-400 tracking-tighter">{{ number_format($avgRating, 1) }}</flux:text>
                        <div class="flex flex-col leading-none">
                            <div class="flex text-amber-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <flux:icon icon="star" variant="{{ $i < round($avgRating) ? 'solid' : 'outline' }}" class="size-4 {{ $i < round($avgRating) ? 'fill-current' : '' }}" />
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deep Analytics Section (Distribution & Breakdown) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 border-t border-slate-100 dark:border-slate-800/60 pt-8">
            
            {{-- Rating Distribution (Amazon Style) --}}
            @if(count($ratingDistribution) > 0)
            <div class="flex flex-col gap-1.5">
                <flux:text size="xs" weight="bold" class="text-slate-400 uppercase tracking-widest mb-1">Score Distribution</flux:text>
                @foreach(range(5, 1) as $star)
                    @php
                        $count = $ratingDistribution[$star] ?? 0;
                        $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-2 group/bar">
                        <div class="flex items-center gap-0.5 w-6 shrink-0">
                            <flux:text size="xs" weight="bold" class="text-slate-500">{{ $star }}</flux:text>
                            <flux:icon icon="star" variant="solid" class="size-2.5 text-amber-400 fill-amber-400" />
                        </div>
                        <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-amber-400 rounded-full transition-all duration-1000 ease-out" 
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                        <flux:text size="min" weight="bold" class="text-slate-400 w-4 text-right">{{ $count }}</flux:text>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Analytical Breakdown Grid --}}
            <div class="flex flex-col gap-3">
                <flux:text size="xs" weight="bold" class="text-slate-400 uppercase tracking-widest mb-0.5">Criteria Average</flux:text>
                @foreach(['teaching_quality' => 'Teaching', 'communication' => 'Communication', 'punctuality' => 'Punctual', 'professionalism' => 'Professional'] as $key => $label)
                    <div class="space-y-1">
                        <div class="flex justify-between items-center px-0.5">
                            <flux:text size="min" weight="bold" class="text-slate-500 uppercase tracking-widest">{{ $label }}</flux:text>
                            <flux:text size="xs" weight="black" class="text-slate-900 dark:text-white">{{ $avgCriteria[$key] }}</flux:text>
                        </div>
                        <div class="relative h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div 
                                class="absolute top-0 left-0 h-full bg-emerald-500 rounded-full transition-all duration-1000 ease-out" 
                                style="width: {{ ($avgCriteria[$key] / 5) * 100 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Feedback Themes (Extracted Keywords) --}}
        @if(count($topStrengths) > 0 || count($topImprovements) > 0)
            <div class="mb-8 border-t border-slate-100 dark:border-slate-800/60 pt-6">
                <flux:text size="xs" weight="bold" class="text-slate-400 uppercase tracking-widest mb-3">Extracted Themes</flux:text>
                <div class="flex flex-col gap-3">
                    @if(count($topStrengths) > 0)
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:icon icon="hand-thumb-up" class="size-4 text-emerald-500" />
                            @foreach($topStrengths as $theme)
                                <flux:badge size="sm" color="emerald" variant="subtle" class="font-bold">{{ ucwords($theme) }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    @if(count($topImprovements) > 0)
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:icon icon="wrench" class="size-4 text-rose-500" />
                            @foreach($topImprovements as $theme)
                                <flux:badge size="sm" color="rose" variant="subtle" class="font-bold">{{ ucwords($theme) }}</flux:badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Collapsible Student Feedbacks --}}
        <div class="mt-auto border-t border-slate-100 dark:border-slate-800/60 pt-6">
            <flux:button 
                @click="open = !open" 
                variant="ghost" 
                size="sm" 
                class="w-full flex justify-between items-center group/btn font-bold text-slate-600 dark:text-slate-400"
            >
                <flux:text size="sm" weight="bold" class="text-current">Recent Feedback ({{ $performances->count() }})</flux:text>
                <flux:icon icon="chevron-down" class="size-4 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''" />
            </flux:button>

            <div x-show="open" x-collapse x-cloak class="mt-4 space-y-4">
                @foreach($performances->take(5) as $performance)
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex flex-col gap-2">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                <div class="size-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                    <flux:text size="min" weight="black" class="text-slate-500">?</flux:text>
                                </div>
                                <flux:text size="xs" weight="bold" class="text-slate-600 dark:text-slate-300 italic">Anonymous Student</flux:text>
                            </div>
                            <flux:text size="min" class="text-slate-400 font-mono">{{ $performance->evaluation_date->format('M d, Y') }}</flux:text>
                        </div>
                        
                        @if($performance->feedback_comment)
                            <flux:text size="sm" class="text-slate-700 dark:text-slate-300 italic leading-relaxed pl-2 border-l-2 border-emerald-500/30">
                                "{{ Str::limit($performance->feedback_comment, 120) }}"
                            </flux:text>
                        @endif

                        <div class="flex gap-2">
                            @if($performance->areas_of_strength)
                                <flux:badge size="xs" color="emerald" variant="subtle" class="font-bold uppercase tracking-tighter">Strength</flux:badge>
                            @endif
                            @if($performance->areas_for_improvement)
                                <flux:badge size="xs" color="rose" variant="subtle" class="font-bold uppercase tracking-tighter">Improvement</flux:badge>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                @if($performances->count() > 5)
                    <flux:text size="xs" class="text-center text-slate-400 italic block pt-2">And {{ $performances->count() - 5 }} more reviews...</flux:text>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:icon icon="calendar" class="size-3 text-slate-400" />
                <flux:text size="min" class="text-slate-400 uppercase tracking-tighter font-black">Last Review: {{ $lastEvaluationDate ? $lastEvaluationDate->format('M Y') : 'N/A' }}</flux:text>
            </div>
            <flux:badge size="sm" color="zinc" variant="subtle" class="font-bold tracking-widest">{{ $performances->count() }} TOTAL</flux:badge>
        </div>
    </div>
</div>

