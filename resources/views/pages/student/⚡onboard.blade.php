<?php

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new #[Layout('layouts::auth')] class extends Component {
    public $birth_date = '';
    public $sex = '';
    public $civil_status = '';
    public $nationality = '';

    public $contact_number = '';
    public $occupation = '';
    public $educational_attainment = '';
    public $address = '';

    // Logic State
    public $is_minor = false;

    // JSON Meta Details
    public $guardian_name = '';
    public $guardian_contact = '';

    // For foreign
    public $passport_file = '';

    // Licensing
    public $ltms_client_id = '';
    public $license_no = ''; // Maps to student_permit_or_license_no

    // Detect minors
    public function updatedBirthDate($value)
    {
        if (!$value) {
            return;
        }

        // Calculate age
        $age = Carbon::parse($value)->age;
        $this->is_minor = $age < 18;

        // Reset guardian fields if no longer a minor
        if (!$this->is_minor) {
            $this->guardian_name = '';
            $this->guardian_contact = '';
        }
    }

    #[Computed]
    public function progress()
    {
        // Define which fields are "essential" for completion
        $essentialFields = [
            $this->birth_date, 
            $this->sex, 
            $this->nationality, 
            $this->contact_number, 
            $this->address
        ];

        // If minor, add guardian fields to the requirement
        if ($this->is_minor) {
            $essentialFields[] = $this->guardian_name;
            $essentialFields[] = $this->guardian_contact;
        }

        $completed = count(array_filter($essentialFields));
        return round(($completed / count($essentialFields)) * 100);
    }


};
?>


    {{-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca --}}
    <div class="min-h-screen ">
    <div class="max-w-4xl mx-auto py-8 px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="xl" class="text-slate-900 dark:text-slate-100">Application Form</flux:heading>
                    <flux:subheading class="text-slate-600 dark:text-slate-400">Student ID: #REC-{{ now()->format('Ymd') }}</flux:subheading>
                </div>
                <flux:button variant="ghost" size="sm" icon="question-mark-circle" class="text-slate-600 dark:text-slate-400">
                    Help
                </flux:button>
            </div>

            {{-- Progress Bar --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-700 dark:text-slate-300">Step 1 of 3: Identity Details</span>
                    <span class="text-[var(--color-accent)] font-bold">{{ $this->progress }}%</span>
                </div>
                <div class="relative h-2 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="absolute top-0 left-0 h-full bg-[var(--color-accent)] transition-all duration-500 rounded-full" 
                        style="width: {{ $this->progress }}%"
                    ></div>
                </div>
            </div>
        </div>

        {{-- Main Form Content --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-slate-950/50 border border-slate-200/50 dark:border-slate-800/50 p-8 lg:p-10">
            <form wire:submit="save" class="space-y-12">
                {{-- Section: Personal identity --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--color-accent)] shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            <flux:icon icon="identification" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Identity & Nationality
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Provide your personal identification details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:input wire:model.live.blur="birth_date" type="date" label="Date of Birth" icon="calendar" />

                        <flux:select wire:model="sex" label="Sex" placeholder="Select...">
                            <flux:select.option value="male">Male</flux:select.option>
                            <flux:select.option value="female">Female</flux:select.option>
                        </flux:select>

                        <div class="sm:col-span-2">
                            <flux:radio.group wire:model.live="nationality" label="Citizenship Status" variant="segmented">
                                <flux:radio value="filipino" label="Filipino National" icon="flag" />
                                <flux:radio value="foreigner" label="Foreign National" icon="globe-alt" />
                            </flux:radio.group>
                        </div>

                        @if ($nationality === 'foreigner')
                            <div class="sm:col-span-2 p-6 rounded-xl border-2 border-dashed border-sky-200 dark:border-sky-800/50 bg-gradient-to-br from-sky-50 to-blue-50/50 dark:from-sky-950/20 dark:to-blue-950/20 animate-in fade-in slide-in-from-top-2">
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/50">
                                        <flux:icon icon="document-text" class="text-[var(--color-accent)] size-5" />
                                    </div>
                                    <div>
                                        <flux:heading size="sm" class="text-sky-900 dark:text-sky-300">Passport Attachment</flux:heading>
                                        <p class="text-sm text-sky-700 dark:text-sky-400 mt-1">Upload a clear copy of your passport</p>
                                    </div>
                                </div>
                                <flux:input type="file" wire:model="passport_file" />
                            </div>
                        @endif
                    </div>
                </flux:fieldset>

                {{-- Minor Alert --}}
                @if ($this->is_minor)
                    <div class="relative p-6 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border-2 border-amber-200 dark:border-amber-800/50 shadow-lg shadow-amber-100 dark:shadow-none transition-all animate-in zoom-in-95">
                        <div class="flex gap-4">
                            <div class="shrink-0 w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-200 dark:shadow-none">
                                <flux:icon icon="user-group" class="text-white size-6" />
                            </div>
                            <div class="flex-1 space-y-4">
                                <div>
                                    <flux:heading size="sm" class="text-amber-900 dark:text-amber-200">Guardian Authorization Required</flux:heading>
                                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">The applicant is under 18. Please provide parent/guardian details.</p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <flux:input wire:model="guardian_name" label="Guardian Full Name" dense />
                                    <flux:input wire:model="guardian_contact" label="Guardian Mobile" dense />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section: Contact --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--color-accent)] shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            <flux:icon icon="phone" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Primary Contact
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">How can we reach you?</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:input wire:model="contact_number" label="Phone Number" icon="phone" placeholder="+63" />
                        <flux:input wire:model="ltms_client_id" label="LTMS Client ID" icon="finger-print" />
                        
                        <flux:textarea wire:model="address" label="Home Address" class="sm:col-span-2" rows="3" />
                    </div>
                </flux:fieldset>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-8 border-t border-slate-200 dark:border-slate-800">
                    <flux:button variant="ghost" class="text-slate-600 dark:text-slate-400">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="arrow-right" icon-trailing loading>
                        Save & Continue
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>

