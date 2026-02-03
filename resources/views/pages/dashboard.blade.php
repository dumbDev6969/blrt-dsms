<x-layouts::app :title="__('Dashboard')">
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
