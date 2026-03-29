<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\InstructorProfile;
use App\Models\User;
use Livewire\Attributes\Validate;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
new class extends Component {
    #[Validate('required|string|max:15|min:15|unique:instructor_profiles,license_number')]
    public $license_number = '';

    #[Validate('required|date')]
    public $license_expiry = '';

    #[Validate('required|array')]
    public $skills = [];

    #[Validate('array')]
    public $vehicle_types = [];

    #[Validate('required|array')]
    public $weekly_schedule = [];

    // Check if the license is valid
    #[Computed]
    public function licenseStatus()
    {
        if (empty($this->license_expiry)) {
            return 'valid';
        }

        $expiry = Carbon::parse($this->license_expiry);

        if ($expiry->isPast()) {
            return 'expired';
        }

        // Check if expiry is within the next 7 days
        if ($expiry->isBetween(now(), now()->addDays(7))) {
            return 'warning';
        }

        return 'valid';
    }

    public function mount()
    {
        // Initialize default schedule structure
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        foreach ($days as $day) {
            // We set up a default structure for each day
            $this->weekly_schedule[$day] = [
                'active' => false,
                'start' => '08:00',
                'end' => '17:00',
            ];
        }
    }

    public function save()
    {
        // Get the validated data
        $validated = $this->validate();

        // Save to db
        InstructorProfile::create([
            'user_id' => Auth::user()->id,
            'license_number' => $validated['license_number'],
            'license_expiry' => $validated['license_expiry'],
            'skills' => $validated['skills'],
            'vehicle_types' => $validated['vehicle_types'],
            'weekly_schedule' => $validated['weekly_schedule'],
        ]);

        // Redirect to dashboard
        $this->redirect(route('dashboard'), navigate: true);
    }
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
            <form wire:submit.prevent="save" class="space-y-8">

                {{-- Section 1: License & Rate --}}

                <flux:input wire:model.blur="license_number" label="LTO License No." placeholder="N02-12-123456"
                    icon="identification" required />
                @if ($this->licenseStatus === 'expired')
                    {{-- Expired State --}}
                    <flux:input wire:model.live="license_expiry" type="date" label="Your license is expired" required
                        invalid />
                @elseif ($this->licenseStatus === 'warning')
                    {{-- Warning State (About to expire) --}}
                    <flux:input wire:model.live="license_expiry" type="date" label="Your license is about to expire"
                        description="Please renew this soon." required />
                @else
                    {{-- Normal State --}}
                    <flux:input wire:model.blur="license_expiry" type="date" label="License Expiry" required />
                @endif



                <flux:separator variant="subtle" />

                {{-- Section 2: Proficiencies --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <flux:label class="mb-3">Authorized Vehicles</flux:label>
                        <div class="grid grid-cols-1 gap-3">
                            <flux:checkbox.group>
                                <flux:checkbox.all label="All" />

                                <flux:checkbox wire:model.blur="vehicle_types" value="Motorcycle"
                                    label="Motorcycle" />
                                <flux:checkbox wire:model.blur="vehicle_types" value="Tricycle"
                                    label="Tricycle" />
                                <flux:checkbox wire:model.blur="vehicle_types" value="Automobile"
                                    label="Automobile (4-Wheel)" />
                            </flux:checkbox.group>
                        </div>
                        <flux:error name="vehicle_types" />
                    </div>

                    <div>
                        <flux:label class="mb-3">Instruction Capabilities</flux:label>
                        <div class="grid grid-cols-1 gap-3">
                            <flux:checkbox.group>
                                <flux:checkbox.all label="All" />
                                <flux:checkbox wire:model.blur="skills" value="manual" label="Manual Transmission" />
                                <flux:checkbox wire:model.blur="skills" value="auto" label="Automatic Transmission" />
                                <flux:checkbox wire:model.blur="skills" value="tdc"
                                    label="Theoretical Course (TDC)" />
                            </flux:checkbox.group>
                        </div>
                        <flux:error name="skills" />
                    </div>
                </div>

                <flux:separator variant="subtle" />

                {{-- Section 3: Weekly Schedule --}}
                <div>
                    <flux:heading size="lg" class="mb-1">Weekly Schedule</flux:heading>
                    <flux:subheading class="mb-4">Set your availability for standard instruction hours.
                    </flux:subheading>

                    <div
                        class="space-y-3 border rounded-lg p-4 bg-zinc-50 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800">
                        @foreach (['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $key => $label)
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-2 border-b last:border-0 border-zinc-200 dark:border-zinc-700">

                                {{-- Day Toggle --}}
                                <div class="w-32">
                                    <flux:switch wire:model.live="weekly_schedule.{{ $key }}.active"
                                        label="{{ $label }}" />
                                </div>

                                {{-- Time Inputs (Only show if active) --}}
                                @if ($weekly_schedule[$key]['active'] ?? false)
                                    <div
                                        class="flex items-center gap-2 flex-1 animate-in fade-in slide-in-from-left-2 duration-200">
                                        <flux:input type="time"
                                            wire:model.blur="weekly_schedule.{{ $key }}.start"
                                            class="w-full sm:w-auto" />
                                        <span class="text-zinc-400 text-sm">to</span>
                                        <flux:input type="time"
                                            wire:model.blur="weekly_schedule.{{ $key }}.end"
                                            class="w-full sm:w-auto" />
                                    </div>
                                @else
                                    <div class="flex-1 text-sm text-zinc-400 italic">
                                        Unavailable
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer Action --}}
                <div class="flex items-center justify-end pt-4">
                    @if ($this->licenseStatus === 'expired' || Auth::user()->instructorProfile?->isPending())
                        <flux:button variant="primary" type="submit" class="w-full md:w-auto" disabled>
                            Save Profile
                        </flux:button>
                    @else
                        <flux:button variant="primary" type="submit" class="w-full md:w-auto">
                            Save Profile
                        </flux:button>
                    @endif
                    
                    
                </div>

            </form>
        </div>

        {{-- Help Text --}}
        <p class="mt-6 text-left text-xs text-zinc-400">
            Need to update your banking details? <a href="#"
                class="text-zinc-600 hover:underline dark:text-zinc-300">Contact Support</a>
        </p>
    </div>
</div>
