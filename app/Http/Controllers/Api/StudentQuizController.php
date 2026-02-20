<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Api\Admin\QuizResource;
use App\Http\Resources\Api\Admin\QuizAttemptResource;

class StudentQuizController extends Controller
{
    /**
     * Start a new quiz attempt.
     */
    public function start(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        // Check if user is enrolled in the course
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        // Check if quiz is published
        if (!$quiz->is_published) {
            return response()->json(['message' => 'This quiz is not available.'], 404);
        }

        // Check max attempts
        if (!$quiz->canAttempt($user)) {
            return response()->json(['message' => 'You have reached the maximum number of attempts for this quiz.'], 403);
        }

        // Check if there's an in-progress attempt
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            // Check if existing attempt is expired
            $attemptNumber = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->count() + 1;

            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'enrollment_id' => $enrollment->id,
                'attempt_number' => $attemptNumber,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return response()->json([
            'attempt' => new QuizAttemptResource($attempt),
            'quiz' => new QuizResource($quiz->load('questions.options')),
            'questions' => $quiz->getQuestionsForAttempt(),
        ]);
    }

    /**
     * Save/Update answer for a question in an attempt.
     */
    public function saveAnswer(Request $request, QuizAttempt $attempt, QuizQuestion $question)
    {
        $user = $request->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized attempt access.'], 403);
        }

        if (!$attempt->isInProgress()) {
            return response()->json(['message' => 'Attempt is already submitted or expired.'], 403);
        }

        if ($attempt->isExpired()) {
            $attempt->update(['status' => 'expired']);
            return response()->json(['message' => 'Time limit exceeded. Attempt expired.'], 403);
        }

        $answer = QuizAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
            [
                'selected_options' => $request->selected_options,
                'text_answer' => $request->text_answer,
                'order_answer' => $request->order_answer,
                'matching_answer' => $request->matching_answer,
                'is_flagged' => $request->boolean('is_flagged', false),
            ]
        );

        return response()->json(['message' => 'Answer saved successfully', 'answer' => $answer]);
    }

    /**
     * Submit the quiz attempt.
     */
    public function submit(Request $request, QuizAttempt $attempt)
    {
        $user = $request->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized attempt access.'], 403);
        }

        if (!$attempt->isInProgress()) {
            return response()->json(['message' => 'Attempt is already submitted or expired.'], 403);
        }

        // Automatic grading for supported questions
        DB::transaction(function () use ($attempt) {
            foreach ($attempt->quiz->questions as $question) {
                $answer = QuizAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $question->id)
                    ->first();

                if ($answer) {
                    $result = $question->checkAnswer($question->isMCQ() ? $answer->selected_options : ($question->isTextBased() ? $answer->text_answer : null));
                    
                    $answer->update([
                        'is_correct' => $result['is_correct'],
                        'points_earned' => $result['points'],
                    ]);
                }
            }

            $attempt->submit();
        });

        return response()->json([
            'message' => 'Quiz submitted successfully',
            'attempt' => new QuizAttemptResource($attempt->fresh()),
        ]);
    }

    /**
     * Get list of quizzes available for the student (from enrolled courses).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('course_id');

        $quizzes = Quiz::whereIn('course_id', $enrolledCourseIds)
            ->published()
            ->with(['course'])
            ->withCount(['attempts' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->latest()
            ->paginate($request->input('per_page', 10));

        return QuizResource::collection($quizzes);
    }

    /**
     * Get summary of user's quizzes.
     */
    public function myQuizzes(Request $request)
    {
        $user = $request->user();
        
        $attempts = QuizAttempt::with(['quiz'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->input('per_page', 10));

        return QuizAttemptResource::collection($attempts);
    }

    /**
     * Get specific attempt details including results if allowed.
     */
    public function showAttempt(QuizAttempt $attempt)
    {
        $user = auth()->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized attempt access.'], 403);
        }

        $quiz = $attempt->quiz;
        $attempt->load(['answers.question.options']);

        $data = [
            'attempt' => new QuizAttemptResource($attempt),
            'quiz' => new QuizResource($quiz),
        ];

        // Only show detailed results if allowed or quiz is graded
        if ($quiz->show_answers_after_submission && ($attempt->isGraded() || $attempt->isSubmitted())) {
            $data['answers'] = $attempt->answers;
        }

        return response()->json($data);
    }
}
