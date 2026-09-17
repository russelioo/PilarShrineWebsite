<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MinistryDirectoryManagementController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MassIntentionManagementController;
use App\Http\Controllers\MassScheduleController;
use App\Http\Controllers\MinistryManagementController;
use App\Http\Controllers\Parishioner\DashboardController;
use App\Http\Controllers\Parishioner\MassIntentionController;
use App\Http\Controllers\Parishioner\MinistryController;
use App\Http\Controllers\Parishioner\ProfileSettingsController;
use App\Http\Controllers\Parishioner\SacramentRequestController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AnnouncementController;
use App\Services\FacebookLiveService;
use App\Http\Controllers\Parishioner\DonationController as ParishionerDonationController;
use App\Http\Controllers\Admin\DonationManagementController as AdminDonationManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:30,1')->name('inquiries.store');
    Route::get('/inquiries/attachments/{attachment}', [InquiryController::class, 'download'])->name('inquiries.download');
    Route::get('/admin/inquiries', [InquiryController::class, 'index'])->name('admin.inquiries');

    // Real-time Messaging API
    Route::prefix('api/messages')->name('api.messages.')->group(function () {
        Route::get('/conversations', [InquiryController::class, 'apiConversations'])->name('conversations');
        Route::post('/conversations', [InquiryController::class, 'apiStartConversation'])->name('start');
        Route::get('/conversations/{conversation}', [InquiryController::class, 'apiConversationDetails'])->name('details');
        Route::get('/conversations/{conversation}/messages', [InquiryController::class, 'apiMessages'])->name('messages');
        Route::post('/conversations/{conversation}/messages', [InquiryController::class, 'apiSendMessage'])->middleware('throttle:60,1')->name('send');
        Route::post('/conversations/{conversation}/read', [InquiryController::class, 'apiMarkAsRead'])->name('read');
        Route::post('/conversations/{conversation}/archive', [InquiryController::class, 'apiToggleArchive'])->name('archive');
        Route::get('/sync', [InquiryController::class, 'apiSync'])->name('sync');
        Route::get('/directory', [InquiryController::class, 'apiDirectory'])->name('directory');
    });
});

Route::get('/', function () {
    return view('parish');
});

Route::get('/api/announcements', [AnnouncementController::class, 'publicIndex'])->name('api.announcements');
Route::get('/api/mass-schedules', [MassScheduleController::class, 'publicIndex'])->name('api.mass-schedules');

Route::get('/api/livestream-status', function (FacebookLiveService $facebookLive) {
    return response()->json($facebookLive->status());
})->middleware('throttle:60,1')->name('livestream.status');

Route::get('/login', function () {
    return redirect('/#/login');
})->middleware('guest')->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::get('/register', function () {
    return redirect('/#/register');
})->middleware('guest')->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');

// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/auth/google/confirm-register', [GoogleAuthController::class, 'confirmRegister'])->name('auth.google.confirm-register');

// Profile Completion API routes
Route::get('/api/user/profile-status', [ProfileCompletionController::class, 'status'])->name('user.profile-status');
Route::post('/api/parishioner/complete-profile', [ProfileCompletionController::class, 'store'])
    ->middleware('auth')
    ->name('parishioner.complete-profile');

