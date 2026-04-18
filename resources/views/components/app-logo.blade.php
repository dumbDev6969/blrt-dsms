@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="BLRT Driving School INC." {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-12 items-center justify-center rounded-md">
            <x-app-logo-icon class="size-12" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="BLRT Driving School INC." {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-12 items-center justify-center rounded-md">
            <x-app-logo-icon class="size-12" />
        </x-slot>
    </flux:brand>
@endif
