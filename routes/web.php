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

Route::middleware(['auth', 'verified', 'profile_completed'])->group(function () {
    Route::view('dashboard', 'pages::dashboard')
    ->name('dashboard');

    
});

require __DIR__.'/settings.php';
