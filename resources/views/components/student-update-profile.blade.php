<form wire:submit.prevent="updateStudentProfile" class="mt-6 space-y-6">
    {{-- Personal Information Section --}}
    <flux:fieldset>
        <flux:legend>{{ __('Personal Information') }}</flux:legend>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:input wire:model.live="birth_date" :label="__('Birth Date')" type="date" required />
            <flux:select wire:model.blur="sex" :label="__('Sex')" placeholder="{{ __('Select...') }}" required>
                <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <flux:select wire:model.blur="civil_status" :label="__('Civil Status')" placeholder="{{ __('Select...') }}" required>
                <flux:select.option value="single">{{ __('Single') }}</flux:select.option>
                <flux:select.option value="married">{{ __('Married') }}</flux:select.option>
                <flux:select.option value="widowed">{{ __('Widowed') }}</flux:select.option>
                <flux:select.option value="separated">{{ __('Separated') }}</flux:select.option>
            </flux:select>
            
            <div class="flex flex-col gap-2">
                <flux:label>{{ __('Nationality') }}</flux:label>
                <flux:radio.group wire:model.live="nationality" variant="segmented">
                    <flux:radio value="filipino" :label="__('Filipino')" />
                    <flux:radio value="foreigner" :label="__('Foreigner')" />
                </flux:radio.group>
            </div>
        </div>

        {{-- Foreigner Logic --}}
        @if ($nationality === 'foreigner')
            <div class="mt-6 p-4 rounded-xl border border-sky-200 bg-sky-50/50 dark:border-sky-800 dark:bg-sky-950/20 animate-in fade-in slide-in-from-top-2">
                <flux:input type="file" wire:model="passport_file" :label="__('Passport Attachment')" />
                <flux:text size="sm" class="mt-2">{{ __('Upload a clear copy of your passport.') }}</flux:text>
            </div>
        @endif

        {{-- Minor Logic --}}
        @if ($is_minor)
            <div class="mt-6 p-4 rounded-xl border border-amber-200 bg-amber-50/50 dark:border-amber-800 dark:bg-amber-950/20 animate-in fade-in slide-in-from-top-2">
                <flux:heading size="sm" class="mb-4 text-amber-900 dark:text-amber-200">{{ __('Guardian Information (Required for Minors)') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="guardian_name" :label="__('Guardian Name')" />
                    <flux:input wire:model="guardian_contact" :label="__('Guardian Contact')" />
                </div>
            </div>
        @endif
    </flux:fieldset>

    <flux:separator />

    {{-- Contact & Professional Information --}}
    <flux:fieldset>
        <flux:legend>{{ __('Contact & Professional Details') }}</flux:legend>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:input wire:model.blur="contact_number" :label="__('Contact Number')" type="tel" required />
            <flux:input wire:model.blur="occupation" :label="__('Occupation')" />
        </div>

        <div class="mt-6">
            <flux:textarea wire:model.blur="address" :label="__('Current Address')" required rows="3" />
        </div>

        <div class="mt-6">
            <flux:select wire:model.blur="educational_attainment" :label="__('Educational Attainment')" placeholder="{{ __('Select...') }}" required>
                <flux:select.option value="elementary">{{ __('Elementary') }}</flux:select.option>
                <flux:select.option value="high_school">{{ __('High School') }}</flux:select.option>
                <flux:select.option value="college">{{ __('College') }}</flux:select.option>
                <flux:select.option value="post_graduate">{{ __('Post Graduate') }}</flux:select.option>
            </flux:select>
        </div>
    </flux:fieldset>

    <flux:separator />

    {{-- Licensing Information --}}
    <flux:fieldset>
        <flux:legend>{{ __('Licensing Information') }}</flux:legend>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:input wire:model.blur="ltms_client_id" :label="__('LTMS Client ID')" placeholder="e.g. 12-345678" />
            <flux:input wire:model.blur="student_permit_or_license_no" :label="__('Student Permit or License No.')" />
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