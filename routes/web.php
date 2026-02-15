<?php

use Illuminate\Support\Facades\Route;

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
    });

    // Admin routes
    Route::middleware(['can:user.view'])->group(function () {
        Route::livewire('pending-registrations', 'pages::admin.pending-registrations')
            ->name('admin.pending-registrations');

        Route::livewire('manage-courses', 'pages::admin.manage-course')
            ->name('admin.manage-courses');
    });
});

require __DIR__ . '/settings.php';
