<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl font-sans text-slate-900 dark:text-slate-100">

    <x-callout />

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <flux:heading size="xl" class="text-2xl font-bold tracking-tight">Active Enrollments</flux:heading>
            <flux:text>
                Manage ongoing student courses and track their progress.
            </flux:text>
        </div>
    </div>

    {{-- STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Total Records
                </flux:text>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <flux:icon icon="document-text" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['total'] }}</flux:heading>
                <flux:text>total</flux:text>
            </div>
        </div>

        {{-- Active --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Active
                </flux:text>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-900/20 dark:text-emerald-400">
                    <flux:icon icon="check-circle" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['active'] }}</flux:heading>
                <flux:text>running</flux:text>
            </div>
        </div>

        {{-- Pending --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Pending Start
                </flux:text>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg dark:bg-amber-900/20 dark:text-amber-400">
                    <flux:icon icon="clock" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['pending'] }}</flux:heading>
                <flux:text>waiting</flux:text>
            </div>
        </div>

        {{-- Completed --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:text size="sm" weight="medium" class="text-slate-500 dark:text-slate-400">Completed
                </flux:text>
                <div class="p-2 bg-zinc-50 text-zinc-600 rounded-lg dark:bg-zinc-900/20 dark:text-zinc-400">
                    <flux:icon icon="archive-box" class="size-5" />
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ $this->enrollmentCounts['completed'] }}</flux:heading>
                <flux:text>finished</flux:text>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex flex-col gap-5">
        {{-- Filter Tabs & Search --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between p-1 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg w-full gap-2 md:gap-0">
            <div class="flex flex-wrap gap-1 p-1">
                <button wire:click="$set('status', 'all')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'all' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    All ({{ $this->enrollmentCounts['total'] }})
                </button>
                <button wire:click="$set('status', 'active')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'active' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Active ({{ $this->enrollmentCounts['active'] }})
                </button>
                <button wire:click="$set('status', 'pending')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'pending' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Pending ({{ $this->enrollmentCounts['pending'] }})
                </button>
                <button wire:click="$set('status', 'completed')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'completed' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Completed ({{ $this->enrollmentCounts['completed'] }})
                </button>
                <button wire:click="$set('status', 'dropped')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $status === 'dropped' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    Dropped ({{ $this->enrollmentCounts['dropped'] }})
                </button>
            </div>
            <div class="pr-1 w-full md:w-72">
                <x-live-search placeholder="Search code or name..." />
            </div>
        </div>

        {{-- Enrollment Table Container --}}
        <div
            class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm overflow-hidden">
            <div
                class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <flux:heading size="xl" level="2">Enrollment List</flux:heading>
                    <flux:text size="sm" class="mt-1">View and manage all active and archived student
                        enrollments.
                    </flux:text>
                </div>
                <flux:badge color="blue" variant="subtle" size="sm">
                    {{ $this->enrollments->total() }} Records
                </flux:badge>
            </div>

            <div class="relative overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Enrollment Code</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Student</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Course</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Instructor</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Start Date</th>
                            <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                        @forelse ($this->enrollments as $enrollment)
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-4">
                                    <flux:text weight="bold" class="font-mono text-blue-600 dark:text-blue-400">
                                        {{ $enrollment->code }}
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col">
                                            <flux:text weight="medium" class="text-zinc-900 dark:text-white">
                                                {{ $enrollment->studentProfile->user->name ?? 'N/A' }}</flux:text>
                                            <flux:text size="xs" class="text-zinc-500">
                                                {{ $enrollment->studentProfile->user->email ?? '' }}</flux:text>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm">{{ $enrollment->course->title ?? 'N/A' }}
                                        </flux:text>
                                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-tighter">
                                            {{ $enrollment->course->type ?? '' }}</flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:text size="sm" weight="medium">
                                        {{ $enrollment->instructorProfile->user->name ?? 'Unassigned' }}
                                    </flux:text>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['color' => 'amber', 'label' => 'Pending'],
                                            'active' => ['color' => 'emerald', 'label' => 'Active'],
                                            'completed' => ['color' => 'blue', 'label' => 'Completed'],
                                            'dropped' => ['color' => 'red', 'label' => 'Dropped'],
                                        ];
                                        $config = $statusConfig[$enrollment->status] ?? [
                                            'color' => 'zinc',
                                            'label' => $enrollment->status,
                                        ];
                                    @endphp
                                    <flux:badge :color="$config['color']" variant="subtle" size="sm"
                                        class="capitalize">
                                        {{ $config['label'] }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <flux:text size="sm">
                                            {{ $enrollment->start_date ? $enrollment->start_date->format('M d') : 'Not Set' }}
                                        </flux:text>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            <flux:menu.item icon="eye"
                                                href="{{ route('staff.approved-enrollment.show', $enrollment->id) }}"
                                                wire:navigate>View Details</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $enrollment->id }})">Edit Details</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state 
                                variant="table" 
                                :colspan="7"
                                icon="document-text"
                                heading="No enrollments found"
                                message="Try adjusting your filters or search criteria."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($this->enrollments->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    {{ $this->enrollments->links() }}
                </div>
            @endif
        </div>
    </div>
    {{-- Edit Enrollment Modal --}}
    <flux:modal name="edit-enrollment-modal" class="md:w-96">
        <form wire:submit.prevent="saveEnrollment" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Enrollment</flux:heading>
                <flux:text size="sm" class="mt-1">Update the instructor assignment and start date for this enrollment.</flux:text>
            </div>

            <flux:select wire:model="editInstructorId" label="Assigned Instructor" placeholder="Select an instructor...">
                @foreach ($this->instructors as $instructor)
                    <flux:select.option value="{{ $instructor->id }}">
                        {{ $instructor->user->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="editStartDate" type="date" label="Start Date" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>