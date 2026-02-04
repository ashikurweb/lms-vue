<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'thumbnail',
        'video_url',
        'status',
        'is_featured',
        'is_pinned',
        'allow_comments',
        'reading_time',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'published_at',
        'scheduled_at',
        'meta',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'reading_time' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (empty($post->uuid)) {
                $post->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'post_id', 'tag_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }

    public function approvedComments()
    {
        return $this->comments()->where('status', 'approved');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function getFormattedReadingTimeAttribute()
    {
        return $this->reading_time . ' min read';
    }

    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }
}
