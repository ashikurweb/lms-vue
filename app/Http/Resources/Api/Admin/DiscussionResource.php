<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscussionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'is_pinned' => (bool) $this->is_pinned,
            'is_featured' => (bool) $this->is_featured,
            'views_count' => $this->views_count,
            'replies_count' => $this->replies_count,
            'upvotes_count' => $this->upvotes_count,
            'followers_count' => $this->followers_count,
            'last_activity_at' => $this->last_activity_at,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'course' => $this->whenLoaded('course', fn() => [
                'id' => $this->course->id,
                'title' => $this->course->title,
            ]),
            'lesson' => $this->whenLoaded('lesson', fn() => [
                'id' => $this->lesson->id,
                'title' => $this->lesson->title,
            ]),
            'best_answer' => $this->whenLoaded('bestAnswer', fn() => [
                'id' => $this->bestAnswer->id,
                'content' => $this->bestAnswer->content,
                'user' => [
                    'id' => $this->bestAnswer->user->id,
                    'name' => $this->bestAnswer->user->name,
                ],
            ]),
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
