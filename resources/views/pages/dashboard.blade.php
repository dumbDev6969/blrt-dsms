<x-layouts::app :title="__('Dashboard')">
    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            {{ session('status') }}
            <x-slot name="controls">
        <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
    </x-slot>
        </flux:callout>
    @endif

    @role('Student')
        <livewire:dashboards.student-dashboard />
    @endrole

    @role('Instructor')
        <livewire:dashboards.instructor-dashboard />
    @endrole

    @role('Admin')
        <livewire:dashboards.admin-dashboard />
    @endrole
</x-layouts::app>
                    