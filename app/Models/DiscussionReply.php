<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DiscussionReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
        'is_best_answer',
        'is_instructor_reply',
        'upvotes_count',
        'replies_count',
        'status',
        'meta',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
        'is_instructor_reply' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $reply->discussion->updateActivity();
        });
    }

    // ========== Relationships ==========

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'parent_id');
    }

    public function upvotes(): MorphMany
    {
        return $this->morphMany(DiscussionUpvote::class, 'upvoteable');
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBestAnswer($query)
    {
        return $query->where('is_best_answer', true);
    }

    public function scopeInstructorReplies($query)
    {
        return $query->where('is_instructor_reply', true);
    }

    // ========== Helpers ==========

    public function upvote(User $user): bool
    {
        if ($this->upvotes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $this->upvotes()->create(['user_id' => $user->id]);
        $this->increment('upvotes_count');

        return true;
    }

    public function removeUpvote(User $user): bool
    {
        $upvote = $this->upvotes()->where('user_id', $user->id)->first();

        if (!$upvote) {
            return false;
        }

        $upvote->delete();
        $this->decrement('upvotes_count');

        return true;
    }

    public function hasUpvotedBy(User $user): bool
    {
        return $this->upvotes()->where('user_id', $user->id)->exists();
    }

    public function markAsBestAnswer(): void
    {
        $this->discussion->markAsBestAnswer($this);
    }
}
