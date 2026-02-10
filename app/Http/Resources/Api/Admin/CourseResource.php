<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'thumbnail' => $this->thumbnail,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'level' => $this->level,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'instructor' => [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
            ],
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'total_sections' => $this->total_sections,
            'total_lectures' => $this->total_lectures,
            'duration' => $this->getDurationFormatted(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
