<?php

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\StudentProfile;
use App\Models\User;
new class extends Component {
    #[Validate('required|string')]
    public $birth_date = '';

    #[Validate('required|string')]
    public $sex = '';

    #[Validate('required|string')]
    public $civil_status = '';

    #[Validate('required|string')]
    public $nationality = '';

    #[Validate('required|string|min:11|max:11')]
    public $contact_number = '';

    #[Validate('string|max:50')]
    public $occupation = '';

    #[Validate('required|string')]
    public $educational_attainment = '';

    #[Validate('required|string|max:50')]
    public $address = '';

    // Logic State
    public $is_minor = false;

    // JSON Meta Details
    #[Validate('string|max:50')]
    public $guardian_name = '';
    #[Validate('string|min:11|max:11')]
    public $guardian_contact = '';

    // For foreign
    #[Validate('string|max:50')]
    public $passport_file = '';

    // Licensing
    #[Validate('string|max:50')]
    public $ltms_client_id = '';
    #[Validate('string|max:50')]
    public $license_no = '';

    // Detect minors
    public function updatedBirthDate($value)
    {
        if (!$value) {
            return;
        }
        // Calculate age
        $age = Carbon::parse($value)->age;
        if ($age < 17) {
            $this->addError('birth_date', 'You must be at least 17 years old to register.');
            return;
        }
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
        $essentialFields = [$this->birth_date, $this->sex, $this->civil_status, $this->nationality, $this->contact_number, $this->educational_attainment, $this->address];

        // If minor, add guardian fields to the requirement
        if ($this->is_minor) {
            $essentialFields[] = $this->guardian_name;
            $essentialFields[] = $this->guardian_contact;
        }

        // If foreigner, add passport file
        if ($this->nationality === 'foreigner') {
            $essentialFields[] = $this->passport_file;
        }

        $completed = count(array_filter($essentialFields));
        return round(($completed / count($essentialFields)) * 100);
    }

    public function save()
    {
        // Get the validated data
        $this->validate();

        if ($this->birth_date) {
            $age = Carbon::parse($this->birth_date)->age;
            if ($age < 17) {
                $this->addError('birth_date', 'You must be at least 17 years old to register.');
                return;
            }
        }

        // Create the student profile
        StudentProfile::create([
            'user_id' => Auth::user()->id,
            'birth_date' => $this->birth_date,
            'sex' => $this->sex,
            'civil_status' => $this->civil_status,
            'nationality' => $this->nationality,
            'contact_number' => $this->contact_number,
            'occupation' => $this->occupation,
            'educational_attainment' => $this->educational_attainment,
            'address' => $this->address,
            'is_minor' => $this->is_minor,
            'guardian_name' => $this->guardian_name,
            'guardian_contact' => $this->guardian_contact,
            'passport_file' => $this->passport_file,
            'ltms_client_id' => $this->ltms_client_id,
            'license_no' => $this->license_no,
        ]);

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>


{{-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca --}}
<div class="min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <x-callout />
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="xl">Application Form</flux:heading>
                    <flux:text>You must complete this form to continue.</flux:text>
                </div>
                <flux:button variant="ghost" size="sm" icon="question-mark-circle"
                    class="text-slate-600 dark:text-slate-400">
                    Help
                </flux:button>
            </div>

            {{-- Progress Bar --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-700 dark:text-slate-300">Completing Application</span>
                    <span class="text-[var(--color-accent)] font-bold">{{ $this->progress }}%</span>
                </div>
                <div class="relative h-2 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-[var(--color-accent)] transition-all duration-500 rounded-full"
                        style="width: {{ $this->progress }}%"></div>
                </div>
            </div>
        </div>

        {{-- Main Form Content --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-slate-950/50 border border-slate-200/50 dark:border-slate-800/50 p-8 lg:p-10">
            <form wire:submit.prevent="save" class="space-y-12">

                {{-- Section 1: Personal Identity --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--color-accent)] shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            <flux:icon icon="identification" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Identity & Nationality
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Provide your personal
                                identification details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Birth Date & Sex --}}
                        <flux:input wire:model.live.blur="birth_date" type="date" label="Date of Birth"
                            icon="calendar" />

                        <flux:select wire:model="sex" label="Sex" placeholder="Select...">
                            <flux:select.option value="male">Male</flux:select.option>
                            <flux:select.option value="female">Female</flux:select.option>
                        </flux:select>

                        {{-- Civil Status (New Field) --}}
                        <flux:select wire:model.blur="civil_status" label="Civil Status" placeholder="Select Status...">
                            <flux:select.option value="single">Single</flux:select.option>
                            <flux:select.option value="married">Married</flux:select.option>
                            <flux:select.option value="widowed">Widowed</flux:select.option>
                            <flux:select.option value="separated">Separated</flux:select.option>
                        </flux:select>

                        {{-- Nationality --}}
                        <div class="sm:col-span-2 mt-2">
                            <flux:radio.group wire:model.live="nationality" label="Citizenship Status"
                                variant="segmented">
                                <flux:radio value="filipino" label="Filipino National" icon="flag" />
                                <flux:radio value="foreigner" label="Foreign National" icon="globe-alt" />
                            </flux:radio.group>
                        </div>

                        {{-- Foreigner Logic --}}
                        @if ($nationality === 'foreigner')
                            <div
                                class="sm:col-span-2 p-6 rounded-xl border-2 border-dashed border-sky-200 dark:border-sky-800/50 bg-gradient-to-br from-sky-50 to-blue-50/50 dark:from-sky-950/20 dark:to-blue-950/20 animate-in fade-in slide-in-from-top-2">
                                <div class="flex items-start gap-4 mb-4">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/50">
                                        <flux:icon icon="document-text" class="text-[var(--color-accent)] size-5" />
                                    </div>
                                    <div>
                                        <flux:heading size="sm" class="text-sky-900 dark:text-sky-300">Passport
                                            Attachment</flux:heading>
                                        <p class="text-sm text-sky-700 dark:text-sky-400 mt-1">Upload a clear copy of
                                            your passport</p>
                                    </div>
                                </div>
                                <flux:input type="file" wire:model.blur="passport_file" />
                            </div>
                        @endif
                    </div>
                </flux:fieldset>

                {{-- Minor Logic Alert (Dynamic) --}}
                @if ($this->is_minor)
                    <div
                        class="relative p-6 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border-2 border-amber-200 dark:border-amber-800/50 shadow-lg shadow-amber-100 dark:shadow-none transition-all animate-in zoom-in-95">
                        <div class="flex gap-4">
                            <div
                                class="shrink-0 w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-200 dark:shadow-none">
                                <flux:icon icon="user-group" class="text-white size-6" />
                            </div>
                            <div class="flex-1 space-y-4">
                                <div>
                                    <flux:heading size="sm" class="text-amber-900 dark:text-amber-200">Guardian
                                        Authorization Required</flux:heading>
                                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">The applicant is under
                                        18. Please provide parent/guardian details.</p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <flux:input wire:model.blur="guardian_name" label="Guardian Full Name" dense />
                                    <flux:input wire:model.blur="guardian_contact" label="Guardian Mobile" dense />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <flux:separator />

                {{-- Section 2: Education & Employment (New Section) --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--color-accent)] shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            {{-- Using academic cap icon for background context --}}
                            <flux:icon icon="academic-cap" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Background & Education
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your educational and professional
                                background</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Educational Attainment (New Enum Field) --}}
                        <flux:select wire:model="educational_attainment" label="Highest Educational Attainment"
                            placeholder="Select Level...">
                            <flux:select.option value="elementary">Elementary</flux:select.option>
                            <flux:select.option value="high_school">High School</flux:select.option>
                            <flux:select.option value="college">College / University</flux:select.option>
                            <flux:select.option value="post_graduate">Post Graduate</flux:select.option>
                        </flux:select>

                        {{-- Occupation (New String Field) --}}
                        <flux:input wire:model.blur="occupation" label="Current Occupation" icon="briefcase"
                            placeholder="e.g. Software Engineer" />
                    </div>
                </flux:fieldset>

                <flux:separator />

                {{-- Section 3: Contact & Licenses --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--color-accent)] shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            <flux:icon icon="phone" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Contact & Licensing
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">How can we reach you and verify
                                your records?</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Contact Details --}}
                        <flux:input wire:model.blur="contact_number" label="Phone Number" icon="phone"
                            placeholder="+63" />

                        {{-- LTMS Client ID --}}
                        <flux:input wire:model.blur="ltms_client_id" label="LTMS Client ID" icon="finger-print" />

                        {{-- Student Permit/License (New Field) --}}
                        <flux:input wire:model.blur="student_permit_or_license_no"
                            label="Student Permit / License No." icon="identification" class="sm:col-span-2"
                            placeholder="Enter license number if applicable" />

                        {{-- Address --}}
                        <flux:textarea wire:model.blur="address" label="House no., Purok, Brgy, City, Province"
                            class="sm:col-span-2" rows="3" placeholder="Complete permanent address" />
                    </div>
                </flux:fieldset>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-8 border-t border-slate-200 dark:border-slate-800">
                    <flux:button variant="ghost" class="text-slate-600 dark:text-slate-400">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="arrow-right" icon-trailing loading>
                        Save & Submit Application
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
