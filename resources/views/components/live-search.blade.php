@props(['model' => 'search', 'placeholder' => 'Search...'])

<flux:input 
    {{ $attributes->merge(['icon' => 'magnifying-glass', 'placeholder' => $placeholder]) }}
    wire:model.live.debounce.500ms="{{ $model }}" 
/>
