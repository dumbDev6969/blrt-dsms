<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/services', function () {
        return view('services');
    })->name('guest.services');

    Route::get('/about-us', function () {
        return view('about-us');
    })->name('guest.about');

    
});

Route::livewire('/student/onboard', 'pages::student.onboard')
    ->name('student_profile.create')
    ->middleware('auth');
Route::livewire('instructor/onboard', 'pages::instructor.onboard')
    ->name('instructor_profile.create')->middleware('auth');

// Pages allowed only when the users already created their profiles
Route::middleware(['auth', 'verified', 'profile_completed'])->group(function () {
    Route::view('dashboard', 'pages::dashboard')
    ->name('dashboard');

    // Student documents
    Route::livewire('student/upload-document', 'pages::student.upload-document')
    ->name('document.upload');

    
});

// Admin routes
Route::middleware(['auth', 'can:user.view'])->group(function () {
    Route::livewire('pending-registrations', 'pages::admin.pending-registrations')
    ->name('admin.pending-registrations');

    Route::livewire('manage-courses', 'pages::admin.manage-course')
    ->name('admin.manage-courses');
});

require __DIR__.'/settings.php';
