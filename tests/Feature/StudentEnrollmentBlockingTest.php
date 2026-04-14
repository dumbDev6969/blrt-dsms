<?php

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->student = StudentProfile::factory()->create(['user_id' => $this->user->id]);
    $this->course = Course::factory()->create(['type' => 'theoretical']);
});

test('a student with no active enrollment can access the enrollment form', function () {
    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $this->course])
        ->assertStatus(200);
});

test('a student with an active enrollment is redirected from the enrollment form', function () {
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $this->course])
        ->assertRedirect(route('dashboard'));
});

test('a student with a pending enrollment is redirected from the enrollment form', function () {
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $this->course])
        ->assertRedirect(route('dashboard'));
});

test('a student on waiting list is redirected from the enrollment form', function () {
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'status' => 'waiting_list',
    ]);

    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $this->course])
        ->assertRedirect(route('dashboard'));
});

test('a student with a submitted enrollment form is redirected from the enrollment form', function () {
    EnrollmentForm::factory()->create([
        'student_id' => $this->student->id,
        'status' => 'submitted',
    ]);

    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $this->course])
        ->assertRedirect(route('dashboard'));
});

test('dashboard blocks enrollment UI when student is blocked', function () {
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->user);

    Livewire::test('dashboards.⚡student-dashboard')
        ->assertSet('isEnrollmentBlocked', true)
        ->assertSet('enrollmentBlockReason', 'You already have an active enrollment.');
});

test('a student cannot access PDC enrollment form if TDC is not completed', function () {
    $pdcCourse = Course::factory()->create(['type' => 'practical']);
    
    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $pdcCourse])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'You must complete a Theoretical Driving Course (TDC) before enrolling in a Practical course.');
});

test('a student can access PDC enrollment form if TDC is completed', function () {
    $pdcCourse = Course::factory()->create(['type' => 'practical']);
    
    // Create a completed TDC enrollment
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'tdc_status' => 'completed',
        'status' => 'completed', // Not active/pending so it doesn't trigger the general block
    ]);
    
    $this->actingAs($this->user);

    Livewire::test('pages::student.enrollment-form', ['course' => $pdcCourse])
        ->assertStatus(200);
});

test('dashboard identifies if TDC is completed', function () {
    $this->actingAs($this->user);

    Livewire::test('dashboards.⚡student-dashboard')
        ->assertSet('hasCompletedTdc', false);

    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'tdc_status' => 'completed',
    ]);

    Livewire::test('dashboards.⚡student-dashboard')
        ->assertSet('hasCompletedTdc', true);
});
