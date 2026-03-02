<?php

use Livewire\Component;
use App\Models\Vehicle;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public $search = '';
    #[Validate('required|string|max:50')]
    public $model = '';

    #[Validate('required|string|max:20|unique:vehicles,plate_number')]
    public $plate_number = '';

    #[Validate('required|in:auto,manual')]
    public $transmission = '';

    #[Validate('required|in:2-wheel,4-wheel')]
    public $type = '';

    #[Validate('required|in:available,maintenance,in-use')]
    public $status = 'available';

    #[Validate('nullable|date')]
    public $next_maintenance_date = '';

    // Get the vehicles
    #[Computed]
    public function vehicles()
    {
        return Vehicle::query()
            ->when($this->search, function ($query) {
                $query->where('model', 'like', '%' . $this->search . '%')->orWhere('plate_number', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();
    }

    public function delete(vehicle $vehicle)
    {
        $vehicle->delete();
        session()->flash('status', 'Vehicle deleted successfully.');
    }
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-12">

        {{-- Callout Alert --}}
        @if (session('status'))
            <flux:callout icon="check-circle" variant="success" class="shadow-sm fixed top-5 w-5xl z-10"
                x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @endif

        <div class="lg:col-span-1 space-y-2" id="create-vehicle-form">
            <flux:heading size="xl" level="1">Manage Vehicles</flux:heading>
            <flux:subheading>
                Register and track the university's driving school fleet.
            </flux:subheading>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 ">
            {{-- Form Column --}}
            <div class="lg:col-span-3 w-full">
                <div
                    class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                    <form class="p-6 md:p-8 space-y-6">

                        {{-- Row 1: Model & Plate Number --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:input label="Vehicle Model" wire:model.blur="model" placeholder="e.g. Toyota Vios"
                                icon="truck" required />
                            <flux:input label="Plate Number" wire:model.blur="plate_number" placeholder="ABC 1234"
                                icon="hashtag" required />
                        </div>

                        {{-- Row 2: Transmission & Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:select label="Transmission" wire:model.blur="transmission" placeholder="Select..."
                                icon="adjustments-horizontal">
                                <flux:select.option value="auto">Automatic</flux:select.option>
                                <flux:select.option value="manual">Manual</flux:select.option>
                            </flux:select>
                            <flux:select label="Vehicle Type" wire:model.blur="type" placeholder="Select..."
                                icon="tag">
                                <flux:select.option value="2-wheel">2-Wheel (Motorcycle)</flux:select.option>
                                <flux:select.option value="4-wheel">4-Wheel (Car/Van)</flux:select.option>
                            </flux:select>
                        </div>
                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <flux:button variant="subtle" wire:click="$refresh">Cancel</flux:button>
                            <flux:button variant="primary" type="submit">Save Vehicle</flux:button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- SECTION 2: TABLE (Directory) --}}
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Vehicle Inventory</flux:heading>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        View and manage all registered vehicles.
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <flux:input icon="magnifying-glass" placeholder="Search vehicles..." variant="filled"
                        wire:model.live.debounce.500ms="search" />
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Model</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Plate Number</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Type & Trans.</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Next Maintenance</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                            @forelse ($this->vehicles as $vehicle)
                                <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-zinc-900 dark:text-white">{{ $vehicle->model }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                            {{ $vehicle->plate_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                        {{ $vehicle->type }} • {{ ucfirst($vehicle->transmission) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClasses = match ($vehicle->status) {
                                                'available'
                                                    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'maintenance'
                                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                'in-use'
                                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                default => 'bg-zinc-100 text-zinc-700',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                            {{ ucfirst(str_replace('-', ' ', $vehicle->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-500">
                                        {{ $vehicle->next_maintenance_date ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu>
                                                <flux:menu.item icon="pencil-square">Edit Details</flux:menu.item>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="trash" variant="danger"
                                                    wire:click="delete({{ $vehicle->id }})">Delete</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                            <flux:icon name="truck" class="size-10 text-zinc-300 mb-3" />
                                            <flux:heading>No vehicles registered</flux:heading>
                                            <div class="mt-4">
                                                <flux:button size="sm" icon="plus" href="#create-vehicle-form">
                                                    Add Vehicle</flux:button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
