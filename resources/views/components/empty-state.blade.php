@props([
    'variant' => 'card', // 'table', 'card', 'page'
    'icon' => 'information-circle',
    'heading' => 'No data found',
    'message' => null,
    'actionUrl' => null,
    'actionLabel' => null,
    'wireAction' => null,
    'wireLabel' => null,
    'colspan' => 5,
])

@php
    $commonClasses = 'flex flex-col items-center justify-center text-center';
    
    $wrapperClasses = match ($variant) {
        'page' => $commonClasses . ' py-20 px-4 h-full min-h-[50vh]',
        'card' => $commonClasses . ' py-16 px-4 bg-zinc-50 dark:bg-zinc-900/30 rounded-[2rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800',
        'table' => $commonClasses . ' max-w-sm mx-auto',
        default => $commonClasses,
    };
    
    $iconSize = match ($variant) {
        'page' => 'size-16',
        'card' => 'size-12',
        'table' => 'size-10',
        default => 'size-10',
    };
    
    $headingSize = match ($variant) {
        'page' => 'xl',
        'card' => 'lg',
        'table' => 'md',
        default => 'lg',
    };
@endphp

@if ($variant === 'table')
    <tr>
        <td colspan="{{ $colspan }}" class="py-12 text-center text-zinc-500">
@endif

<div class="{{ $wrapperClasses }}">
    <flux:icon :icon="$icon" variant="outline" class="{{ $iconSize }} text-zinc-300 dark:text-zinc-600 mb-4" />
    
    <flux:heading size="{{ $headingSize }}" weight="bold" class="mb-2 text-zinc-800 dark:text-zinc-200">
        {{ $heading }}
    </flux:heading>
    
    @if ($message)
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 max-w-md">{{ $message }}</flux:text>
    @endif
    
    @if ($actionUrl && $actionLabel)
        <div class="mt-6">
            <flux:button :href="$actionUrl" variant="primary" wire:navigate>{{ $actionLabel }}</flux:button>
        </div>
    @endif

    @if ($wireAction && $wireLabel)
        <div class="mt-6">
            <flux:button wire:click="{{ $wireAction }}" variant="ghost">{{ $wireLabel }}</flux:button>
        </div>
    @endif
</div>

@if ($variant === 'table')
        </td>
    </tr>
@endif
