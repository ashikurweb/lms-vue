<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
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
            'quiz_id' => $this->quiz_id,
            'type' => $this->type,
            'question' => $this->question,
            'explanation' => $this->explanation,
            'image' => $this->image,
            'audio' => $this->audio,
            'video' => $this->video,
            'points' => $this->points,
            'order' => $this->order,
            'is_required' => $this->is_required,
            'settings' => $this->settings,
            'options' => $this->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'feedback' => $option->feedback,
                    'image' => $option->image,
                    'order' => $option->order,
                    'match_with' => $option->match_with,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
