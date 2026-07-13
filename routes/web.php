<?php

use App\Http\Controllers\Admin\BookingApprovalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\ScheduleAvailabilityController;
use Illuminate\Support\Facades\Route;

// Import Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\JobController as AdminJobController;

// Import Customer Controllers
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\TrackController as CustomerTrackController;
use App\Http\Controllers\Customer\SupportController as CustomerSupportController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;

// Import Staff Controllers
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\BookingQueueController as StaffBookingQueueController;
use App\Http\Controllers\Staff\AssistanceController as StaffAssistanceController;
use App\Http\Controllers\Staff\WalkInBookingController as StaffWalkInBookingController;
use App\Http\Controllers\Staff\ScheduleController as StaffScheduleController;
use App\Http\Controllers\Staff\CustomerController as StaffCustomerController;
use App\Http\Controllers\Staff\JobController as StaffJobController;

// Import Mechanic Controllers
use App\Http\Controllers\Mechanic\DashboardController as MechanicDashboardController;
use App\Http\Controllers\Mechanic\JobController as MechanicJobController;
use App\Http\Controllers\Mechanic\NoteController as MechanicNoteController;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware(['auth', 'active'])
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function () {

    Route::prefix('admin')
        ->middleware('role:admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('/users', [AdminUserController::class, 'index'])
                ->middleware('permission:users.manage')
                ->name('users.index');

            Route::get('/approvals', [BookingApprovalController::class, 'index'])
                ->middleware('permission:approvals.manage')
                ->name('approvals.index');

            Route::get('/bookings/history', [BookingApprovalController::class, 'history'])
                ->middleware('permission:approvals.manage')
                ->name('bookings.history');

            Route::post('/bookings/{booking}/approve', [BookingApprovalController::class, 'approve'])
                ->middleware('permission:approvals.manage')
                ->name('bookings.approve');

            Route::post('/bookings/{booking}/reject', [BookingApprovalController::class, 'reject'])
                ->middleware('permission:approvals.manage')
                ->name('bookings.reject');

            Route::post('/bookings/{booking}/verify-payment', [BookingApprovalController::class, 'verifyPayment'])
                ->middleware('permission:approvals.manage')
                ->name('bookings.verify-payment');

            Route::get('/services', [AdminServiceController::class, 'index'])
                ->middleware('permission:services.manage')
                ->name('services.index');

            Route::post('/services', [AdminServiceController::class, 'store'])
                ->middleware('permission:services.manage')
                ->name('services.store');

            Route::post('/services/categories', [AdminServiceController::class, 'storeCategory'])
                ->middleware('permission:services.manage')
                ->name('services.categories.store');

            Route::put('/services/{service}', [AdminServiceController::class, 'update'])
                ->middleware('permission:services.manage')
                ->name('services.update');

            Route::post('/services/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])
                ->middleware('permission:services.manage')
                ->name('services.toggle-status');

            Route::delete('/services/{service}', [AdminServiceController::class, 'destroy'])
                ->middleware('permission:services.manage')
                ->name('services.destroy');

            Route::get('/jobs', [AdminJobController::class, 'index'])
                ->middleware('permission:approvals.manage')
                ->name('jobs.index');

            Route::post('/jobs/{job}/assign', [AdminJobController::class, 'assign'])
                ->middleware('permission:approvals.manage')
                ->name('jobs.assign');

            Route::get('/reports', [AdminReportController::class, 'index'])
                ->middleware('permission:reports.view')
                ->name('reports.index');
        });

    Route::prefix('customer')
        ->middleware('role:customer')
        ->name('customer.')
        ->group(function () {
            Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');

            Route::get('/book-service', [BookingController::class, 'create'])
                ->middleware('permission:bookings.create')
                ->name('book-service');

            Route::post('/bookings', [BookingController::class, 'store'])
                ->middleware('permission:bookings.create')
                ->name('bookings.store');

            Route::get('/bookings', [BookingController::class, 'index'])
                ->middleware('permission:bookings.view')
                ->name('bookings.index');

            Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])
                ->middleware('permission:bookings.cancel')
                ->name('bookings.destroy');

            Route::get('/schedule/availability', ScheduleAvailabilityController::class)
                ->middleware('permission:bookings.create')
                ->name('schedule.availability');

            Route::get('/track', [CustomerTrackController::class, 'index'])
                ->middleware('permission:tracking.view')
                ->name('track');

            Route::get('/track/refresh', [CustomerTrackController::class, 'refresh'])
                ->middleware('permission:tracking.view')
                ->name('track.refresh');


            Route::get('/support', [CustomerSupportController::class, 'index'])
                ->middleware('permission:support.view')
                ->name('support.index');

            Route::post('/support', [CustomerSupportController::class, 'store'])
                ->middleware('permission:support.view');

            Route::post('/support/{ticket}/reply', [CustomerSupportController::class, 'storeReply'])
                ->middleware('permission:support.view');

            Route::post('/support/{ticket}/reopen', [CustomerSupportController::class, 'reopen'])
                ->middleware('permission:support.view')
                ->name('support.reopen');

            Route::post('/support/{ticket}/close', [CustomerSupportController::class, 'close'])
                ->middleware('permission:support.view')
                ->name('support.close');

            Route::get('/profile', [CustomerProfileController::class, 'index'])
                ->middleware('permission:profile.view')
                ->name('profile');

            Route::post('/profile', [CustomerProfileController::class, 'update'])
                ->middleware('permission:profile.view');

            Route::get('/payment/{bookingId}', [CustomerPaymentController::class, 'show'])
                ->middleware('permission:payments.submit')
                ->name('payment');
            Route::post('/payment/{bookingId}', [CustomerPaymentController::class, 'submit'])
                ->middleware('permission:payments.submit')
                ->name('payment.submit');
        });

    Route::prefix('staff')
        ->middleware('role:staff')
        ->name('staff.')
        ->group(function () {
            Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');

            // Booking Queue — view and schedule only (no approve/reject/verify-payment)
            Route::get('/booking-queue', [StaffBookingQueueController::class, 'index'])
                ->middleware('permission:bookings.queue.view')
                ->name('booking-queue');

            Route::post('/bookings/{booking}/schedule', [StaffBookingQueueController::class, 'schedule'])
                ->middleware('permission:bookings.schedule');

            // Walk-In Booking
            Route::get('/walk-in-booking', [StaffWalkInBookingController::class, 'create'])
                ->middleware('permission:walk-in.create')
                ->name('walk-in-booking');

            Route::post('/walk-in-booking', [StaffWalkInBookingController::class, 'store'])
                ->middleware('permission:walk-in.create')
                ->name('walk-in-booking.store');

            Route::get('/customers/search', [StaffWalkInBookingController::class, 'searchCustomers'])
                ->middleware('permission:customers.view')
                ->name('customers.search');

            Route::get('/customers/{user}/vehicles', [StaffWalkInBookingController::class, 'getCustomerVehicles'])
                ->middleware('permission:customers.view')
                ->name('customers.vehicles');

            // Schedule Calendar
            Route::get('/schedule', [StaffScheduleController::class, 'index'])
                ->middleware('permission:bookings.queue.view')
                ->name('schedule');

            // Customer Management (read-only)
            Route::get('/customers', [StaffCustomerController::class, 'index'])
                ->middleware('permission:customers.view')
                ->name('customers.index');

            Route::get('/customers/{user}', [StaffCustomerController::class, 'show'])
                ->middleware('permission:customers.view')
                ->name('customers.show');

            // Job Order Monitoring (read-only)
            Route::get('/jobs', [StaffJobController::class, 'index'])
                ->middleware('permission:jobs.view')
                ->name('jobs.index');

            // Customer Assistance / Support Tickets
            Route::get('/assistance', [StaffAssistanceController::class, 'index'])
                ->middleware('permission:support.view')
                ->name('assistance');

            Route::post('/assistance/{ticket}/reply', [StaffAssistanceController::class, 'reply'])
                ->middleware('permission:support.view');

            Route::post('/assistance/{ticket}/resolve', [StaffAssistanceController::class, 'resolve'])
                ->middleware('permission:support.view');

            Route::post('/assistance/{ticket}/status', [StaffAssistanceController::class, 'updateStatus'])
                ->middleware('permission:support.view');
        });

    Route::prefix('mechanic')
        ->middleware('role:mechanic')
        ->name('mechanic.')
        ->group(function () {
            Route::get('/', [MechanicDashboardController::class, 'index'])->name('dashboard');

            Route::get('/jobs', [MechanicJobController::class, 'index'])
                ->middleware('permission:jobs.view')
                ->name('jobs.index');

            Route::post('/jobs/{job}/start', [MechanicJobController::class, 'start'])
                ->middleware('permission:jobs.view');

            Route::post('/jobs/{job}/pause', [MechanicJobController::class, 'pause'])
                ->middleware('permission:jobs.view');

            Route::post('/jobs/{job}/complete', [MechanicJobController::class, 'complete'])
                ->middleware('permission:jobs.view');

            Route::get('/notes', [MechanicNoteController::class, 'index'])
                ->middleware('permission:service-notes.view')
                ->name('notes.index');

            Route::post('/notes', [MechanicNoteController::class, 'store'])
                ->middleware('permission:service-notes.view');
        });
});
