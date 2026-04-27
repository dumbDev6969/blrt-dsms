<?php

use Livewire\Component;
use App\Models\Vehicle;
use App\Models\BookingSession;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Validate('required|string|max:50')]
    public $model = '';

    #[Validate('required|string|max:20|unique:vehicles,plate_number')]
    public $plate_number = '';

    #[Validate('required|in:auto,manual')]
    public $transmission = '';

    #[Validate('required|in:motorcycle,automobile,tricycle')]
    public $type = '';

    #[Validate('required|in:available,maintenance,in-use')]
    public $status = 'available';

    #[Validate('nullable|date')]
    public $next_maintenance_date = '';

    // Edit Properties
    public ?Vehicle $editingVehicle = null;

    public $edit_model = '';
    public $edit_plate_number = '';
    public $edit_transmission = '';
    public $edit_type = '';
    public $edit_next_maintenance_date = '';

    public function mount()
    {
        // 1. Auto-set status to 'maintenance' for any vehicle whose date has arrived
        Vehicle::whereIn('status', ['available', 'in-use'])
            ->whereNotNull('next_maintenance_date')
            ->whereDate('next_maintenance_date', '<=', now())
            ->update(['status' => 'maintenance']);

        // 2. Auto-set status for vehicles currently in use based on booking sessions
        $inUseVehicleIds = BookingSession::where(function ($query) {
            $query->where('status', 'in_progress')
                ->orWhere(function ($q) {
                    $q->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                        ->whereNotIn('status', ['completed', 'cancelled']);
                });
        })
            ->pluck('vehicle_id')
            ->filter()
            ->unique();

        // Mark as 'in-use' if not in maintenance
        if ($inUseVehicleIds->isNotEmpty()) {
            Vehicle::whereIn('id', $inUseVehicleIds)
                ->where('status', 'available')
                ->update(['status' => 'in-use']);
        }

        // Revert 'in-use' to 'available' if no active session and not maintenance
        Vehicle::where('status', 'in-use')
            ->whereNotIn('id', $inUseVehicleIds)
            ->update(['status' => 'available']);
    }

    public function edit(Vehicle $vehicle)
    {
        $this->editingVehicle = $vehicle;
        $this->edit_model = $vehicle->model;
        $this->edit_plate_number = $vehicle->plate_number;
        $this->edit_transmission = $vehicle->transmission;
        $this->edit_type = $vehicle->type;
        $this->edit_next_maintenance_date = $vehicle->next_maintenance_date;

        $this->resetValidation();
    }

    public function update()
    {
        $this->edit_plate_number = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->edit_plate_number));

        $this->validate([
            'edit_model' => 'required|string|max:50',
            'edit_plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $this->editingVehicle?->id,
            'edit_transmission' => 'required|in:auto,manual',
            'edit_type' => 'required|in:motorcycle,automobile,tricycle',
            'edit_next_maintenance_date' => 'nullable|date',
        ]);

        $this->editingVehicle->update([
            'model' => $this->edit_model,
            'plate_number' => $this->edit_plate_number,
            'transmission' => $this->edit_transmission,
            'type' => $this->edit_type,
            'next_maintenance_date' => $this->edit_next_maintenance_date,
        ]);

        session()->flash('status', 'Vehicle updated successfully.');
        $this->reset(['edit_model', 'edit_plate_number', 'edit_transmission', 'edit_type', 'edit_next_maintenance_date', 'editingVehicle']);

        \Flux::modal('edit-vehicle')->close();
    }

    public function updateStatus($vehicleId, $status)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        if (!in_array($status, ['available', 'maintenance', 'in-use'])) {
            return;
        }

        $data = ['status' => $status];

        // Clear the maintenance date if its in use or available
        if ($vehicle->status === 'maintenance' && $status !== 'maintenance') {
            $data['next_maintenance_date'] = null;
        }

        $vehicle->update($data);
        session()->flash('status', 'Vehicle status changed to ' . ucfirst(str_replace('-', ' ', $status)) . '.');
    }

    #[Computed]
    public function vehicles()
    {
        return Vehicle::query()
            ->when($this->search, function ($query) {
                $query->where('model', 'like', '%' . $this->search . '%')->orWhere('plate_number', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(5);
    }

    public function save()
    {
        $this->plate_number = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->plate_number));
        $validated = $this->validate();

        Vehicle::create([
            'model' => $validated['model'],
            'plate_number' => $validated['plate_number'],
            'transmission' => $validated['transmission'],
            'type' => $validated['type'],
        ]);

        session()->flash('status', 'Vehicle added successfully.');
        $this->reset();
    }

    public function delete(Vehicle $vehicle)
    {
        $vehicle->delete();
        session()->flash('status', 'Vehicle deleted successfully.');
    }
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-12">

        <x-callout />

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
                    <form wire:submit.prevent="save" class="p-6 md:p-8 space-y-6">

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
                                <flux:select.option value="motorcycle">Motorcycle</flux:select.option>
                                <flux:select.option value="automobile">Automobile</flux:select.option>
                                <flux:select.option value="tricycle">Tricycle</flux:select.option>
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
                    <x-live-search placeholder="Search vehicles..." variant="filled" />
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
                                        <div
                                            class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800/50 p-1 rounded-full w-fit">
                                            @php
                                                $isMaintenanceDue =
                                                    $vehicle->next_maintenance_date?->isToday() ||
                                                    $vehicle->next_maintenance_date?->isPast();
                                            @endphp
                                            <button @if ($isMaintenanceDue) disabled @endif
                                                wire:click="updateStatus({{ $vehicle->id }}, 'available')"
                                                class="px-2.5 py-1 rounded-full text-xs font-medium transition-all duration-200 {{ $vehicle->status === 'available' && !$isMaintenanceDue ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 shadow-[0_1px_2px_rgba(0,0,0,0.05)]' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50' }}">
                                                Available
                                            </button>
                                            <button @if ($isMaintenanceDue) disabled @endif
                                                wire:click="updateStatus({{ $vehicle->id }}, 'in-use')"
                                                class="px-2.5 py-1 rounded-full text-xs font-medium transition-all duration-200 {{ $vehicle->status === 'in-use' && !$isMaintenanceDue ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 shadow-[0_1px_2px_rgba(0,0,0,0.05)]' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50' }}">
                                                In Use
                                            </button>

                                            <button wire:click="updateStatus({{ $vehicle->id }}, 'maintenance')"
                                                class="px-2.5 py-1 rounded-full text-xs font-medium transition-all duration-200 
                                                {{ $vehicle->status === 'maintenance'
                                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 shadow-[0_1px_2px_rgba(0,0,0,0.05)]'
                                                    : ($isMaintenanceDue
                                                        ? 'bg-orange-500 text-white animate-pulse shadow-lg'
                                                        : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50') }}">
                                                Maintenance
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-500">
                                        {{ $vehicle->next_maintenance_date?->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu>
                                                <flux:modal.trigger name="edit-vehicle">
                                                    <flux:menu.item icon="pencil-square"
                                                        wire:click="edit({{ $vehicle->id }})">Edit Details
                                                    </flux:menu.item>
                                                </flux:modal.trigger>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="trash" variant="danger"
                                                    wire:click="delete({{ $vehicle->id }})">Delete</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state 
                                    variant="table" 
                                    :colspan="6"
                                    icon="truck"
                                    heading="No vehicles registered"
                                    action-url="#create-vehicle-form"
                                    action-label="Add Vehicle"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                @if ($this->vehicles->hasPages())
                    <div
                        class="flex items-center justify-between px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500">
                            Showing {{ $this->vehicles->firstItem() }} to {{ $this->vehicles->lastItem() }} of
                            {{ $this->vehicles->total() }} vehicles
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" icon="chevron-left" variant="subtle"
                                wire:click="previousPage" :disabled="$this->vehicles->onFirstPage()" />
                            <flux:button size="sm" icon="chevron-right" variant="subtle" wire:click="nextPage"
                                :disabled="!$this->vehicles->hasMorePages()" />
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Vehicle Modal --}}
    <flux:modal name="edit-vehicle" class="md:w-96">
        <form wire:submit.prevent="update">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Update Vehicle</flux:heading>
                    <flux:text class="mt-2">Make changes to your vehicle details.</flux:text>
                </div>

                <flux:input wire:model="edit_model" label="Model" placeholder="e.g. Toyota Vios" required />
                <flux:input wire:model="edit_plate_number" label="Plate Number" placeholder="ABC 1234" required />

                <flux:select wire:model="edit_type" label="Vehicle Type" placeholder="Select..." required>
                    <flux:select.option value="motorcycle">Motorcycle</flux:select.option>
                    <flux:select.option value="automobile">Automobile</flux:select.option>
                    <flux:select.option value="tricycle">Tricycle</flux:select.option>
                </flux:select>

                <flux:select wire:model="edit_transmission" label="Transmission" placeholder="Select..." required>
                    <flux:select.option value="auto">Automatic</flux:select.option>
                    <flux:select.option value="manual">Manual</flux:select.option>
                </flux:select>

                <flux:input type="date" wire:model="edit_next_maintenance_date" label="Next Maintenance" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Save changes</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
