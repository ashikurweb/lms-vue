<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'lesson_id',
        'title',
        'description',
        'instructions',
        'total_points',
        'passing_points',
        'due_date',
        'allow_late_submission',
        'late_submission_penalty',
        'max_file_size',
        'allowed_file_types',
        'max_submissions',
        'is_required',
        'is_published',
        'order',
        'attachments',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
        'allowed_file_types' => 'array',
        'is_required' => 'boolean',
        'is_published' => 'boolean',
        'attachments' => 'array',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (empty($assignment->uuid)) {
                $assignment->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>', now());
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    // ========== Helpers ==========

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    public function canSubmit(User $user): bool
    {
        $submissionsCount = $this->submissions()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['draft'])
            ->count();

        if ($submissionsCount >= $this->max_submissions) {
            return false;
        }

        if ($this->isOverdue() && !$this->allow_late_submission) {
            return false;
        }

        return true;
    }

    public function getLatePenaltyFor(User $user): int
    {
        if (!$this->isOverdue() || !$this->allow_late_submission) {
            return 0;
        }

        return $this->late_submission_penalty;
    }

    public function getUserSubmission(User $user): ?AssignmentSubmission
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    public function hasUserSubmitted(User $user): bool
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->exists();
    }

    public function getTimeRemaining(): ?string
    {
        if (!$this->due_date || $this->isOverdue()) {
            return null;
        }

        return $this->due_date->diffForHumans();
    }

    public function getAllowedFileTypesFormatted(): string
    {
        if (empty($this->allowed_file_types)) {
            return 'All file types';
        }

        return implode(', ', array_map(fn($type) => '.' . $type, $this->allowed_file_types));
    }
}
