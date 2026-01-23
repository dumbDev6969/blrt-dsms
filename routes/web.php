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
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')
    ->name('dashboard');
});

require __DIR__.'/settings.php';
