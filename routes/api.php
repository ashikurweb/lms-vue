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
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Protected Routes (JWT required)
    Route::middleware('auth.jwt')->group(function () {
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('/toggle-2fa', [AuthController::class, 'toggle2fa']);
        Route::post('/verify-2fa', [AuthController::class, 'verify2fa']);

        // Routes that require verified email
        Route::middleware('verified.api')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });
});

// Public Blog Routes
Route::prefix('blog')->group(function () {
    Route::get('/posts', [\App\Http\Controllers\Api\BlogController::class, 'index']);
    Route::get('/posts/featured', [\App\Http\Controllers\Api\BlogController::class, 'featured']);
    Route::get('/posts/{slug}', [\App\Http\Controllers\Api\BlogController::class, 'show']);
    Route::get('/categories', [\App\Http\Controllers\Api\BlogController::class, 'categories']);
});

// Admin Routes
Route::middleware(['auth.jwt'])->prefix('admin')->group(function () {
    Route::get('/blog-categories/all', [\App\Http\Controllers\Api\Admin\BlogCategoryController::class, 'getAll']);
    Route::apiResource('blog-categories', \App\Http\Controllers\Api\Admin\BlogCategoryController::class);

    Route::get('/blog-tags/all', [\App\Http\Controllers\Api\Admin\BlogTagController::class, 'getAll']);
    Route::apiResource('blog-tags', \App\Http\Controllers\Api\Admin\BlogTagController::class);

    Route::apiResource('blog-posts', \App\Http\Controllers\Api\Admin\BlogPostController::class);
    Route::post('upload-image', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadImage']);
});
