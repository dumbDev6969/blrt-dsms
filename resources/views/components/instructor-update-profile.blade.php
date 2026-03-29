<form wire:submit.prevent="updateInstructorProfile" class="mt-6 space-y-8">
    {{-- Section 1: Professional Licensing --}}
    <flux:fieldset>
        <flux:legend>{{ __('Professional Licensing') }}</flux:legend>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:input wire:model.blur="license_number" :label="__('LTO License No.')" placeholder="N02-12-123456" icon="identification" required />
            <flux:input wire:model.live="license_expiry" type="date" :label="__('License Expiry')" required />
        </div>
    </flux:fieldset>

    <flux:separator variant="subtle" />

    {{-- Section 2: Proficiencies --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <flux:fieldset>
            <flux:legend>{{ __('Authorized Vehicles') }}</flux:legend>
            <div class="grid grid-cols-1 gap-3 mt-4">
                <flux:checkbox.group wire:model.blur="vehicle_types">
                    <flux:checkbox value="Motorcycle" :label="__('Motorcycle')" />
                    <flux:checkbox value="Tricycle" :label="__('Tricycle')" />
                    <flux:checkbox value="Automobile" :label="__('Automobile')" />
                </flux:checkbox.group>
            </div>
            <flux:error name="vehicle_types" />
        </flux:fieldset>

        <flux:fieldset>
            <flux:legend>{{ __('Instruction Capabilities') }}</flux:legend>
            <div class="grid grid-cols-1 gap-3 mt-4">
                <flux:checkbox.group wire:model.blur="skills">
                    <flux:checkbox value="manual" :label="__('Manual Transmission')" />
                    <flux:checkbox value="auto" :label="__('Automatic Transmission')" />
                    <flux:checkbox value="tdc" :label="__('Theoretical Course (TDC)')" />
                </flux:checkbox.group>
            </div>
            <flux:error name="skills" />
        </flux:fieldset>
    </div>

    <flux:separator variant="subtle" />

    {{-- Section 3: Weekly Schedule --}}
    <flux:fieldset>
        <flux:legend>{{ __('Weekly Schedule') }}</flux:legend>
        <flux:text class="mb-4">{{ __('Set your availability for standard instruction hours.') }}</flux:text>

        <div class="space-y-3 border rounded-lg p-4 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 mt-4">
            @foreach (['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $key => $label)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-2 border-b last:border-0 border-zinc-200 dark:border-zinc-700">
                    {{-- Day Toggle --}}
                    <div class="w-32">
                        <flux:switch wire:model.live="weekly_schedule.{{ $key }}.active" :label="$label" />
                    </div>

                    {{-- Time Inputs (Only show if active) --}}
                    @if ($weekly_schedule[$key]['active'] ?? false)
                        <div class="flex items-center gap-2 flex-1 animate-in fade-in slide-in-from-left-2 duration-200">
                            <flux:input type="time" wire:model.blur="weekly_schedule.{{ $key }}.start" class="w-full sm:w-auto" />
                            <span class="text-zinc-400 text-sm italic">{{ __('to') }}</span>
                            <flux:input type="time" wire:model.blur="weekly_schedule.{{ $key }}.end" class="w-full sm:w-auto" />
                        </div>
                    @else
                        <div class="flex-1 text-sm text-zinc-400 italic">
                            {{ __('Unavailable') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </flux:fieldset>

    <div class="flex items-center gap-4">
        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Save Changes') }}
        </flux:button>

        <x-action-message class="me-3" on="profile-updated">
            {{ __('Saved.') }}
        </x-action-message>
    </div>
</form>
