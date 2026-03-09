<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::middleware(['guest'])->group(function () {
    Route::view('/', 'welcome')->name('home');
    Route::view('/services', 'services')->name('guest.services');
    Route::view('/about-us', 'about-us')->name('guest.about');
});

Route::middleware(['auth'])->group(function () {
    Route::livewire('/student/onboard', 'pages::student.onboard')
        ->name('student_profile.create');

    Route::livewire('instructor/onboard', 'pages::instructor.onboard')
        ->name('instructor_profile.create');

    // Pages allowed only when the users already created their profiles
    Route::middleware(['verified', 'profile_completed'])->group(function () {
        Route::view('dashboard', 'pages::dashboard')->name('dashboard');

        // Student documents
        Route::livewire('student/upload-document', 'pages::student.upload-document')
            ->name('document.upload');

        // Enrollment form
        Route::livewire('student/enrollment/{course}', 'pages::student.enrollment-form')
            ->name('enrollment.create');
    });

    // Admin routes
    Route::middleware(['can:user.view'])->group(function () {
        //Registration management
        Route::livewire('pending-registrations', 'pages::admin.pending-registrations')
            ->name('admin.pending-registrations');

        Route::livewire('admin/registrations/{instructor}', 'pages::admin.registration-data')
            ->name('admin.registration-data');

        // Course management
        Route::livewire('manage-courses', 'pages::admin.manage-course')
            ->name('admin.manage-courses');

        // Document management
        Route::livewire('pending-documents', 'pages::admin.pending-documents')
            ->name('admin.pending-documents');

        // Vehicle management
        Route::livewire('manage-vehicle', 'pages::admin.manage-vehicle')
            ->name('admin.manage-vehicle');

        // Serve private documents 
        Route::get('document/serve/{document}', function (App\Models\Document $document) {
            return Storage::disk('local')->response($document->file_path);
        })->name('admin.document.serve');

        // Document checking
        Route::livewire('document/{document}', 'pages::admin.document')
            ->name('admin.document.check');

        // User management
        Route::livewire('manage-users', 'pages::admin.manage-users')
            ->name('admin.manage-users');
    });

    Route::middleware(['can:enrollment.view_any'])->group(function () {
        Route::get('document/serve/{document}', function (App\Models\Document $document) {
            return Storage::disk('local')->response($document->file_path);
        })->name('admin.document.serve');

        // Document checking
        Route::livewire('document/{document}', 'pages::admin.document')
            ->name('admin.document.check');
    });

    // Staff routes
    Route::middleware(['can:student.view_any'])->group(function () {
        // Enrollment management
        Route::livewire('manage-enrollments', 'pages::staff.enrollments')
            ->name('staff.manage-enrollments');

        Route::livewire('enrollment/{enrollment}', 'pages::staff.enrollment')
            ->name('staff.enrollment.show');
    });
});

require __DIR__ . '/settings.php';
