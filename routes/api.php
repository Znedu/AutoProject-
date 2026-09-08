<?php

use App\Http\Controllers\Api\TxtFlowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TxtFlow Android SMS Gateway API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group (no session, no CSRF, no cookies).
|
| Endpoints polled by the TxtFlow Android app to fetch pending outgoing
| SMS messages and report delivery status or incoming customer replies.
|
| Supports:
|   1. Root-level: https://domain.com/api/txtflow
|   2. Token-in-path: https://domain.com/api/txtflow/{token}
|
*/

// Standard /api/txtflow prefix
Route::prefix('txtflow')
    ->middleware(['txtflow.token'])
    ->name('api.txtflow.')
    ->group(function (): void {
        Route::get('health-check', [TxtFlowController::class, 'healthCheck'])->name('health-check');
        Route::get('messages', [TxtFlowController::class, 'messages'])->name('messages');
        Route::post('message', [TxtFlowController::class, 'receiveMessage'])->name('message');
        Route::post('cron/clean', [TxtFlowController::class, 'clean'])->name('clean');
        Route::post('broadcast', [TxtFlowController::class, 'broadcast'])->name('broadcast');
    });

// Token-in-path: /api/txtflow/{token} (enables entering token directly in Server URL)
Route::prefix('txtflow/{token}')
    ->middleware(['txtflow.token'])
    ->group(function (): void {
        Route::get('health-check', [TxtFlowController::class, 'healthCheck']);
        Route::get('messages', [TxtFlowController::class, 'messages']);
        Route::post('message', [TxtFlowController::class, 'receiveMessage']);
        Route::post('cron/clean', [TxtFlowController::class, 'clean']);
        Route::post('broadcast', [TxtFlowController::class, 'broadcast']);
    });