Route::get('/portal', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    if ($user && in_array($user->role, ['admin', 'super_admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'commission_member', 'staff'], true)) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('parishioner.dashboard');
})->middleware('auth')->name('portal');

Route::prefix('parishioner')->name('parishioner.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mass-intentions', [MassIntentionController::class, 'index'])->name('mass-intentions');
    Route::get('/sacrament-requests', [SacramentRequestController::class, 'index'])->name('sacrament-requests');
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries');
    Route::get('/request-mass-intention', [MassIntentionController::class, 'create'])->name('request-mass-intention');
    Route::post('/mass-intentions', [MassIntentionController::class, 'store'])->name('mass-intentions.store');
    Route::get('/request-sacrament', [SacramentRequestController::class, 'create'])->name('request-sacrament');
    Route::post('/sacrament-requests', [SacramentRequestController::class, 'store'])->name('sacrament-requests.store');
    Route::view('/other-requests', 'parishioner.other-requests')->name('other-requests');
    Route::redirect('/events-schedule', '/#/schedule')->name('events-schedule');
    Route::redirect('/announcements', '/#/announcements')->name('announcements');
    Route::get('/donations', [ParishionerDonationController::class, 'index'])->name('donations');
    Route::get('/donations/request', [ParishionerDonationController::class, 'create'])->name('donations.request');
    Route::post('/donations', [ParishionerDonationController::class, 'store'])->name('donations.store');
    Route::get('/ministries', [MinistryController::class, 'index'])->name('ministries');
    Route::post('/ministries/{ministry}/join', [MinistryController::class, 'join'])->name('ministries.join');
    Route::get('/messages-inquiries', [InquiryController::class, 'index'])->name('messages-inquiries');
    Route::get('/profile-settings', [ProfileSettingsController::class, 'index'])->name('profile-settings');
    Route::put('/profile-settings', [ProfileSettingsController::class, 'update'])->name('profile-settings.update');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
});
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/parishioners', [UserManagementController::class, 'parishioners'])->name('parishioners');
        Route::post('/parishioners/{user}/promote', [UserManagementController::class, 'promoteParishioner'])->name('parishioners.promote');
        Route::get('/staff', [UserManagementController::class, 'staff'])->name('staff');
        Route::post('/staff', [UserManagementController::class, 'storeStaff'])->name('staff.store');
        Route::get('/staff/{user}/activity', [UserManagementController::class, 'activity'])->name('staff.activity');
        Route::get('/staff/{user}/details', [UserManagementController::class, 'show'])->name('staff.show');
        Route::get('/staff/{user}/permissions', [UserManagementController::class, 'permissions'])->name('staff.permissions');
        Route::put('/staff/{user}/permissions', [UserManagementController::class, 'updatePermissions'])->name('staff.permissions.update');
        Route::post('/staff/{user}/revert-to-parishioner', [UserManagementController::class, 'revertToParishioner'])->name('staff.revert');
        Route::post('/organization-context/switch', [UserManagementController::class, 'switchOrganization'])->name('organization-context.switch');
        Route::get('/commissions/{commission}/members', [UserManagementController::class, 'commissionMembers'])->name('commissions.members');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
        Route::get('/mass-intentions', [MassIntentionManagementController::class, 'index'])->name('mass-intentions');
        Route::patch('/mass-intentions/{massIntention}/status', [MassIntentionManagementController::class, 'updateStatus'])->name('mass-intentions.status');
        Route::get('/mass-schedules', [MassScheduleController::class, 'index'])->name('mass-schedules');
        Route::post('/mass-schedules', [MassScheduleController::class, 'store'])->name('mass-schedules.store');
        Route::put('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'update'])->name('mass-schedules.update');
        Route::delete('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'destroy'])->name('mass-schedules.destroy');
        Route::get('/ministries', [MinistryDirectoryManagementController::class, 'index'])->name('ministries');
        Route::post('/ministries', [MinistryDirectoryManagementController::class, 'store'])->name('ministries.store');
        Route::put('/ministries/{ministry}', [MinistryDirectoryManagementController::class, 'update'])->name('ministries.update');
        Route::patch('/ministries/{ministry}/toggle', [MinistryDirectoryManagementController::class, 'toggle'])->name('ministries.toggle');
        Route::delete('/ministries/{ministry}', [MinistryDirectoryManagementController::class, 'destroy'])->name('ministries.destroy');
        Route::get('/ministry-requests', [MinistryManagementController::class, 'requests'])->name('ministry-requests');
        Route::post('/ministry-requests/{membership}/approve', [MinistryManagementController::class, 'approve'])->name('ministry-requests.approve');
        Route::post('/ministry-requests/{membership}/reject', [MinistryManagementController::class, 'reject'])->name('ministry-requests.reject');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::patch('/announcements/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('announcements.toggle-pin');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('/donations', [AdminDonationManagementController::class, 'index'])->name('donations');
        Route::patch('/donations/{donation}/status', [AdminDonationManagementController::class, 'updateStatus'])->name('donations.status');
        Route::get('/donations/{donation}/history', [AdminDonationManagementController::class, 'donorHistory'])->name('donations.history');
    });
    Route::view('/appointments', 'admin.appointments')->name('appointments');
    Route::view('/sacramental-records', 'admin.sacramental-records')->name('sacramental-records');
    Route::view('/form-submissions', 'admin.form-submissions')->name('form-submissions');
    Route::view('/notifications', 'admin.notifications')->name('notifications');
});

Route::prefix('staff')->name('staff.')->group(function () {
    // Static staff pages; connect controllers and role middleware with the backend.
    Route::view('/dashboard', 'staff.dashboard')->name('dashboard');

    Route::view('/mass-intentions', 'staff.mass-intentions')->name('mass-intentions');
    Route::view('/sacrament-requests', 'staff.sacrament-requests')->name('sacrament-requests');
    Route::get('/inquiries', [InquiryController::class, 'index'])->middleware('auth')->name('inquiries');

    Route::middleware('auth')->group(function () {
        Route::get('/mass-schedules', [MassScheduleController::class, 'index'])->name('mass-schedules');
        Route::post('/mass-schedules', [MassScheduleController::class, 'store'])->name('mass-schedules.store');
        Route::put('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'update'])->name('mass-schedules.update');
        Route::delete('/mass-schedules/{massSchedule}', [MassScheduleController::class, 'destroy'])->name('mass-schedules.destroy');
        Route::get('/ministry-requests', [MinistryManagementController::class, 'requests'])->name('ministry-requests');
        Route::post('/ministry-requests/{membership}/approve', [MinistryManagementController::class, 'approve'])->name('ministry-requests.approve');
        Route::post('/ministry-requests/{membership}/reject', [MinistryManagementController::class, 'reject'])->name('ministry-requests.reject');
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
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');
