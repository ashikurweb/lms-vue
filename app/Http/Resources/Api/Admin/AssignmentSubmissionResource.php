<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'assignment_id' => $this->assignment_id,
            'user_id' => $this->user_id,
            'enrollment_id' => $this->enrollment_id,
            'submission_number' => $this->submission_number,
            'content' => $this->content,
            'files' => $this->files,
            'status' => $this->status,
            'points_earned' => $this->points_earned,
            'is_late' => (bool) $this->is_late,
            'late_penalty_applied' => $this->late_penalty_applied,
            'feedback' => $this->feedback,
            'inline_comments' => $this->inline_comments,
            'graded_by' => $this->graded_by,
            'graded_at' => $this->graded_at,
            'submitted_at' => $this->submitted_at,
            
            // Helper fields
            'is_draft' => $this->isDraft(),
            'is_submitted' => $this->isSubmitted(),
            'is_graded' => $this->isGraded(),
            'is_passed' => $this->isPassed(),
            'score_percentage' => $this->getScorePercentage(),
            
            // Relationships
            'assignment' => $this->whenLoaded('assignment', function () {
                return [
                    'id' => $this->assignment->id,
                    'title' => $this->assignment->title,
                    'total_points' => $this->assignment->total_points,
                    'passing_points' => $this->assignment->passing_points,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
                ];
            }),
            'grader' => $this->whenLoaded('gradedBy', function () {
                return [
                    'id' => $this->gradedBy->id,
                    'name' => $this->gradedBy->name,
                ];
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
