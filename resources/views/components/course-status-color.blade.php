@props(['status'])

@php
    $config = match ($status) {
        'in_progress' => ['color' => 'blue', 'icon' => 'arrow-path'],
        'completed' => ['color' => 'emerald', 'icon' => 'check-circle'],
        default => ['color' => 'zinc', 'icon' => 'clock'],
    };
@endphp

<flux:badge :color="$config['color']" variant="subtle" size="xs" {{ $attributes->merge(['class' => 'shrink-0']) }}>
    <flux:icon :icon="$config['icon']" class="size-3 mr-1" />
    {{ str_replace('_', ' ', $status) }}
</flux:badge>
