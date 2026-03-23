@php
    $message = session('status') ?? session('success') ?? session('error');
    $variant = session('error') ? 'danger' : 'success';
    $icon = session('error') ? 'exclamation-circle' : 'check-circle';
@endphp

@if ($message)
    <flux:callout :icon="$icon" :variant="$variant" class="shadow-sm fixed top-5 w-5xl z-10 left-1/2 -translate-x-1/2" 
        x-data="{ visible: true }"
        x-show="visible" 
        x-init="setTimeout(() => visible = false, 5000)"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <flux:callout.heading>{{ $message }}</flux:callout.heading>
        <x-slot name="controls">
            <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
        </x-slot>
    </flux:callout>
@endif