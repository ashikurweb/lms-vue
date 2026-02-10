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

    Route::get('/discussions/all', [\App\Http\Controllers\Api\Admin\DiscussionController::class, 'getAll']);
    Route::apiResource('discussions', \App\Http\Controllers\Api\Admin\DiscussionController::class);
    Route::patch('/discussions/{discussion}/toggle-featured', [\App\Http\Controllers\Api\Admin\DiscussionController::class, 'toggleFeatured']);
    Route::patch('/discussions/{discussion}/toggle-pinned', [\App\Http\Controllers\Api\Admin\DiscussionController::class, 'togglePinned']);
    Route::patch('/discussions/{discussion}/toggle-status', [\App\Http\Controllers\Api\Admin\DiscussionController::class, 'toggleStatus']);

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

    // Admin Upload Routes
    Route::post('/upload-image', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadImage']);
    Route::post('/upload-video', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadVideo']);

    // Currency Routes
    Route::get('/currencies/default', [\App\Http\Controllers\API\CurrencyController::class, 'getDefault']);
    Route::get('/currencies/active', [\App\Http\Controllers\API\CurrencyController::class, 'getActive']);
    Route::apiResource('currencies', \App\Http\Controllers\API\CurrencyController::class);
    Route::patch('/currencies/{currency}/toggle-status', [\App\Http\Controllers\API\CurrencyController::class, 'toggleStatus']);
    Route::patch('/currencies/{currency}/set-default', [\App\Http\Controllers\API\CurrencyController::class, 'setDefault']);
    
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

    // Course Routes
    Route::apiResource('courses', \App\Http\Controllers\Api\Admin\CourseController::class);
    Route::patch('/courses/{course}/toggle-featured', [\App\Http\Controllers\Api\Admin\CourseController::class, 'toggleFeatured']);
    Route::patch('/courses/{course}/toggle-status', [\App\Http\Controllers\Api\Admin\CourseController::class, 'toggleStatus']);

    // Course Curriculum Routes
    Route::get('/lessons', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'lessonsIndex']);
    Route::get('/courses/{course}/curriculum', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'index']);
    Route::post('/courses/{course}/sections', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'storeSection']);
    Route::post('/sections/{section}/lessons', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'storeLesson']);
    Route::put('/sections/{section}', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'updateSection']);
    Route::delete('/sections/{section}', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'destroySection']);
    Route::put('/lessons/{lesson}', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'updateLesson']);
    Route::delete('/lessons/{lesson}', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'destroyLesson']);
    Route::post('/curriculum/reorder-sections', [\App\Http\Controllers\Api\Admin\CourseCurriculumController::class, 'reorderSections']);

    // Lesson Resources (Downloadable Files)
    Route::get('/lessons/{lesson}/resources', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'resourceIndex']);
    Route::post('/lessons/{lesson}/resources', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'resourceStore']);
    Route::put('/lesson-resources/{resource}', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'resourceUpdate']);
    Route::delete('/lesson-resources/{resource}', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'resourceDestroy']);

    // Video Tracks (Subtitles/Captions)
    Route::get('/lessons/{lesson}/tracks', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'trackIndex']);
    Route::post('/lessons/{lesson}/tracks', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'trackStore']);
    Route::put('/video-tracks/{track}', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'trackUpdate']);
    Route::delete('/video-tracks/{track}', [\App\Http\Controllers\Api\Admin\LessonAttachmentController::class, 'trackDestroy']);

    // Admin Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Api\Admin\AdminProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\Admin\AdminProfileController::class, 'update']);
});
