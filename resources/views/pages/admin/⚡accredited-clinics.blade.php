<?php

use Livewire\Component;
use App\Models\LtoClinic;
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

    #[Validate('required|string|max:255')]
    public $clinic_name = '';

    #[Validate('required|string|max:100|unique:lto_clinics,accreditation_number')]
    public $accreditation_number = '';

    #[Validate('required|string|max:255')]
    public $address = '';

    #[Validate('required|string|max:20')]
    public $contact_number = '';

    #[Validate('required|date')]
    public $accreditation_expiry = '';

    // Edit Properties
    public ?LtoClinic $editingClinic = null;

    public $edit_clinic_name = '';
    public $edit_accreditation_number = '';
    public $edit_address = '';
    public $edit_contact_number = '';
    public $edit_accreditation_expiry = '';

    public function edit(LtoClinic $clinic)
    {
        $this->editingClinic = $clinic;
        $this->edit_clinic_name = $clinic->clinic_name;
        $this->edit_accreditation_number = $clinic->accreditation_number;
        $this->edit_address = $clinic->address;
        $this->edit_contact_number = $clinic->contact_number;
        $this->edit_accreditation_expiry = $clinic->accreditation_expiry->format('Y-m-d');

        $this->resetValidation();
    }

    public function update()
    {
        $this->validate([
            'edit_clinic_name' => 'required|string|max:255',
            'edit_accreditation_number' => 'required|string|max:100|unique:lto_clinics,accreditation_number,' . $this->editingClinic?->id,
            'edit_address' => 'required|string|max:255',
            'edit_contact_number' => 'required|string|max:20',
            'edit_accreditation_expiry' => 'required|date',
        ]);

        $this->editingClinic->update([
            'clinic_name' => $this->edit_clinic_name,
            'accreditation_number' => $this->edit_accreditation_number,
            'address' => $this->edit_address,
            'contact_number' => $this->edit_contact_number,
            'accreditation_expiry' => $this->edit_accreditation_expiry,
        ]);

        session()->flash('status', 'Clinic updated successfully.');
        $this->reset(['edit_clinic_name', 'edit_accreditation_number', 'edit_address', 'edit_contact_number', 'edit_accreditation_expiry', 'editingClinic']);

        \Flux::modal('edit-clinic')->close();
    }

    public function toggleActive($clinicId)
    {
        $clinic = LtoClinic::findOrFail($clinicId);
        $clinic->update(['is_active' => !$clinic->is_active]);
        session()->flash('status', 'Clinic status updated.');
    }

    #[Computed]
    public function clinics()
    {
        return LtoClinic::query()
            ->when($this->search, function ($query) {
                $query->where('clinic_name', 'like', '%' . $this->search . '%')
                    ->orWhere('accreditation_number', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);
    }

    public function save()
    {
        $validated = $this->validate();

        LtoClinic::create([
            'clinic_name' => $validated['clinic_name'],
            'accreditation_number' => $validated['accreditation_number'],
            'address' => $validated['address'],
            'contact_number' => $validated['contact_number'],
            'accreditation_expiry' => $validated['accreditation_expiry'],
            'is_active' => true,
        ]);

        session()->flash('status', 'Clinic added successfully.');
        $this->reset(['clinic_name', 'accreditation_number', 'address', 'contact_number', 'accreditation_expiry']);
    }

    public function delete(LtoClinic $clinic)
    {
        $clinic->delete();
        session()->flash('status', 'Clinic deleted successfully.');
    }
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-12">

        <x-callout />

        <div class="lg:col-span-1 space-y-2" id="create-clinic-form">
            <flux:heading size="xl" level="1">Accredited Clinics</flux:heading>
            <flux:subheading>
                Manage LTO-accredited clinics for medical certificate validation.
            </flux:subheading>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 ">
            {{-- Form Column --}}
            <div class="lg:col-span-3 w-full">
                <div
                    class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                    <form wire:submit.prevent="save" class="p-6 md:p-8 space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:input label="Clinic Name" wire:model.blur="clinic_name" placeholder="e.g. St. Jude Medical Clinic"
                                icon="building-office-2" required />
                            <flux:input label="Accreditation Number" wire:model.blur="accreditation_number" placeholder="LTO-ACC-12345"
                                icon="hashtag" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:input label="Address" wire:model.blur="address" placeholder="Full address"
                                icon="map-pin" required />
                            <flux:input label="Contact Number" wire:model.blur="contact_number" placeholder="e.g. 09123456789"
                                icon="phone" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:input type="date" label="Accreditation Expiry" wire:model.blur="accreditation_expiry" 
                                icon="calendar" required />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <flux:button variant="subtle" wire:click="$refresh">Cancel</flux:button>
                            <flux:button variant="primary" type="submit">Add Clinic</flux:button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- TABLE SECTION --}}
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Clinic Directory</flux:heading>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        View and manage all accredited clinics.
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <x-live-search placeholder="Search clinics..." variant="filled" />
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="bg-zinc-50/50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Clinic Name</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Accreditation No.</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Contact & Expiry</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">Status</th>
                                <th class="px-6 py-4 font-semibold text-zinc-900 dark:text-white"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800" wire:transition>
                            @forelse ($this->clinics as $clinic)
                                <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $clinic->clinic_name }}</span>
                                            <span class="text-xs text-zinc-500">{{ $clinic->address }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-mono text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                            {{ $clinic->accreditation_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                        <div class="flex flex-col">
                                            <span>{{ $clinic->contact_number }}</span>
                                            <span class="text-xs @if($clinic->accreditation_expiry->isPast()) text-red-500 @else text-zinc-500 @endif">
                                                Expires: {{ $clinic->accreditation_expiry->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:switch wire:click="toggleActive({{ $clinic->id }})" :checked="$clinic->is_active" />
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu>
                                                <flux:modal.trigger name="edit-clinic">
                                                    <flux:menu.item icon="pencil-square"
                                                        wire:click="edit({{ $clinic->id }})">Edit Details
                                                    </flux:menu.item>
                                                </flux:modal.trigger>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="trash" variant="danger"
                                                    wire:confirm="Are you sure you want to delete this clinic?"
                                                    wire:click="delete({{ $clinic->id }})">Delete</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state 
                                    variant="table" 
                                    :colspan="5"
                                    icon="building-office-2"
                                    heading="No clinics registered"
                                    action-url="#create-clinic-form"
                                    action-label="Add Clinic"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                @if ($this->clinics->hasPages())
                    <div
                        class="flex items-center justify-between px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500">
                            Showing {{ $this->clinics->firstItem() }} to {{ $this->clinics->lastItem() }} of
                            {{ $this->clinics->total() }} clinics
                        </div>
                        <div class="flex gap-2">
                            {{ $this->clinics->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Clinic Modal --}}
    <flux:modal name="edit-clinic" class="md:w-96">
        <form wire:submit.prevent="update">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Update Clinic</flux:heading>
                    <flux:text class="mt-2">Make changes to the accredited clinic details.</flux:text>
                </div>

                <flux:input wire:model="edit_clinic_name" label="Clinic Name" required />
                <flux:input wire:model="edit_accreditation_number" label="Accreditation Number" required />
                <flux:input wire:model="edit_address" label="Address" required />
                <flux:input wire:model="edit_contact_number" label="Contact Number" required />
                <flux:input type="date" wire:model="edit_accreditation_expiry" label="Accreditation Expiry" required />

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