<x-layouts::app :title="__('Dashboard')">
    <x-callout />

    @role('Student')
        <livewire:dashboards.student-dashboard />
    @endrole

    @role('Instructor')
        <livewire:dashboards.instructor-dashboard />
    @endrole

    @role('Admin')
        <livewire:dashboards.admin-dashboard />
    @endrole

    @role('Staff')
        <livewire:dashboards.staff-dashboard />
    @endrole
</x-layouts::app>
                    