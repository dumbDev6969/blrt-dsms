<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component {
    // Student Properties
    public string $birth_date = '';
    public string $contact_number = '';
    public string $address = '';
    public string $nationality = '';
    public bool $is_minor = false;
    public string $occupation = '';
    public string $educational_attainment = '';
    public string $civil_status = '';
    public string $sex = '';
    public string $ltms_client_id = '';
    public string $student_permit_or_license_no = '';

    // Student Meta details
    public string $passport_file = '';
    public string $guardian_name = '';
    public string $guardian_contact = '';

    // Instructor Properties
    public string $license_number = '';
    public string $license_expiry = '';
    public array $skills = [];
    public array $vehicle_types = [];
    public array $weekly_schedule = [];

    public function mount()
    {
        $user = Auth::user();

        // Only load student profile if user is a student
        if ($user->can('student.view_own')) {
            $student = $user->studentProfile;

            if ($student) {
                $this->birth_date = $student->birth_date?->format('Y-m-d') ?? '';
                $this->sex = $student->sex ?? '';
                $this->civil_status = $student->civil_status ?? '';
                $this->nationality = $student->nationality ?? '';
                $this->contact_number = $student->contact_number ?? '';
                $this->occupation = $student->occupation ?? '';
                $this->educational_attainment = $student->educational_attainment ?? '';
                $this->address = $student->address ?? '';
                $this->is_minor = (bool) $student->is_minor;
                $this->ltms_client_id = $student->ltms_client_id ?? '';
                $this->student_permit_or_license_no = $student->student_permit_or_license_no ?? '';

                $meta = $student->meta_details ?? [];
                $this->passport_file = $meta['passport_file'] ?? '';
                $this->guardian_name = $meta['guardian_name'] ?? '';
                $this->guardian_contact = $meta['guardian_contact'] ?? '';
            }
        }

        // Only load instructor profile if user is an instructor
        if ($user->can('instructor.view_own')) {
            $instructor = $user->instructorProfile;

            if ($instructor) {
                $this->license_number = $instructor->license_number ?? '';
                $this->license_expiry = $instructor->license_expiry?->format('Y-m-d') ?? '';
                $this->skills = $instructor->skills ?? [];
                $this->vehicle_types = $instructor->vehicle_types ?? [];
                $this->weekly_schedule = $instructor->weekly_schedule ?? [];
            } else {
                // Initialize default schedule only for instructors without a profile
                foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                    $this->weekly_schedule[$day] = ['active' => false, 'start' => '08:00', 'end' => '17:00'];
                }
            }
        }
    }

    // Detect minors automatically
    public function updatedBirthDate($value)
    {
        if (!$value) return;

        $this->is_minor = Carbon::parse($value)->age < 18;

        if (!$this->is_minor) {
            $this->guardian_name = '';
            $this->guardian_contact = '';
        }
    }

    public function updateStudentProfile(): void
    {
        $profile = Auth::user()->studentProfile;

        if ($profile) {
            $profile->update([
                'birth_date' => $this->birth_date,
                'sex' => $this->sex,
                'civil_status' => $this->civil_status,
                'nationality' => $this->nationality,
                'contact_number' => $this->contact_number,
                'occupation' => $this->occupation,
                'educational_attainment' => $this->educational_attainment,
                'address' => $this->address,
                'is_minor' => $this->is_minor,
                'ltms_client_id' => $this->ltms_client_id,
                'student_permit_or_license_no' => $this->student_permit_or_license_no,
                'meta_details' => [
                    'passport_file' => $this->passport_file,
                    'guardian_name' => $this->guardian_name,
                    'guardian_contact' => $this->guardian_contact,
                ],
            ]);
        }

        session()->flash('success', 'Student profile updated successfully.');
    }

    public function updateInstructorProfile(): void
    {
        $profile = Auth::user()->instructorProfile;

        if ($profile) {
            $profile->update([
                'license_number' => $this->license_number,
                'license_expiry' => $this->license_expiry,
                'skills' => $this->skills,
                'vehicle_types' => $this->vehicle_types,
                'weekly_schedule' => $this->weekly_schedule,
            ]);
        }

        session()->flash('success', 'Instructor profile updated successfully.');
    }
};
?>


{{-- Life is available only in the present moment. - Thich Nhat Hanh --}}
<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Additional Information Settings') }}</flux:heading>

    <x-callout />

    <x-pages::settings.layout :heading="__('Update information')" :subheading="__('Ensure your information is right.')">

        {{-- only student can view this form --}}
        @can('student.view_own')
            @include('components.student-update-profile')
        @endcan

        {{-- only instructor can view this form --}}
        @can('instructor.view_own')
            @include('components.instructor-update-profile')
        @endcan
    </x-pages::settings.layout>
</section>
