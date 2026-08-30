<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StaffLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('parish');
});

Route::post('/login/staff', [StaffLoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.staff');

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

Route::prefix('staff')->name('staff.')->group(function () {
    // Static staff pages; connect controllers and role middleware with the backend.
    Route::view('/dashboard', 'staff.dashboard')->name('dashboard');

    Route::view('/mass-intentions', 'staff.mass-intentions')->name('mass-intentions');
    Route::view('/sacrament-requests', 'staff.sacrament-requests')->name('sacrament-requests');
    Route::view('/inquiries', 'staff.inquiries')->name('inquiries');

    Route::view('/mass-schedules', 'staff.mass-schedules')->name('mass-schedules');
    Route::view('/sacrament-schedules', 'staff.sacrament-schedules')->name('sacrament-schedules');
    Route::view('/events-calendar', 'staff.events-calendar')->name('events-calendar');

    Route::view('/baptism-records', 'staff.baptism-records')->name('baptism-records');
    Route::view('/marriage-records', 'staff.marriage-records')->name('marriage-records');
    Route::view('/confirmation-records', 'staff.confirmation-records')->name('confirmation-records');

    Route::view('/announcements', 'staff.announcements')->name('announcements');
    Route::view('/events', 'staff.events')->name('events');

    Route::view('/parishioners', 'staff.parishioners')->name('parishioners');
    Route::view('/reports', 'staff.reports')->name('reports');
    Route::view('/profile-settings', 'staff.profile-settings')->name('profile-settings');

    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
});
// Add ->middleware(['auth', 'role:admin']) after backend login is connected.
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');

Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])
    ->name('admin.logout');
