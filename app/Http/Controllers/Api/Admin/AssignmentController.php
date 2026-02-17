<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Http\Requests\Api\Admin\AssignmentRequest;
use App\Http\Requests\Api\Admin\AssignmentSubmissionRequest;
use App\Http\Resources\Api\Admin\AssignmentResource;
use App\Http\Resources\Api\Admin\AssignmentSubmissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $assignments = Assignment::query()
            ->with(['course', 'lesson'])
            ->withCount('submissions')
            ->when($request->search, fn($q, $search) => 
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            )
            ->when($request->course_id, fn($q, $courseId) => $q->where('course_id', $courseId))
            ->when($request->lesson_id, fn($q, $lessonId) => $q->where('lesson_id', $lessonId))
            ->when($request->has('is_published'), function ($q) use ($request) {
                $q->where('is_published', $request->is_published === 'true' || $request->is_published === '1');
            })
            ->when($request->has('is_required'), function ($q) use ($request) {
                $q->where('is_required', $request->is_required === 'true' || $request->is_required === '1');
            })
            ->when($request->status === 'overdue', fn($q) => $q->overdue())
            ->when($request->status === 'due_soon', fn($q) => $q->dueSoon())
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10);

        return AssignmentResource::collection($assignments);
    }

    /**
     * Get all assignments for a specific course.
     */
    public function byCourse(Course $course): AnonymousResourceCollection
    {
        $assignments = Assignment::query()
            ->where('course_id', $course->id)
            ->with(['lesson'])
            ->withCount('submissions')
            ->orderBy('order')
            ->get();

        return AssignmentResource::collection($assignments);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(AssignmentRequest $request): JsonResponse
    {
        $assignment = Assignment::create($request->validated());

        return response()->json([
            'message' => 'Assignment created successfully',
            'assignment' => new AssignmentResource($assignment->load(['course', 'lesson']))
        ], 201);
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment): AssignmentResource
    {
        return new AssignmentResource(
            $assignment->load(['course', 'lesson', 'submissions.user', 'submissions.gradedBy'])
        );
    }

    /**
     * Update the specified assignment.
     */
    public function update(AssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $assignment->update($request->validated());

        return response()->json([
            'message' => 'Assignment updated successfully',
            'assignment' => new AssignmentResource($assignment->load(['course', 'lesson']))
        ]);
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Assignment $assignment): JsonResponse
    {
        // Check if there are any graded submissions
        $hasGradedSubmissions = $assignment->submissions()
            ->where('status', 'graded')
            ->exists();

        if ($hasGradedSubmissions) {
            return response()->json([
                'message' => 'Cannot delete assignment with graded submissions. Consider unpublishing instead.'
            ], 422);
        }

        $assignment->delete();

        return response()->json([
            'message' => 'Assignment deleted successfully'
        ]);
    }

    /**
     * Toggle published status.
     */
    public function togglePublished(Assignment $assignment): JsonResponse
    {
        $assignment->update(['is_published' => !$assignment->is_published]);

        return response()->json([
            'message' => 'Published status updated successfully',
            'assignment' => new AssignmentResource($assignment)
        ]);
    }

    /**
     * Toggle required status.
     */
    public function toggleRequired(Assignment $assignment): JsonResponse
    {
        $assignment->update(['is_required' => !$assignment->is_required]);

        return response()->json([
            'message' => 'Required status updated successfully',
            'assignment' => new AssignmentResource($assignment)
        ]);
    }

    /**
     * Reorder assignments.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.id' => 'required|exists:assignments,id',
            'assignments.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->assignments as $item) {
            Assignment::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Assignments reordered successfully'
        ]);
    }

    /**
     * Get assignment statistics.
     */
    public function statistics(Assignment $assignment): JsonResponse
    {
        $totalSubmissions = $assignment->submissions()->count();
        $submittedCount = $assignment->submissions()->where('status', '!=', 'draft')->count();
        $gradedCount = $assignment->submissions()->where('status', 'graded')->count();
        $needsGradingCount = $assignment->submissions()->needsGrading()->count();
        $passedCount = $assignment->submissions()
            ->where('status', 'graded')
            ->where('points_earned', '>=', $assignment->passing_points)
            ->count();
        $lateSubmissions = $assignment->submissions()->where('is_late', true)->count();

        $averageScore = $assignment->submissions()
            ->where('status', 'graded')
            ->avg('points_earned');

        return response()->json([
            'total_submissions' => $totalSubmissions,
            'submitted_count' => $submittedCount,
            'graded_count' => $gradedCount,
            'needs_grading_count' => $needsGradingCount,
            'passed_count' => $passedCount,
            'failed_count' => $gradedCount - $passedCount,
            'late_submissions' => $lateSubmissions,
            'average_score' => round($averageScore ?? 0, 2),
            'pass_rate' => $gradedCount > 0 ? round(($passedCount / $gradedCount) * 100, 2) : 0,
        ]);
    }

    /**
     * Get all submissions for an assignment.
     */
    public function submissions(Assignment $assignment, Request $request): AnonymousResourceCollection
    {
        $submissions = $assignment->submissions()
            ->with(['user', 'enrollment', 'gradedBy'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => 
                $q->whereHas('user', fn($uq) => 
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->orderBy($request->sort_by ?? 'submitted_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10);

        return AssignmentSubmissionResource::collection($submissions);
    }

    /**
     * Grade a submission.
     */
    public function gradeSubmission(
        AssignmentSubmissionRequest $request, 
        Assignment $assignment,
        AssignmentSubmission $submission
    ): JsonResponse {
        // Verify submission belongs to assignment
        if ($submission->assignment_id !== $assignment->id) {
            return response()->json([
                'message' => 'Submission does not belong to this assignment'
            ], 422);
        }

        $validated = $request->validate([
            'points_earned' => 'required|numeric|min:0|max:' . $assignment->total_points,
            'feedback' => 'nullable|string',
            'inline_comments' => 'nullable|array',
        ]);

        $submission->grade(
            $validated['points_earned'],
            $validated['feedback'] ?? '',
            auth()->user()
        );

        if (isset($validated['inline_comments'])) {
            $submission->update(['inline_comments' => $validated['inline_comments']]);
        }

        return response()->json([
            'message' => 'Submission graded successfully',
            'submission' => new AssignmentSubmissionResource($submission->load(['user', 'gradedBy']))
        ]);
    }

    /**
     * Request resubmission.
     */
    public function requestResubmission(
        Request $request,
        Assignment $assignment,
        AssignmentSubmission $submission
    ): JsonResponse {
        // Verify submission belongs to assignment
        if ($submission->assignment_id !== $assignment->id) {
            return response()->json([
                'message' => 'Submission does not belong to this assignment'
            ], 422);
        }

        $validated = $request->validate([
            'feedback' => 'required|string',
        ]);

        $submission->requestResubmission($validated['feedback']);

        return response()->json([
            'message' => 'Resubmission requested successfully',
            'submission' => new AssignmentSubmissionResource($submission->load(['user']))
        ]);
    }

    /**
     * Bulk grade submissions.
     */
    public function bulkGrade(Request $request, Assignment $assignment): JsonResponse
    {
        $validated = $request->validate([
            'submissions' => 'required|array',
            'submissions.*.id' => 'required|exists:assignment_submissions,id',
            'submissions.*.points_earned' => 'required|numeric|min:0|max:' . $assignment->total_points,
            'submissions.*.feedback' => 'nullable|string',
        ]);

        $graded = 0;
        foreach ($validated['submissions'] as $item) {
            $submission = AssignmentSubmission::find($item['id']);
            
            if ($submission->assignment_id === $assignment->id) {
                $submission->grade(
                    $item['points_earned'],
                    $item['feedback'] ?? '',
                    auth()->user()
                );
                $graded++;
            }
        }

        return response()->json([
            'message' => "{$graded} submissions graded successfully",
            'graded_count' => $graded
        ]);
    }

    /**
     * Export assignment submissions.
     */
    public function exportSubmissions(Assignment $assignment): JsonResponse
    {
        $submissions = $assignment->submissions()
            ->with(['user', 'enrollment'])
            ->get()
            ->map(function ($submission) {
                return [
                    'student_name' => $submission->user->name,
                    'student_email' => $submission->user->email,
                    'submission_number' => $submission->submission_number,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                    'is_late' => $submission->is_late ? 'Yes' : 'No',
                    'points_earned' => $submission->points_earned,
                    'score_percentage' => $submission->getScorePercentage(),
                    'passed' => $submission->isPassed() ? 'Yes' : 'No',
                    'graded_at' => $submission->graded_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'assignment' => [
                'title' => $assignment->title,
                'total_points' => $assignment->total_points,
                'passing_points' => $assignment->passing_points,
            ],
            'submissions' => $submissions
        ]);
    }
}
