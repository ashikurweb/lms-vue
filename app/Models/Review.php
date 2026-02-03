<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'rating',
        'title',
        'content',
        'rating_content',
        'rating_instructor',
        'rating_value',
        'status',
        'rejection_reason',
        'moderated_by',
        'moderated_at',
        'helpful_count',
        'not_helpful_count',
        'report_count',
        'instructor_response',
        'responded_at',
        'is_featured',
        'is_verified_purchase',
        'meta',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'rating_content' => 'decimal:1',
        'rating_instructor' => 'decimal:1',
        'rating_value' => 'decimal:1',
        'moderated_at' => 'datetime',
        'responded_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'meta' => 'array',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    // ========== Scopes ==========

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFlagged($query)
    {
        return $query->where('status', 'flagged');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    public function scopeWithResponse($query)
    {
        return $query->whereNotNull('instructor_response');
    }

    public function scopeHighRated($query, $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeMostHelpful($query)
    {
        return $query->orderByDesc('helpful_count');
    }

    // ========== Helpers ==========

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(User $moderator): void
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
        ]);

        // Update course rating
        $this->course->updateStats();
    }

    public function reject(User $moderator, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
        ]);
    }

    public function addInstructorResponse(string $response): void
    {
        $this->update([
            'instructor_response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function voteHelpful(User $user): void
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();

        if ($vote) {
            if ($vote->vote === 'not_helpful') {
                $this->decrement('not_helpful_count');
            }
            $vote->update(['vote' => 'helpful']);
        } else {
            $this->votes()->create([
                'user_id' => $user->id,
                'vote' => 'helpful',
            ]);
        }

        $this->increment('helpful_count');
    }

    public function voteNotHelpful(User $user): void
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();

        if ($vote) {
            if ($vote->vote === 'helpful') {
                $this->decrement('helpful_count');
            }
            $vote->update(['vote' => 'not_helpful']);
        } else {
            $this->votes()->create([
                'user_id' => $user->id,
                'vote' => 'not_helpful',
            ]);
        }

        $this->increment('not_helpful_count');
    }

    public function report(User $user, string $reason, ?string $description = null): void
    {
        $this->reports()->create([
            'user_id' => $user->id,
            'reason' => $reason,
            'description' => $description,
        ]);

        $this->increment('report_count');

        // Auto-flag if too many reports
        if ($this->report_count >= 5) {
            $this->update(['status' => 'flagged']);
        }
    }

    public function getHelpfulPercentage(): ?float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return null;
        }

        return round(($this->helpful_count / $total) * 100, 1);
    }
}
