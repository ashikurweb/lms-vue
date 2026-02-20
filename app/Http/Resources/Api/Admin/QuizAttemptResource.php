<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
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
            'user_id' => $this->user_id,
            'quiz_id' => $this->quiz_id,
            'enrollment_id' => $this->enrollment_id,
            'attempt_number' => $this->attempt_number,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'time_spent' => $this->time_spent,
            'time_spent_formatted' => $this->getTimeSpentFormatted(),
            'total_questions' => $this->total_questions,
            'answered_questions' => $this->answered_questions,
            'correct_answers' => $this->correct_answers,
            'wrong_answers' => $this->wrong_answers,
            'skipped_questions' => $this->skipped_questions,
            'score_earned' => $this->score_earned,
            'score_total' => $this->score_total,
            'percentage' => $this->percentage,
            'is_passed' => $this->is_passed,
            'status' => $this->status,
            'feedback' => $this->feedback,
            'graded_at' => $this->graded_at,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->avatar,
            ],
            'quiz' => [
                'id' => $this->quiz?->id,
                'title' => $this->quiz?->title,
                'passing_score' => $this->quiz?->passing_score,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
