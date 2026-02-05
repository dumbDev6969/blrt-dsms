<?php

use Livewire\Component;

new class extends Component {
    public function save()
    {
        // Save logic here...
    }
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-12">

        <div class="space-y-6">
            {{-- Section Header --}}
            <div>
                <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                    Manage Course
                </flux:heading>
                <flux:subheading class="mt-2 text-zinc-500 dark:text-zinc-400">
                    Add a new driving course to your curriculum.
                </flux:subheading>
            </div>

            {{-- Form Card --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <form wire:submit.prevent="save" class="p-6 sm:p-8">

                    <div class="space-y-8">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-6">

                            <div class="sm:col-span-4">
                                <flux:input label="Course Title" name="title"
                                    placeholder="e.g. Practical Driving Course" icon="tag" required />
                            </div>

                            <div class="sm:col-span-2">
                                <flux:select label="Type" name="type" placeholder="Select type..."
                                    icon="book-open">
                                    <flux:select.option value="theoretical">Theoretical</flux:select.option>
                                    <flux:select.option value="practical">Practical</flux:select.option>
                                </flux:select>
                            </div>

                        </div>

                        <flux:separator variant="subtle" />

                        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-3">

                            <flux:input type="number" step="0.01" label="Price (₱)" name="price"
                                placeholder="0.00" icon="currency-dollar" required />

                            <flux:input type="number" label="Duration (Hrs)" name="duration_hours" placeholder="15"
                                icon="clock" required />

                            <flux:select label="Prerequisite" name="prerequisites" placeholder="Optional...">
                                <flux:select.option value="1">Practical Driving</flux:select.option>
                                <flux:select.option value="2">Theoretical Driving</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    {{-- Form Footer --}}
                    <div
                        class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="ghost" class="text-zinc-500">Reset</flux:button>
                        <flux:button variant="primary" icon="plus" type="submit">
                            Save Course
                        </flux:button>
                    </div>

                </form>
            </div>
        </div>

        {{-- SECTION 2: TABLE (Directory) --}}
        <div class="space-y-6">

            {{-- Table Header --}}
            <div class="flex items-end justify-between px-1">
                <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">
                    Existing Courses
                </flux:heading>
                <div class="text-xs text-zinc-400">
                    Displaying 3 records
                </div>
            </div>

            {{-- Table Card --}}
            <div
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:text-zinc-400">
                                    Code</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:text-zinc-400">
                                    Course</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:text-zinc-400">
                                    Type</th>
                                <th scope="col"
                                    class="px-6 py-4 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:text-zinc-400">
                                    Duration</th>
                                <th scope="col"
                                    class="px-6 py-4 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:text-zinc-400">
                                    Price</th>
                                <th scope="col" class="relative px-6 py-4"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                            {{-- Row 1 --}}
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-mono text-xs font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">TDC-001</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">Theoretical Driving Course
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Basic traffic rules</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">Theoretical</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-zinc-600 dark:text-zinc-400">
                                    15 hrs</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    ₱ 2,500.00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" tooltip="Actions"/>
                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                            {{-- Row 2 --}}
                            <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-mono text-xs font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">PDC-AUTO</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">Practical Driving (Sedan)
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Automatic trans</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-900/30">Practical</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-zinc-600 dark:text-zinc-400">
                                    8 hrs</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    ₱ 4,000.00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <flux:dropdown>
                                        
                                        <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm"
                                            inset="top bottom" tooltip="Actions" />

                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                            {{-- Row 3 --}}
                            <tr
                                class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-mono text-xs font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">PDC-MOTO</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">Motorcycle Basics</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Unavailable</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-900/30">Practical</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-zinc-600 dark:text-zinc-400">
                                    8 hrs</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    ₱ 1,500.00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" tooltip="Actions"/>
                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="border-t border-zinc-200 bg-zinc-50 px-6 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span
                                class="font-medium">12</span> results
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="subtle" disabled icon="chevron-left" />
                            <flux:button size="sm" variant="subtle" icon="chevron-right" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
