<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return view('landing');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    });
    Route::get('/users', function () {
        return view('admin.users');
    });
    Route::get('/approvals', function () {
        return view('admin.approvals');
    });
    Route::get('/services', function () {
        return view('admin.services');
    });
    Route::get('/reports', function () {
        return view('admin.reports');
    });
});

Route::prefix('customer')->group(function () {
    Route::get('/', function () {
        return view('customer.dashboard');
    });
    Route::get('/book-service', function () {
        return view('customer.book-service');
    });
    Route::get('/bookings', function () {
        return view('customer.bookings');
    });
    Route::get('/track', function () {
        return view('customer.track');
    });
    Route::get('/support', function () {
        return view('customer.support');
    });
    Route::get('/profile', function () {
        return view('customer.profile');
    });
    Route::get('/payment/{bookingId}', function ($bookingId) {
        return view('customer.payment', ['bookingId' => $bookingId]);
    });
});

Route::prefix('staff')->group(function () {
    Route::get('/', function () {
        return view('staff.dashboard');
    });
    Route::get('/booking-queue', function () {
        return view('staff.booking-queue');
    });
    Route::get('/assistance', function () {
        return view('staff.assistance');
    });
});

Route::prefix('mechanic')->group(function () {
    Route::get('/', function () {
        return view('mechanic.dashboard');
    });
    Route::get('/jobs', function () {
        return view('mechanic.jobs');
    });
    Route::get('/notes', function () {
        return view('mechanic.notes');
    });
});
