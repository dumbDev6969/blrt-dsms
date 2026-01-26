<x-layouts::app :title="__('Dashboard')">
    @role('Student')
        <livewire:dashboards.student-dashboard />
    @endrole
</x-layouts::app>
