<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('parish');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // Static pages; replace with controllers when CRUD is implemented.
    Route::view('/parishioners', 'admin.parishioners')->name('parishioners');
    Route::view('/staff', 'admin.staff')->name('staff');
    Route::view('/mass-schedules', 'admin.mass-schedules')->name('mass-schedules');
    Route::view('/time-slots', 'admin.time-slots')->name('time-slots');
    Route::view('/mass-intentions', 'admin.mass-intentions')->name('mass-intentions');
    Route::view('/appointments', 'admin.appointments')->name('appointments');
    Route::view('/sacramental-records', 'admin.sacramental-records')->name('sacramental-records');
    Route::view('/events', 'admin.events')->name('events');
    Route::view('/announcements', 'admin.announcements')->name('announcements');
    Route::view('/donations', 'admin.donations')->name('donations');
    Route::view('/forms', 'admin.forms')->name('forms');
    Route::view('/form-fields', 'admin.form-fields')->name('form-fields');
    Route::view('/form-submissions', 'admin.form-submissions')->name('form-submissions');
    Route::view('/notifications', 'admin.notifications')->name('notifications');
});

// Add ->middleware(['auth', 'role:admin']) after backend login is connected.
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');

Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])
    ->name('admin.logout');
