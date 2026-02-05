<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasSlug;

class BlogTag extends Model
{
    use HasFactory, HasSlug;

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    // Relationships
    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag', 'tag_id', 'post_id');
    }

    // Scopes
    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('posts_count', 'desc')->limit($limit);
    }

    // Methods
    public function updatePostsCount()
    {
        $this->update(['posts_count' => $this->posts()->published()->count()]);
    }
}
