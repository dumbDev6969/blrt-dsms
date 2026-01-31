<x-layouts::app :title="__('Dashboard')">
    @role('Student')
        <livewire:dashboards.student-dashboard />
    @endrole

    @role('Instructor')
        <livewire:dashboards.instructor-dashboard />
    @endrole
</x-layouts::app>
