<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        
        {{-- Header --}}
        <div class="mb-8 text-left">
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Instructor Settings
            </flux:heading>
            <flux:subheading class="mt-2 text-zinc-500 dark:text-zinc-400">
                Update your professional credentials and teaching rates.
            </flux:subheading>
        </div>

        {{-- Card Container --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form wire:submit="save" class="space-y-8">

                    <flux:input 
                        wire:model="license_number" 
                        label="LTO License No." 
                        placeholder="N02-12-123456"
                        icon="identification"
                        required 
                    />

                    <flux:input 
                        wire:model="license_expiry" 
                        type="date" 
                        label="License Expiry"
                        required 
                    />


                    <div class="col-span-1 md:col-span-2">
                        <flux:separator variant="subtle" />
                    </div>

                    {{-- Row 3: Vehicle Proficiency --}}
                    <div class="col-span-1 md:col-span-2">
                        <flux:label class="mb-3">Authorized Vehicles</flux:label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:checkbox wire:model="vehicle_types" value="2-wheel" label="Motorcycle (2-Wheel)" />
                            <flux:checkbox wire:model="vehicle_types" value="4-wheel" label="Sedan / SUV (4-Wheel)" />
                        </div>
                    </div>

                    {{-- Row 4: Skills --}}
                    <div class="col-span-1 md:col-span-2">
                        <flux:label class="mb-3">Instruction Capabilities</flux:label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:checkbox wire:model="skills" value="manual" label="Manual Transmission" />
                            <flux:checkbox wire:model="skills" value="auto" label="Automatic Transmission" />
                            <flux:checkbox wire:model="skills" value="tdc" label="Theoretical Course (TDC)" />
                        </div>
                    </div>

                

                {{-- Footer Action --}}
                <div class="flex items-center justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="primary" type="submit" class="w-full md:w-auto">
                        Save Profile
                    </flux:button>
                </div>

            </form>
        </div>

        {{-- Help Text --}}
        <p class="mt-6 text-left text-xs text-zinc-400">
            Need to update your banking details? <a href="#" class="text-zinc-600 hover:underline dark:text-zinc-300">Contact Support</a>
        </p>
    </div>
</div>