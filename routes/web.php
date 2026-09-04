<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MassScheduleController;
use App\Http\Controllers\Parishioner\MassIntentionController;
use App\Http\Controllers\UserManagementController;
use App\Services\FacebookLiveService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('parish');
});

Route::get('/api/livestream-status', function (FacebookLiveService $facebookLive) {
    return response()->json($facebookLive->status());
})->middleware('throttle:60,1')->name('livestream.status');

Route::get('/login', function () {
    return redirect('/#/login');
})->middleware('guest')->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::prefix('parishioner')->name('parishioner.')->middleware('auth')->group(function () {
    Route::view('/dashboard', 'parishioner.dashboard')->name('dashboard');
    Route::get('/mass-intentions', [MassIntentionController::class, 'index'])->name('mass-intentions');
    Route::view('/sacrament-requests', 'parishioner.sacrament-requests')->name('sacrament-requests');
    Route::view('/inquiries', 'parishioner.inquiries')->name('inquiries');
    Route::get('/request-mass-intention', [MassIntentionController::class, 'create'])->name('request-mass-intention');
    Route::post('/mass-intentions', [MassIntentionController::class, 'store'])->name('mass-intentions.store');
    Route::view('/request-sacrament', 'parishioner.request-sacrament')->name('request-sacrament');
    Route::view('/other-requests', 'parishioner.other-requests')->name('other-requests');
    Route::view('/events-schedule', 'parishioner.events-schedule')->name('events-schedule');
    Route::view('/announcements', 'parishioner.announcements')->name('announcements');
    Route::view('/donations', 'parishioner.donations')->name('donations');
    Route::view('/ministries', 'parishioner.ministries')->name('ministries');
    Route::view('/messages-inquiries', 'parishioner.messages-inquiries')->name('messages-inquiries');
    Route::view('/profile-settings', 'parishioner.profile-settings')->name('profile-settings');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
});
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/parishioners', [UserManagementController::class, 'parishioners'])->name('parishioners');
        Route::get('/staff', [UserManagementController::class, 'staff'])->name('staff');
        Route::get('/mass-schedules', [MassScheduleController::class, 'index'])->name('mass-schedules');
        Route::post('/mass-schedules', [MassScheduleController::class, 'store'])->name('mass-schedules.store');
        Route::put('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'update'])->name('mass-schedules.update');
        Route::delete('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'destroy'])->name('mass-schedules.destroy');
    });
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

    Route::middleware('auth')->group(function () {
        Route::get('/mass-schedules', [MassScheduleController::class, 'index'])->name('mass-schedules');
        Route::post('/mass-schedules', [MassScheduleController::class, 'store'])->name('mass-schedules.store');
        Route::put('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'update'])->name('mass-schedules.update');
        Route::delete('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'destroy'])->name('mass-schedules.destroy');
    });
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

Route::post('/admin/livestream', [AdminDashboardController::class, 'updateLivestream'])
    ->middleware('auth')
    ->name('admin.livestream.update');
