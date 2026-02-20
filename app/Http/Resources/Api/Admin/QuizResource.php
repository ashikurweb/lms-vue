<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
            'time_limit' => $this->time_limit,
            'passing_score' => $this->passing_score,
            'max_attempts' => $this->max_attempts,
            'show_answers_after_submission' => $this->show_answers_after_submission,
            'show_correct_answers' => $this->show_correct_answers,
            'randomize_questions' => $this->randomize_questions,
            'randomize_options' => $this->randomize_options,
            'questions_per_page' => $this->questions_per_page,
            'allow_review' => $this->allow_review,
            'is_required' => $this->is_required,
            'total_points' => $this->total_points,
            'total_questions' => $this->total_questions,
            'is_published' => $this->is_published,
            'order' => $this->order,
            'meta' => $this->meta,
            'course' => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
                'slug' => $this->course?->slug,
            ],
            'lesson' => [
                'id' => $this->lesson?->id,
                'title' => $this->lesson?->title,
            ],
            'questions' => QuizQuestionResource::collection($this->whenLoaded('questions')),
            'attempts_count' => $this->attempts_count ?? $this->attempts()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
