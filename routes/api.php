<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

// Public
Route::prefix('public')->group(function () {
    Route::get('plans', [PublicController::class, 'plans']);
    Route::get('classes', [PublicController::class, 'classes']);
    Route::get('trainers', [PublicController::class, 'trainers']);
    Route::get('testimonials', [PublicController::class, 'testimonials']);
    Route::get('announcements', [PublicController::class, 'announcements']);
});

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Wallet
    Route::get('wallet', [WalletController::class, 'show']);
    Route::get('wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('wallet/fund', [WalletController::class, 'fund']);
    Route::post('/paystack/webhook', [\App\Http\Controllers\Api\PaystackWebhookController::class, 'handle']);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Memberships
    Route::get('membership-plans', [MembershipController::class, 'plans']);
    Route::get('memberships', [MembershipController::class, 'index']);
    Route::post('memberships/subscribe', [MembershipController::class, 'subscribe']);

    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);

    // Tickets
    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets/verify', [TicketController::class, 'verify'])->middleware('permission:tickets.verify');

    // Feedback
    Route::get('feedback', [FeedbackController::class, 'index']);
    Route::post('feedback', [FeedbackController::class, 'store']);

    // Notifications
    Route::get('notifications', [Admin\NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [Admin\NotificationController::class, 'unreadCount']);
    Route::post('notifications/{notification}/read', [Admin\NotificationController::class, 'markRead']);
    Route::post('notifications/mark-all-read', [Admin\NotificationController::class, 'markAllRead']);

    // Admin area
    Route::prefix('admin')->middleware('role:super-admin|admin|manager|front-desk')->group(function () {
        Route::get('dashboard/stats', [Admin\DashboardController::class, 'stats']);

        Route::apiResource('users', Admin\UserController::class)->middleware('permission:users.view');
        Route::apiResource('membership-plans', Admin\MembershipPlanController::class);
        Route::apiResource('classes', Admin\GymClassController::class);
        Route::apiResource('trainers', Admin\TrainerController::class);
        Route::apiResource('announcements', Admin\AnnouncementController::class);
        Route::apiResource('equipment', Admin\EquipmentController::class);

        Route::get('reports/revenue', [Admin\ReportController::class, 'revenue'])->middleware('permission:reports.view');
        Route::get('reports/bookings', [Admin\ReportController::class, 'bookings'])->middleware('permission:reports.view');
        Route::get('reports/members', [Admin\ReportController::class, 'members'])->middleware('permission:reports.view');

        Route::get('activity-logs', [Admin\ActivityLogController::class, 'index'])->middleware('permission:activity.view');
        Route::get('activity-logs/{activity}', [Admin\ActivityLogController::class, 'show'])->middleware('permission:activity.view');

        Route::apiResource('roles', Admin\RoleController::class)->middleware('permission:roles.view');
        Route::get('settings', [Admin\SettingController::class, 'index'])->middleware('permission:settings.view');
        Route::post('settings', [Admin\SettingController::class, 'update'])->middleware('permission:settings.edit');

        Route::post('feedback/{feedback}/approve', [FeedbackController::class, 'approve'])->middleware('permission:feedback.approve');
        Route::delete('feedback/{feedback}', [FeedbackController::class, 'destroy'])->middleware('permission:feedback.delete');
    });
});