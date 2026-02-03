<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Discussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'lesson_id',
        'title',
        'content',
        'type',
        'status',
        'is_pinned',
        'is_featured',
        'best_answer_id',
        'views_count',
        'replies_count',
        'upvotes_count',
        'followers_count',
        'last_activity_at',
        'meta',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'last_activity_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($discussion) {
            if (empty($discussion->uuid)) {
                $discussion->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class)->whereNull('parent_id');
    }

    public function allReplies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function bestAnswer(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'best_answer_id');
    }

    public function upvotes(): MorphMany
    {
        return $this->morphMany(DiscussionUpvote::class, 'upvoteable');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(DiscussionFollower::class);
    }

    // ========== Scopes ==========

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeQuestions($query)
    {
        return $query->where('type', 'question');
    }

    public function scopeDiscussions($query)
    {
        return $query->where('type', 'discussion');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_activity_at');
    }

    public function scopeMostUpvoted($query)
    {
        return $query->orderByDesc('upvotes_count');
    }

    // ========== Helpers ==========

    public function isQuestion(): bool
    {
        return $this->type === 'question';
    }

    public function isAnswered(): bool
    {
        return $this->status === 'answered' || $this->best_answer_id !== null;
    }

    public function markAsBestAnswer(DiscussionReply $reply): void
    {
        // Remove previous best answer
        if ($this->best_answer_id) {
            DiscussionReply::where('id', $this->best_answer_id)
                ->update(['is_best_answer' => false]);
        }

        $reply->update(['is_best_answer' => true]);

        $this->update([
            'best_answer_id' => $reply->id,
            'status' => 'answered',
        ]);
    }

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

    public function follow(User $user): bool
    {
        if ($this->followers()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $this->followers()->create(['user_id' => $user->id]);
        $this->increment('followers_count');

        return true;
    }

    public function unfollow(User $user): bool
    {
        $follower = $this->followers()->where('user_id', $user->id)->first();

        if (!$follower) {
            return false;
        }

        $follower->delete();
        $this->decrement('followers_count');

        return true;
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('user_id', $user->id)->exists();
    }

    public function hasUpvotedBy(User $user): bool
    {
        return $this->upvotes()->where('user_id', $user->id)->exists();
    }

    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
            'replies_count' => $this->allReplies()->count(),
        ]);
    }
}
