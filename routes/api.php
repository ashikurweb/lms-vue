<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes (JWT required)
    Route::middleware('auth.jwt')->group(function () {
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

        // Routes that require verified email
        Route::middleware('verified.api')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });
});
