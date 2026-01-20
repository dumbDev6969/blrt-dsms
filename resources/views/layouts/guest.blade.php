<x-layouts::app.header :title="$title ?? null">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -mt-20 overflow-hidden blur-3xl opacity-20 z-0 pointer-events-none">
            <div class="aspect-square h-[600px] bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full"></div>
        </div>

    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- <flux:footer>
        <livewire:footer />
    </flux:footer> --}}

</x-layouts::app.header>