<?php

use App\Http\Controllers\Admin\BookingApprovalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\ScheduleAvailabilityController;
use Illuminate\Support\Facades\Route;

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
            Route::get('/', function () {
                return view('admin.dashboard');
            })->name('dashboard');

            Route::get('/users', function () {
                return view('admin.users');
            })->middleware('permission:users.manage')->name('users.index');

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

            Route::get('/services', function () {
                return view('admin.services');
            })->middleware('permission:services.manage')->name('services.index');

            Route::get('/reports', function () {
                return view('admin.reports');
            })->middleware('permission:reports.view')->name('reports.index');
        });

    Route::prefix('customer')
        ->middleware('role:customer')
        ->name('customer.')
        ->group(function () {
            Route::get('/', function () {
                return view('customer.dashboard');
            })->name('dashboard');

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

            Route::get('/track', function () {
                return view('customer.track');
            })->middleware('permission:tracking.view')->name('track');

            Route::get('/support', function () {
                return view('customer.support');
            })->middleware('permission:support.view')->name('support.index');

            Route::get('/profile', function () {
                return view('customer.profile');
            })->middleware('permission:profile.view')->name('profile');

            Route::get('/payment/{bookingId}', function ($bookingId) {
                return view('customer.payment', ['bookingId' => $bookingId]);
            })->middleware('permission:payments.submit')->name('payment');
        });

    Route::prefix('staff')
        ->middleware('role:staff')
        ->name('staff.')
        ->group(function () {
            Route::get('/', function () {
                return view('staff.dashboard');
            })->name('dashboard');

            Route::get('/booking-queue', function () {
                return view('staff.booking-queue');
            })->middleware('permission:bookings.queue.view')->name('booking-queue');

            Route::get('/assistance', function () {
                return view('staff.assistance');
            })->middleware('permission:support.view')->name('assistance');
        });

    Route::prefix('mechanic')
        ->middleware('role:mechanic')
        ->name('mechanic.')
        ->group(function () {
            Route::get('/', function () {
                return view('mechanic.dashboard');
            })->name('dashboard');

            Route::get('/jobs', function () {
                return view('mechanic.jobs');
            })->middleware('permission:jobs.view')->name('jobs.index');

            Route::get('/notes', function () {
                return view('mechanic.notes');
            })->middleware('permission:service-notes.view')->name('notes.index');
        });
});
