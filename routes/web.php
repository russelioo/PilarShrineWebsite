<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('parish');
});

// Parishioners - Static page for now
Route::get('/admin/parishioners', function () {
    return view('admin.parishioners');
})->name('admin.parishioners');

Route::get('/admin/staff', function () {
    return view('admin.staff');
})->name('admin.staff');

// Add ->middleware(['auth', 'role:admin']) after backend login is connected.
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');

Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])
    ->name('admin.logout');
