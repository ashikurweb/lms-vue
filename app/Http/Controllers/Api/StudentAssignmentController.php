<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Http\Requests\Api\Admin\AssignmentSubmissionRequest;
use App\Http\Resources\Api\Admin\AssignmentResource;
use App\Http\Resources\Api\Admin\AssignmentSubmissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentAssignmentController extends Controller
{
    /**
     * Get all assignments for enrolled courses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = auth()->user();
        
        // Get enrolled course IDs
        $enrolledCourseIds = $user->student_enrollments()
            ->where('status', 'active')
            ->pluck('course_id');

        $assignments = Assignment::query()
            ->whereIn('course_id', $enrolledCourseIds)
            ->published()
            ->with(['course', 'lesson'])
            ->when($request->course_id, fn($q, $courseId) => $q->where('course_id', $courseId))
            ->when($request->status === 'pending', function ($q) use ($user) {
                $q->whereDoesntHave('submissions', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id)
                       ->where('status', '!=', 'draft');
                });
            })
            ->when($request->status === 'submitted', function ($q) use ($user) {
                $q->whereHas('submissions', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id)
                       ->whereIn('status', ['submitted', 'grading']);
                });
            })
            ->when($request->status === 'graded', function ($q) use ($user) {
                $q->whereHas('submissions', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id)
                       ->where('status', 'graded');
                });
            })
            ->when($request->status === 'overdue', fn($q) => $q->overdue())
            ->orderBy('due_date', 'asc')
            ->paginate($request->per_page ?? 10);

        // Add user submission status to each assignment
        $assignments->through(function ($assignment) use ($user) {
            $submission = $assignment->getUserSubmission($user);
            $resource = new AssignmentResource($assignment);
            $data = $resource->toArray(request());
            
            $data['user_submission'] = $submission ? new AssignmentSubmissionResource($submission) : null;
            $data['can_submit'] = $assignment->canSubmit($user);
            $data['late_penalty'] = $assignment->getLatePenaltyFor($user);
            
            return $data;
        });

        return AssignmentResource::collection($assignments);
    }

    /**
     * Get a specific assignment with user's submission.
     */
    public function show(Assignment $assignment): JsonResponse
    {
        $user = auth()->user();

        // Check if user is enrolled in the course
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        if (!$assignment->is_published) {
            return response()->json([
                'message' => 'This assignment is not available'
            ], 404);
        }

        $assignment->load(['course', 'lesson']);
        $submission = $assignment->getUserSubmission($user);

        $data = new AssignmentResource($assignment);
        $responseData = $data->toArray(request());
        
        $responseData['user_submission'] = $submission ? new AssignmentSubmissionResource($submission) : null;
        $responseData['can_submit'] = $assignment->canSubmit($user);
        $responseData['late_penalty'] = $assignment->getLatePenaltyFor($user);
        $responseData['enrollment_id'] = $enrollment->id;

        return response()->json($responseData);
    }

    /**
     * Create or update a draft submission.
     */
    public function saveDraft(AssignmentSubmissionRequest $request, Assignment $assignment): JsonResponse
    {
        $user = auth()->user();

        // Check enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Get or create draft submission
        $submission = AssignmentSubmission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'status' => 'draft',
            ],
            [
                'enrollment_id' => $enrollment->id,
                'submission_number' => $assignment->submissions()
                    ->where('user_id', $user->id)
                    ->max('submission_number') + 1,
            ]
        );

        $submission->update([
            'content' => $request->input('content'),
            'files' => $request->input('files'),
        ]);

        return response()->json([
            'message' => 'Draft saved successfully',
            'submission' => new AssignmentSubmissionResource($submission)
        ]);
    }

    /**
     * Submit an assignment.
     */
    public function submit(AssignmentSubmissionRequest $request, Assignment $assignment): JsonResponse
    {
        $user = auth()->user();

        // Check enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Check if can submit
        if (!$assignment->canSubmit($user)) {
            return response()->json([
                'message' => 'You cannot submit this assignment. Maximum submissions reached or deadline passed.'
            ], 422);
        }

        // Validate content or files
        if (empty($request->input('content')) && empty($request->input('files'))) {
            return response()->json([
                'message' => 'Please provide content or upload files before submitting'
            ], 422);
        }

        // Get or create submission
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if (!$submission) {
            $submissionNumber = $assignment->submissions()
                ->where('user_id', $user->id)
                ->max('submission_number') + 1;

            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'submission_number' => $submissionNumber,
                'content' => $request->input('content'),
                'files' => $request->input('files'),
            ]);
        } else {
            $submission->update([
                'content' => $request->input('content'),
                'files' => $request->input('files'),
            ]);
        }

        // Submit the assignment
        $submission->submit();

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'submission' => new AssignmentSubmissionResource($submission->load('assignment'))
        ], 201);
    }

    /**
     * Get user's submissions for an assignment.
     */
    public function mySubmissions(Assignment $assignment): AnonymousResourceCollection
    {
        $user = auth()->user();

        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->with(['assignment', 'gradedBy'])
            ->orderBy('submission_number', 'desc')
            ->get();

        return AssignmentSubmissionResource::collection($submissions);
    }

    /**
     * Get a specific submission.
     */
    public function getSubmission(Assignment $assignment, AssignmentSubmission $submission): JsonResponse
    {
        $user = auth()->user();

        // Verify ownership
        if ($submission->user_id !== $user->id || $submission->assignment_id !== $assignment->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json(
            new AssignmentSubmissionResource($submission->load(['assignment', 'gradedBy']))
        );
    }

    /**
     * Delete a draft submission.
     */
    public function deleteDraft(Assignment $assignment, AssignmentSubmission $submission): JsonResponse
    {
        $user = auth()->user();

        // Verify ownership and draft status
        if ($submission->user_id !== $user->id || $submission->assignment_id !== $assignment->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$submission->isDraft()) {
            return response()->json([
                'message' => 'Only draft submissions can be deleted'
            ], 422);
        }

        $submission->delete();

        return response()->json([
            'message' => 'Draft deleted successfully'
        ]);
    }

    /**
     * Get assignment statistics for the student.
     */
    public function myStatistics(): JsonResponse
    {
        $user = auth()->user();

        $enrolledCourseIds = $user->student_enrollments()
            ->where('status', 'active')
            ->pluck('course_id');

        $totalAssignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->published()
            ->count();

        $submittedCount = AssignmentSubmission::where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->distinct('assignment_id')
            ->count('assignment_id');

        $gradedCount = AssignmentSubmission::where('user_id', $user->id)
            ->where('status', 'graded')
            ->count();

        $passedCount = AssignmentSubmission::where('user_id', $user->id)
            ->where('status', 'graded')
            ->whereRaw('points_earned >= (SELECT passing_points FROM assignments WHERE id = assignment_submissions.assignment_id)')
            ->count();

        $averageScore = AssignmentSubmission::where('user_id', $user->id)
            ->where('status', 'graded')
            ->avg('points_earned');

        $overdueCount = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->published()
            ->overdue()
            ->whereDoesntHave('submissions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', '!=', 'draft');
            })
            ->count();

        return response()->json([
            'total_assignments' => $totalAssignments,
            'submitted_count' => $submittedCount,
            'pending_count' => $totalAssignments - $submittedCount,
            'graded_count' => $gradedCount,
            'passed_count' => $passedCount,
            'failed_count' => $gradedCount - $passedCount,
            'overdue_count' => $overdueCount,
            'average_score' => round($averageScore ?? 0, 2),
            'pass_rate' => $gradedCount > 0 ? round(($passedCount / $gradedCount) * 100, 2) : 0,
        ]);
    }
}
