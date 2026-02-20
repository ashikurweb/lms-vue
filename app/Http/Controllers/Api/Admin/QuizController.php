<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
use App\Http\Requests\Api\Admin\QuizRequest;
use App\Http\Requests\Api\Admin\QuizQuestionRequest;
use App\Http\Resources\Api\Admin\QuizResource;
use App\Http\Resources\Api\Admin\QuizQuestionResource;
use App\Http\Resources\Api\Admin\QuizAttemptResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['course', 'lesson'])
            ->withCount('attempts');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'published':
                    $query->published();
                    break;
                case 'draft':
                    $query->where('is_published', false);
                    break;
                case 'required':
                    $query->required();
                    break;
            }
        }

        $quizzes = $query->latest()
            ->paginate($request->input('per_page', 10));

        return QuizResource::collection($quizzes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuizRequest $request)
    {
        $quiz = Quiz::create($request->validated());
        return new QuizResource($quiz);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        $quiz->load(['course', 'lesson', 'questions.options']);
        return new QuizResource($quiz);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuizRequest $request, Quiz $quiz)
    {
        $quiz->update($request->validated());
        return new QuizResource($quiz);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    /**
     * Toggle published status.
     */
    public function togglePublished(Quiz $quiz)
    {
        $quiz->update(['is_published' => !$quiz->is_published]);
        return response()->json([
            'message' => 'Published status updated',
            'is_published' => $quiz->is_published
        ]);
    }

    /**
     * Toggle required status.
     */
    public function toggleRequired(Quiz $quiz)
    {
        $quiz->update(['is_required' => !$quiz->is_required]);
        return response()->json([
            'message' => 'Required status updated',
            'is_required' => $quiz->is_required
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|exists:quizzes,id',
            'action' => 'required|in:delete,publish,unpublish'
        ]);

        $ids = $request->ids;
        $action = $request->action;

        switch ($action) {
            case 'delete':
                Quiz::whereIn('id', $ids)->delete();
                $message = 'Quizzes deleted successfully';
                break;
            case 'publish':
                Quiz::whereIn('id', $ids)->update(['is_published' => true]);
                $message = 'Quizzes published successfully';
                break;
            case 'unpublish':
                Quiz::whereIn('id', $ids)->update(['is_published' => false]);
                $message = 'Quizzes unpublished successfully';
                break;
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Get statistics for a specific quiz.
     */
    public function statistics(Quiz $quiz)
    {
        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'passed_count' => $quiz->attempts()->passed()->count(),
            'failed_count' => $quiz->attempts()->where('is_passed', false)->where('status', 'graded')->count(),
            'average_score' => round($quiz->attempts()->where('status', 'graded')->avg('percentage') ?? 0, 2),
            'unique_students' => $quiz->attempts()->distinct('user_id')->count(),
            'completion_rate' => 0,
            'needs_grading' => $quiz->attempts()->submitted()->count(),
        ];

        if ($stats['total_attempts'] > 0) {
            $stats['completion_rate'] = round(($stats['passed_count'] / $stats['total_attempts']) * 100, 2);
        }

        return response()->json($stats);
    }

    /**
     * Get attempts for a quiz.
     */
    public function attempts(Request $request, Quiz $quiz)
    {
        $attempts = $quiz->attempts()
            ->with(['user'])
            ->latest()
            ->paginate($request->input('per_page', 15));

        return QuizAttemptResource::collection($attempts);
    }

    /**
     * Add/Update questions for a quiz.
     */
    public function saveQuestion(QuizQuestionRequest $request, Quiz $quiz, QuizQuestion $question = null)
    {
        return DB::transaction(function () use ($request, $quiz, $question) {
            $validated = $request->validated();
            $optionsData = $validated['options'] ?? [];
            unset($validated['options']);

            if ($question) {
                $question->update($validated);
            } else {
                $validated['quiz_id'] = $quiz->id;
                $question = QuizQuestion::create($validated);
            }

            // Sync options
            if (!empty($optionsData)) {
                // Delete old options
                $question->options()->delete();

                // Create new options
                foreach ($optionsData as $option) {
                    $question->options()->create($option);
                }
            }

            $quiz->updateStats();

            return new QuizQuestionResource($question->load('options'));
        });
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        $quiz->updateStats();
        return response()->json(['message' => 'Question deleted successfully']);
    }
}
