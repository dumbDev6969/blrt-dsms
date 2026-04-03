@props(['analytics'])

@if($analytics)
    {{-- PERFORMANCE ANALYTICS SECTION --}}
    <div class="mb-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg border border-indigo-100 dark:border-indigo-800">
                <flux:icon icon="presentation-chart-line" class="size-5" />
            </div>
            <flux:heading size="xl" weight="bold" class="tracking-tight">PDC Performance Analytics</flux:heading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
            {{-- Score Gauge Card --}}
            <div class="md:col-span-4 p-8 rounded-3xl bg-slate-900 dark:bg-slate-950 text-white border border-slate-800 relative overflow-hidden shadow-xl">
                <div class="absolute -right-10 -bottom-10 size-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div class="relative z-10 flex flex-col items-center text-center h-full justify-center">
                    <flux:text size="xs" class="text-indigo-300 font-black uppercase tracking-widest mb-2">Running Assessment Score</flux:text>
                    <div class="relative size-32 flex items-center justify-center mb-4">
                        <svg class="size-full -rotate-90 transform" viewBox="0 0 100 100">
                            <circle class="text-slate-800" stroke-width="8" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50" />
                            <circle class="text-indigo-500 transition-all duration-1000 ease-out" stroke-width="8" stroke-dasharray="{{ (2 * pi() * 40 * ($analytics['score'] / 100)) }} {{ (2 * pi() * 40) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-black">{{ round($analytics['score']) }}%</span>
                        </div>
                    </div>
                    <flux:badge size="sm" :color="$analytics['score'] >= 75 ? 'emerald' : 'amber'" variant="solid" class="font-bold border-none">
                        {{ $analytics['score'] >= 75 ? 'On Track to Pass' : 'Needs Improvement' }}
                    </flux:badge>
                </div>
            </div>

            {{-- Narrative Insights Card --}}
            <div class="md:col-span-12 lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-6 rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="size-2 bg-indigo-500 rounded-full"></div>
                        <flux:text size="xs" class="text-slate-400 font-bold uppercase tracking-widest">Instructor Insights</flux:text>
                    </div>
                    <flux:heading size="lg" class="text-slate-800 dark:text-white leading-tight font-black mb-4 h-full flex items-center">
                        {{ $analytics['insights']['narrative'] }}
                    </flux:heading>
                    <div class="mt-auto flex flex-wrap gap-2 pt-4">
                        @foreach($analytics['insights']['strengths'] as $strength)
                            <flux:badge size="xs" color="blue" variant="subtle" class="font-bold uppercase tracking-tighter">{{ $strength }}</flux:badge>
                        @endforeach
                    </div>
                </div>

                {{-- Stats Breakdown Grid --}}
                <div class="grid grid-cols-1 gap-4">
                    <div class="p-5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-500 text-white rounded-lg"><flux:icon icon="check" class="size-4" /></div>
                            <flux:text size="sm" class="font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tighter">Good Marks</flux:text>
                        </div>
                        <span class="text-xl font-black text-emerald-600">{{ $analytics['counts']['good'] }}</span>
                    </div>
                    <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-amber-500 text-white rounded-lg"><flux:icon icon="exclamation-triangle" class="size-4" /></div>
                            <flux:text size="sm" class="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-tighter">Needs Work</flux:text>
                        </div>
                        <span class="text-xl font-black text-amber-600">{{ $analytics['counts']['fair'] + $analytics['counts']['poor'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Failure Warnings Alert --}}
        @if(count($analytics['insights']['warnings']) > 0)
            <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 rounded-2xl flex items-start gap-4">
                <flux:icon icon="exclamation-circle" class="size-6 text-red-600 mt-0.5" />
                <div>
                    <flux:heading size="sm" class="text-red-900 dark:text-red-300 font-bold mb-1">Critical Learning Opportunities</flux:heading>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($analytics['insights']['warnings'] as $warning)
                            <li class="text-xs text-red-700 dark:text-red-400 font-medium">{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif
