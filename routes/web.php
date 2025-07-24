<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ShiftTemplateController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'can:Edycja pracowników'])->group(function () {
    Route::resource('users', UsersController::class)->except(['show']);
    Route::post('/users/{userId}/restore', [UsersController::class, 'restore'])
        ->name('users.restore');
});

Route::middleware(['auth', 'can:Edycja ról'])->group(function () {
    Route::resource('roles', RolesController::class)->except(['show']);
    Route::post('/roles/{roleId}/restore', [RolesController::class, 'restore'])
        ->name('roles.restore');
});

Route::middleware(['auth', 'can:Moje Preferencje'])->group(function () {
    Route::resource('preferences', PreferenceController::class)->except(['show']);
    Route::post('/preferences/{preferencesId}/restore', [PreferenceController::class, 'restore'])
        ->name('preferences.restore');
});

Route::middleware(['auth', 'can:Harmonogram Zmian'])->group(function () {
    Route::resource('shift-templates', ShiftTemplateController::class)->except(['show']);
    Route::post('/shift-templates/{shift_template}/restore', [ShiftTemplateController::class, 'restore'])
        ->name('shift-templates.restore');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
