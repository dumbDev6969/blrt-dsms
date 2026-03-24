@props([
    'label' => null,
    'sublabel' => null,
    'value' => null,
    'icon' => null,
    'color' => 'blue', // For icon bg/text
    'trend' => null,
    'trendColor' => 'emerald',
    'subtext' => null,
    'iconPosition' => 'right', // 'right' or 'left'
])

@php
    $colorClasses = match ($color) {
        'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
        'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
        'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
        'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
        default => 'bg-slate-50 text-slate-600 dark:bg-slate-900/20 dark:text-slate-400',
    };
@endphp

<div {{ $attributes->merge(['class' => 'p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm']) }}>
    @if ($iconPosition === 'right')
        <div class="flex items-start justify-between mb-4 gap-4">
            <div class="flex-1 min-w-0">
                @if ($label)
                    <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400 truncate">{{ $label }}</flux:text>
                @endif
                @if ($sublabel)
                    <flux:text size="xs" class="text-slate-400 mt-1 truncate">{{ $sublabel }}</flux:text>
                @endif
            </div>
            @if ($icon)
                <div class="{{ $colorClasses }} p-2 rounded-lg shrink-0">
                    <flux:icon :icon="$icon" class="size-5" />
                </div>
            @endif
        </div>
    @else
        <div class="flex items-center gap-4 mb-4">
            @if ($icon)
                <div class="{{ $colorClasses }} flex items-center justify-center size-10 rounded-lg shrink-0">
                    <flux:icon :icon="$icon" class="size-6" />
                </div>
            @endif
            <div class="min-w-0 flex-1">
                @if ($label)
                    <flux:heading size="sm" weight="semibold" class="truncate">{{ $label }}</flux:heading>
                @endif
                @if ($sublabel)
                    <flux:text size="xs" class="text-slate-500 dark:text-slate-400 truncate">{{ $sublabel }}</flux:text>
                @endif
            </div>
        </div>
    @endif

    @if (isset($value) || $trend)
        <div class="flex items-baseline gap-2">
            @if (isset($value))
                <flux:heading size="xl" class="font-bold">{{ $value }}</flux:heading>
            @endif
            @if ($trend)
        <flux:text :color="$trendColor === 'zinc' ? null : $trendColor" size="xs" weight="medium">{{ $trend }}</flux:text>
    @endif
        </div>
    @endif

    @if ($subtext)
        <flux:text size="xs" class="text-slate-400 mt-1">{{ $subtext }}</flux:text>
    @endif

    @if($slot->isNotEmpty())
        <div @class(['mt-4' => $value || $trend || $subtext])>
            {{ $slot }}
        </div>
    @endif
</div>
