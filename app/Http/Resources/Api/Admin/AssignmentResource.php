<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'total_points' => $this->total_points,
            'passing_points' => $this->passing_points,
            'due_date' => $this->due_date,
            'allow_late_submission' => (bool) $this->allow_late_submission,
            'late_submission_penalty' => $this->late_submission_penalty,
            'max_file_size' => $this->max_file_size,
            'allowed_file_types' => $this->allowed_file_types,
            'allowed_file_types_formatted' => $this->getAllowedFileTypesFormatted(),
            'max_submissions' => $this->max_submissions,
            'is_required' => (bool) $this->is_required,
            'is_published' => (bool) $this->is_published,
            'order' => $this->order,
            'attachments' => $this->attachments,
            'meta' => $this->meta,
            'is_overdue' => $this->isOverdue(),
            'time_remaining' => $this->getTimeRemaining(),
            
            // Relationships
            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                ];
            }),
            'lesson' => $this->whenLoaded('lesson', function () {
                return [
                    'id' => $this->lesson->id,
                    'title' => $this->lesson->title,
                ];
            }),
            'submissions_count' => $this->when(isset($this->submissions_count), $this->submissions_count),
            'submissions' => AssignmentSubmissionResource::collection($this->whenLoaded('submissions')),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
