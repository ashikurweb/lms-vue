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
        });
    });

    // Refresh Token (must be outside auth.jwt to allow expired tokens)
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Public Blog Routes
Route::prefix('blog')->group(function () {
    Route::get('/posts', [\App\Http\Controllers\Api\BlogController::class, 'index']);
    Route::get('/posts/featured', [\App\Http\Controllers\Api\BlogController::class, 'featured']);
    Route::get('/posts/{slug}', [\App\Http\Controllers\Api\BlogController::class, 'show']);
    Route::get('/categories', [\App\Http\Controllers\Api\BlogController::class, 'categories']);
    Route::post('/posts/{slug}/share', [\App\Http\Controllers\Api\BlogController::class, 'share']);
});

// Authenticated Blog Routes
Route::middleware(['auth.jwt'])->prefix('blog')->group(function () {
    Route::post('/posts/{slug}/like', [\App\Http\Controllers\Api\BlogController::class, 'like']);
    Route::post('/posts/{slug}/comment', [\App\Http\Controllers\Api\BlogController::class, 'comment']);
});

// Admin Routes
Route::middleware(['auth.jwt', 'admin.role'])->prefix('admin')->group(function () {
    Route::get('/categories/all', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'getAll']);
    Route::apiResource('categories', \App\Http\Controllers\Api\Admin\CategoryController::class);
    Route::patch('/categories/{category}/toggle-featured', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'toggleFeatured']);
    Route::patch('/categories/{category}/toggle-status', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'toggleStatus']);

    Route::get('/blog-categories/all', [\App\Http\Controllers\Api\Admin\BlogCategoryController::class, 'getAll']);
    Route::apiResource('blog-categories', \App\Http\Controllers\Api\Admin\BlogCategoryController::class);
    Route::patch('/blog-categories/{blogCategory}/toggle-featured', [\App\Http\Controllers\Api\Admin\BlogCategoryController::class, 'toggleFeatured']);
    Route::patch('/blog-categories/{blogCategory}/toggle-status', [\App\Http\Controllers\Api\Admin\BlogCategoryController::class, 'toggleStatus']);

    Route::get('/blog-tags/all', [\App\Http\Controllers\Api\Admin\BlogTagController::class, 'getAll']);
    Route::apiResource('blog-tags', \App\Http\Controllers\Api\Admin\BlogTagController::class);

    Route::apiResource('blog-posts', \App\Http\Controllers\Api\Admin\BlogPostController::class);

    // User/Student Routes
    Route::apiResource('student', \App\Http\Controllers\Api\Admin\UserController::class)->parameters([
        'student' => 'user'
    ]);
    Route::patch('/student/{user}/toggle-status', [\App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);

    // Instructor Routes
    Route::apiResource('instructors', \App\Http\Controllers\Api\Admin\InstructorController::class);
    Route::patch('/instructors/{instructor}/toggle-featured', [\App\Http\Controllers\Api\Admin\InstructorController::class, 'toggleFeatured']);
    Route::patch('/instructors/{instructor}/toggle-status', [\App\Http\Controllers\Api\Admin\InstructorController::class, 'toggleStatus']);

    // Payout Routes
    Route::apiResource('payouts', \App\Http\Controllers\Api\Admin\PayoutController::class);

    Route::post('upload-image', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadImage']);

    // Currency Routes
    Route::apiResource('currencies', \App\Http\Controllers\API\CurrencyController::class);
    Route::patch('/currencies/{currency}/toggle-status', [\App\Http\Controllers\API\CurrencyController::class, 'toggleStatus']);
    Route::patch('/currencies/{currency}/set-default', [\App\Http\Controllers\API\CurrencyController::class, 'setDefault']);
    Route::get('/currencies/default', [\App\Http\Controllers\API\CurrencyController::class, 'getDefault']);
    Route::get('/currencies/active', [\App\Http\Controllers\API\CurrencyController::class, 'getActive']);
    
    // Multi-Currency Routes
    Route::post('/currencies/convert', [\App\Http\Controllers\API\CurrencyController::class, 'convert']);
    Route::post('/currencies/format', [\App\Http\Controllers\API\CurrencyController::class, 'format']);
    Route::get('/currencies/exchange-rates', [\App\Http\Controllers\API\CurrencyController::class, 'exchangeRates']);
    Route::post('/currencies/set-user-currency', [\App\Http\Controllers\API\CurrencyController::class, 'setUserCurrency']);
    Route::get('/currencies/get-user-currency', [\App\Http\Controllers\API\CurrencyController::class, 'getUserCurrency']);
    Route::post('/currencies/clear-cache', [\App\Http\Controllers\API\CurrencyController::class, 'clearCache']);
    
    // Settings Routes
    Route::get('/settings', [\App\Http\Controllers\Api\Admin\SettingController::class, 'index']);
    Route::get('/settings/general', [\App\Http\Controllers\Api\Admin\SettingController::class, 'general']);
    Route::get('/settings/group/{group}', [\App\Http\Controllers\Api\Admin\SettingController::class, 'group']);
    Route::get('/settings/timezones', [\App\Http\Controllers\Api\Admin\SettingController::class, 'timezones']);
    Route::post('/settings', [\App\Http\Controllers\Api\Admin\SettingController::class, 'update']);
    Route::post('/settings/single', [\App\Http\Controllers\Api\Admin\SettingController::class, 'updateSingle']);

    // Admin Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Api\Admin\AdminProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\Admin\AdminProfileController::class, 'update']);
});
