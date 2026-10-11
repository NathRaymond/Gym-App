<?php

use App\Http\Controllers\Api\FeedbackController as ApiFeedbackController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\WalletController as ApiWalletController;
use App\Http\Controllers\Web\Admin\AnnouncementController;
use App\Http\Controllers\Web\Admin\BookingController;
use App\Http\Controllers\Web\Admin\EquipmentController;
use App\Http\Controllers\Web\Admin\GymClassController;
use App\Http\Controllers\Web\Admin\MembershipPlanController;
use App\Http\Controllers\Web\Admin\RoleController;
use App\Http\Controllers\Web\Admin\SessionCategoryController;
use App\Http\Controllers\Web\Admin\SessionSlotController;
use App\Http\Controllers\Web\Admin\SessionTypeController;
use App\Http\Controllers\Web\Admin\TrainerController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FrontDesk\FrontDeskPaymentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\Member\MemberBookingController;
use App\Http\Controllers\Web\Member\MemberFeedbackController;
use App\Http\Controllers\Web\Member\MemberMembershipController;
use App\Http\Controllers\Web\Member\MemberPaymentController;
use App\Http\Controllers\Web\Member\MemberWalletController;
use App\Http\Controllers\Web\Admin\BookingTypeController;
use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC
// ============================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================
// AUTH
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register']);
});

Route::post('/logout', [AuthWebController::class, 'logout'])
    ->middleware('auth')->name('logout');

// ============================================
// MEMBER DASHBOARD  →  /member/*
// ============================================
Route::middleware(['auth', 'role:member'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Bookings
        Route::get('/bookings', [MemberBookingController::class, 'index'])->name('bookings');
        Route::get('/bookings/create', [MemberBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [MemberBookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{booking}/cancel', [MemberBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/pay', [MemberPaymentController::class, 'payBooking'])->name('bookings.pay');
        Route::post('/bookings/available-slots', [MemberBookingController::class, 'availableSlots'])->name('bookings.slots');
        Route::post('/bookings/slots', [MemberBookingController::class, 'loadSlots'])->name('bookings.slots');
        // Membership
        Route::get('/membership', [MemberMembershipController::class, 'index'])->name('membership');
        Route::post('/membership/subscribe', [MemberMembershipController::class, 'subscribe'])->name('membership.subscribe');
        Route::get('/membership/callback', [MemberMembershipController::class, 'paystackCallback'])->name('membership.callback');
        Route::post('/memberships/{membership}/pay', [MemberPaymentController::class, 'payMembership'])->name('memberships.pay');

        // Wallet
        Route::get('/wallet', [MemberWalletController::class, 'index'])->name('wallet');
        Route::post('/wallet/fund/paystack', [MemberWalletController::class, 'fundWithPaystack'])->name('wallet.fund.paystack');
        Route::get('/wallet/callback', [MemberWalletController::class, 'paystackCallback'])->name('wallet.callback');

        // Keep the API fund endpoint for manual/cash funding
        Route::post('/wallet/fund', [ApiWalletController::class, 'fund'])->name('wallet.fund');

        // Payments (view only)
        Route::get('/payments', [DashboardController::class, 'payments'])->name('payments');

        // Tickets
        Route::get('/tickets', [DashboardController::class, 'tickets'])->name('tickets');

        // Feedback
        Route::get('/feedback', [MemberFeedbackController::class, 'index'])->name('feedback');
        Route::post('/feedback', [MemberFeedbackController::class, 'store'])->name('feedback.store');

        // Profile
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    });

// ============================================
// FRONT DESK DASHBOARD  →  /frontdesk/*
// ============================================
Route::middleware(['auth', 'role:front-desk|manager|admin|super-admin'])
    ->prefix('frontdesk')
    ->name('frontdesk.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'frontDeskDashboard'])->name('dashboard');
        Route::get('/verify-ticket', [DashboardController::class, 'verifyTicketPage'])->name('verify-ticket');

        // Payments
        Route::get('/payments', [DashboardController::class, 'frontDeskPayments'])->name('payments');
        Route::post('/payments', [FrontDeskPaymentController::class, 'store'])->name('payments.store');  // ✅ FIXED

        // Members
        Route::get('/members', [DashboardController::class, 'frontDeskMembers'])->name('members');

        // Bookings
        Route::get('/bookings', [DashboardController::class, 'frontDeskBookings'])->name('bookings');

        // Ticket verification
        Route::post('/tickets/verify', [TicketController::class, 'verify'])->name('tickets.verify');
    });

// ============================================
// TRAINER DASHBOARD  →  /trainer/*
// ============================================
Route::middleware(['auth', 'role:trainer|admin|super-admin'])
    ->prefix('trainer')
    ->name('trainer.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'trainerDashboard'])->name('dashboard');
        Route::get('/schedule', [DashboardController::class, 'trainerSchedule'])->name('schedule');
        Route::get('/clients', [DashboardController::class, 'trainerClients'])->name('clients');
        Route::get('/earnings', [DashboardController::class, 'trainerEarnings'])->name('earnings');
    });

// ============================================
// ADMIN DASHBOARD  →  /admin/*
// ============================================
Route::middleware(['auth', 'role:super-admin|admin|manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // USERS
        Route::resource('users', UserController::class);

        // MEMBERS
        Route::get('members', [DashboardController::class, 'adminMembers'])->name('members');

        // PLANS
        Route::resource('plans', MembershipPlanController::class);

        // CLASSES
        Route::resource('classes', GymClassController::class);

        // TRAINERS
        Route::resource('trainers', TrainerController::class);

        // BOOKINGS
        Route::resource('bookings', BookingController::class);
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        // PAYMENTS
        Route::get('payments', [DashboardController::class, 'adminPayments'])->name('payments');

        // FEEDBACK
        Route::get('feedback', [DashboardController::class, 'adminFeedback'])->name('feedback');
        Route::post('feedback/{feedback}/approve', [ApiFeedbackController::class, 'approve'])->name('feedback.approve');
        Route::delete('feedback/{feedback}', [ApiFeedbackController::class, 'destroy'])->name('feedback.destroy');

        // ANNOUNCEMENTS
        Route::resource('announcements', AnnouncementController::class);

        // EQUIPMENT
        Route::resource('equipment', EquipmentController::class);

        // ROLES (super-admin only)
        Route::middleware('role:super-admin')->group(function () {
            Route::resource('roles', RoleController::class);
        });

        // ACTIVITY
        Route::get('activity', [DashboardController::class, 'activityLog'])->name('activity');

        // SETTINGS
        Route::get('settings', [DashboardController::class, 'adminSettings'])->name('settings');

        // REPORTS
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [DashboardController::class, 'reportRevenue'])->name('revenue');
            Route::get('/bookings', [DashboardController::class, 'reportBookings'])->name('bookings');
            Route::get('/members', [DashboardController::class, 'reportMembers'])->name('members');
        });

        // SESSION CATEGORIES
        Route::resource('session-categories', SessionCategoryController::class);

        // SESSION TYPES
        Route::resource('session-types', SessionTypeController::class);

        // SESSION SLOTS (bulk routes BEFORE resource to avoid {id} wildcard match)
        Route::get('session-slots/bulk', [SessionSlotController::class, 'bulkCreate'])->name('session-slots.bulk');
        Route::post('session-slots/bulk', [SessionSlotController::class, 'bulkStore'])->name('session-slots.bulk.store');
        Route::resource('session-slots', SessionSlotController::class)->except(['show']);
    });
