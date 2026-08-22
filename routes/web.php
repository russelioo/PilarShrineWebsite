<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('parish');
});

// Add ->middleware(['auth', 'role:admin']) after backend login is connected.
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');

Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])
    ->name('admin.logout');
