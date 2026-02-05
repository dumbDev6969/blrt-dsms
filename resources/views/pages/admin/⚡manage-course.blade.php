<?php

use Livewire\Component;
use App\Models\Course;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
new class extends Component {
    // UPDATED: Title validation
    #[Validate('required|string|min:3|max:100')]
    public $title = '';

    // NEW: Added Description
    #[Validate('nullable|string|max:1000')]
    public $description = '';

    // UPDATED: logical validation for numbers
    #[Validate('required|numeric|min:0')]
    public $price = '';

    #[Validate('required|integer|min:1')]
    public $duration_hours = '';

    #[Validate('required|string')]
    public $type = '';

    #[Validate('array')]
    public $prerequisites = [];

    public function save()
    {
        $validated = $this->validate();

        Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration_hours' => $validated['duration_hours'],
            'type' => $validated['type'],
            'prerequisites' => $validated['prerequisites'],
        ]);

        session()->flash('status', 'Course added successfully.');
        $this->reset();
    }

    // Get the course
    #[Computed]
    public function courses()
    {
        return Course::query()->select('id', 'title', 'code', 'description', 'price', 'duration_hours')->orderBy('title')->get();
    }
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-12">

        {{-- Callout Alert --}}
        @if (session('status'))
            <flux:callout icon="check-circle" variant="success" class="shadow-sm">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif
        <div class="lg:col-span-1 space-y-2">
            <flux:heading size="xl" level="1">Create Course</flux:heading>
            <flux:subheading>
                Define the curriculum details, pricing, and requirements for a new driving module.
            </flux:subheading>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 ">

            {{-- LEFT COLUMN: Context --}}


            {{-- RIGHT COLUMN: Form --}}
            <div class="lg:col-span-3 w-full">
                <div
                    class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                    <form wire:submit.prevent="save" class="p-6 md:p-8 space-y-6">

                        {{-- Top Row: Title & Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <flux:input label="Course Title" wire:model.blur="title"
                                    placeholder="e.g. Theoretical Driving Course" icon="academic-cap" required />
                            </div>
                            <div>
                                <flux:select label="Type" wire:model.blur="type" placeholder="Select..."
                                    icon="tag">
                                    <flux:select.option value="theoretical">Theoretical</flux:select.option>
                                    <flux:select.option value="practical">Practical</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        {{-- Middle Row: Description --}}
                        <div>
                            <flux:textarea label="Description" wire:model.blur="description"
                                placeholder="Briefly describe what the student will learn..." rows="3" />
                        </div>

                        <flux:separator variant="subtle" />

                        {{-- Bottom Row: Metrics --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <flux:input type="number" step="0.01" label="Price (PHP)" wire:model.blur="price"
                                placeholder="0.00" icon="currency-dollar" />

                            <flux:input type="number" label="Duration (Hours)" wire:model.blur="duration_hours"
                                placeholder="15" icon="clock" />

                            <flux:select label="Prerequisites" wire:model.blur="prerequisites"
                                placeholder="Select courses...">

                                
                                @foreach ($this->courses as $course)
                                    <flux:select.option value="{{ $course->id }}">
                                        {{ $course->title }} <span
                                            class="text-zinc-400 text-xs">({{ $course->code }})</span>
                                    </flux:select.option>
                                @endforeach

                            </flux:select>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <flux:button variant="subtle" wire:click="$refresh">Cancel</flux:button>
                            <flux:button variant="primary" type="submit">Save Course</flux:button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- SECTION 2: TABLE (Directory) --}}
        <div class="space-y-6">

            {{-- Table Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Course Directory</flux:heading>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        Manage your existing curriculum and pricing.
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <flux:input icon="magnifying-glass" placeholder="Search courses..." variant="filled" />
                </div>
            </div>

            {{-- Custom Styled Table --}}
            <div
                class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Code</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Course Details</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Price</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white text-right">Duration
                                </th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                            @forelse ($this->courses as $course)
                                <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                            {{ $course->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $course->title }}</span>
                                            <span
                                                class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 max-w-[200px] truncate">
                                                {{ $course->description }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $course->price }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            <flux:icon name="clock" class="size-3 text-zinc-400" />
                                            {{ $course->duration_hours }} hours
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu>
                                                <flux:menu.item icon="pencil-square">Edit Details</flux:menu.item>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @empty
                            @endforelse





                        </tbody>
                    </table>
                </div>

                {{-- Table Footer / Pagination --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500">
                        Showing 1-2 of 12 courses
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" icon="chevron-left" variant="subtle" disabled />
                        <flux:button size="sm" icon="chevron-right" variant="subtle" />
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
