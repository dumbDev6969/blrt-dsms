<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\EnrollmentForm;
use App\Models\StudentProfile;
use App\Models\Course;
new class extends Component {
    #[Validate('required')]
    public $course_id = '';

    public $course_type = '';
    public $is_refresher = false;

    // PDC Specifics
    #[Validate('required_if:course_type,PDC')]
    public $vehicle_category = '';

    #[Validate('required_if:course_type,PDC')]
    public $transmission = '';

    // Personal Info (JSON: {emergency_contact:{}})
    #[Validate('required')]
    public $emergency_contact_name = '';

    #[Validate('required')]
    public $emergency_contact_number = '';

    // Course Preferences (JSON: {schedule_pref:[], instructor_pref:null})
    public $schedule_pref = [];
    public $instructor_pref = '';

    public $control_number = '';

    public function mount(Course $course)
    {
        // Get the course id
        $this->course_id = $course->id;

        // Set the course type based on course type
        if ($course->type === 'practical') {
            $this->course_type = 'PDC';
        } elseif ($course->type === 'theoretical') {
            $this->course_type = 'TDC';
        } else {
            $this->course_type = str_contains(strtolower($course->title), 'refresher') ? 'Refresher' : 'TDC';
        }

        if (str_contains(strtolower($course->title), 'refresher')) {
            $this->is_refresher = true;
            if ($course->type === 'practical') {
                $this->course_type = 'PDC';
            }
        }

        // Generate control number
        do {
            $this->control_number = 'BLRT-' . Str::upper(Str::random(8));
        } while (EnrollmentForm::where('control_number', $this->control_number)->exists());
    }

    #[Computed]
    public function progress()
    {
        $fields = [$this->course_type, $this->emergency_contact_name, $this->emergency_contact_number];

        if ($this->course_type === 'PDC') {
            $fields[] = $this->vehicle_category;
            $fields[] = $this->transmission;
        }

        $completed = count(array_filter($fields));
        return round(($completed / count($fields)) * 100);
    }

    public function save()
    {
        $this->validate();

        // Get the student id
        $student_id = StudentProfile::where('user_id', Auth::user()->id)->first()->id;

        // Save to db
        EnrollmentForm::create([
            'student_id' => $student_id,
            'course_id' => $this->course_id,
            'control_number' => $this->control_number,
            'package_type' => $this->is_refresher ? 'Refresher' : $this->course_type,
            'vehicle_category' => $this->course_type === 'PDC' ? $this->vehicle_category : null,
            'transmission' => $this->course_type === 'PDC' ? $this->transmission : null,
            'personal_info' => [
                'emergency_contact' => [
                    'name' => $this->emergency_contact_name,
                    'number' => $this->emergency_contact_number,
                ],
            ],
            'course_preferences' => [
                'schedule_pref' => $this->schedule_pref,
                'instructor_pref' => $this->instructor_pref,
            ],
            'status' => 'submitted',
        ]);
        session()->flash('status', 'Enrollment form submitted successfully.');
        return $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-6 lg:px-8">
        <x-callout />
        {{-- Header --}}
        <div class="mb-8 relative">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="xl">Enrollment Form</flux:heading>
                    <flux:text>Please fill out the details below to enroll in a course.</flux:text>
                </div>
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">
                        Control Number</div>
                    <div
                        class="text-sm font-mono font-bold text-[var(--color-accent)] bg-sky-50 dark:bg-sky-950/30 px-3 py-1.5 rounded-lg border border-sky-100 dark:border-sky-900/50">
                        {{ $control_number }}
                    </div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-700 dark:text-slate-300">Form Completion</span>
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
                {{-- Section 1: Course Options --}}
                <flux:fieldset class="animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-sky-500 shadow-lg shadow-sky-200 dark:shadow-sky-950/50">
                            <flux:icon icon="arrow-path" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Course Options
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure additional options for
                                your course</p>
                        </div>
                    </div>

                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700/50">
                        <flux:switch wire:model.live="is_refresher" label="Refresher Course"
                            description="Check this if you are actively taking this course as a refresher." />
                    </div>
                </flux:fieldset>

                {{-- Section 2: PDC Customization (Dynamic) --}}
                @if ($course_type === 'PDC')
                    <flux:fieldset class="animate-in fade-in slide-in-from-top-2">
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-500 shadow-lg shadow-purple-200 dark:shadow-purple-950/50">
                                <flux:icon icon="truck" class="text-white size-5" />
                            </div>
                            <div>
                                <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                    Practical Course Details
                                </flux:legend>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Specify your vehicle
                                    preferences for the practical course</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <flux:select wire:model="vehicle_category" label="Vehicle Category"
                                placeholder="Select category...">
                                <flux:select.option value="4-Wheel">4-Wheel (Sedan/SUV)</flux:select.option>
                                <flux:select.option value="Motorcycle">Motorcycle</flux:select.option>
                                <flux:select.option value="Tricycle">Tricycle</flux:select.option>
                            </flux:select>

                            <flux:select wire:model="transmission" label="Transmission Type"
                                placeholder="Select transmission...">
                                <flux:select.option value="Automatic">Automatic</flux:select.option>
                                <flux:select.option value="Manual">Manual</flux:select.option>
                            </flux:select>
                        </div>
                    </flux:fieldset>
                @endif

                <flux:separator />

                {{-- Section 3: Emergency Contact --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-500 shadow-lg shadow-rose-200 dark:shadow-rose-950/50">
                            <flux:icon icon="exclamation-triangle" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Emergency Contact
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Who should we contact in case of
                                an emergency?</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:input wire:model.blur="emergency_contact_name" label="Contact Person Name" icon="user"
                            placeholder="Full Name" />
                        <flux:input wire:model.blur="emergency_contact_number" label="Contact Number" icon="phone"
                            placeholder="+63" />
                    </div>
                </flux:fieldset>

                <flux:separator />

                {{-- Section 4: Training Preferences --}}
                <flux:fieldset>
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-500 shadow-lg shadow-orange-200 dark:shadow-orange-950/50">
                            <flux:icon icon="clock" class="text-white size-5" />
                        </div>
                        <div>
                            <flux:legend class="!text-lg font-bold text-slate-900 dark:text-slate-100">
                                Course Preferences
                            </flux:legend>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Help us tailor your training
                                schedule and experience</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <flux:checkbox.group wire:model="schedule_pref" label="Preferred Training Days"
                            class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <flux:checkbox value="Monday" label="Monday" />
                            <flux:checkbox value="Tuesday" label="Tuesday" />
                            <flux:checkbox value="Wednesday" label="Wednesday" />
                            <flux:checkbox value="Thursday" label="Thursday" />
                            <flux:checkbox value="Friday" label="Friday" />
                            <flux:checkbox value="Saturday" label="Saturday" />
                            <flux:checkbox value="Sunday" label="Sunday" />
                        </flux:checkbox.group>

                        <flux:input wire:model="instructor_pref" label="Preferred Instructor (Optional)"
                            icon="user-circle" placeholder="Enter name if you have a preference" />
                    </div>
                </flux:fieldset>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-8 border-t border-slate-200 dark:border-slate-800">
                    <flux:button variant="ghost" class="text-slate-600 dark:text-slate-400">
                        Save as Draft
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check-circle" icon-trailing loading>
                        Submit Enrollment
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
