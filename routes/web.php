<?php

use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftTemplateController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkerScheduleController;
use App\Http\Controllers\HolidayController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::middleware(['auth', 'can:Edycja Grafików Pracy'])->group(function () {
    Route::resource('schedules', ScheduleController::class)->except(['show']);
    Route::post('/schedules/{schedule}/restore', [ScheduleController::class, 'restore'])->name('schedules.restore');
    Route::post('/schedules/{schedule}/publish', [ScheduleController::class, 'publish'])->name('schedules.publish');
    Route::post('/schedules/{schedule}/unpublish', [ScheduleController::class, 'unpublish'])->name('schedules.unpublish');
});

Route::middleware(['auth', 'can:Grafik Pracy'])->group(function () {
    Route::prefix('employee-schedules')->name('employee.schedules.')->group(function () {
        Route::get('/', [WorkerScheduleController::class, 'index'])->name('index');
        Route::get('/{schedule}', [WorkerScheduleController::class, 'show'])->name('show');
        Route::get('/{schedule}/pdf/full', [WorkerScheduleController::class, 'downloadFullPdf'])->name('pdf.full');
        Route::get('/{schedule}/pdf/my', [WorkerScheduleController::class, 'downloadMyPdf'])->name('pdf.my');
    });

});

Route::middleware(['auth', 'can:Konfiguracja dni wolnych'])->group(function () {
    Route::resource('holidays', HolidayController::class)->except(['show']);
    Route::post('/holidays/{holiday}/restore', [HolidayController::class, 'restore'])->name('holidays.restore');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
