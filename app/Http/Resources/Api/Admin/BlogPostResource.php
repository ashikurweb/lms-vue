<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'video_url' => $this->video_url,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'is_pinned' => (bool) $this->is_pinned,
            'allow_comments' => (bool) $this->allow_comments,
            'reading_time' => $this->reading_time,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'shares_count' => $this->shares_count,
            'author' => $this->whenLoaded('author', function() {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
            'category' => new BlogCategoryResource($this->whenLoaded('category')),
            'tags' => BlogTagResource::collection($this->whenLoaded('tags')),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'canonical_url' => $this->canonical_url,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
