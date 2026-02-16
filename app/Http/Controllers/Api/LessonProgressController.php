<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LessonProgressController extends Controller
{
    /**
     * Save or update lesson progress.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'watch_time' => 'required|numeric',
            'last_position' => 'required|numeric',
            'progress_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user is enrolled in the course this lesson belongs to
        $lesson = Lesson::findOrFail($request->lesson_id);
        $isEnrolled = $user->student_enrollments()->where('course_id', $lesson->course_id)->exists();

        if (!$isEnrolled && !$lesson->is_free) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $request->lesson_id,
            ],
            [
                'watch_time' => $request->watch_time,
                'last_position' => $request->last_position,
                'progress_percentage' => $request->progress_percentage,
                'last_watched_at' => now(),
                'is_completed' => $request->progress_percentage >= 90, // Mark as completed if 90%+ watched
                'completed_at' => $request->progress_percentage >= 90 ? now() : null,
            ]
        );

        return response()->json([
            'message' => 'Progress saved successfully',
            'progress' => $progress
        ]);
    }

    /**
     * Get progress for a specific lesson.
     */
    public function show($lessonId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->first();

        return response()->json($progress);
    }
}
