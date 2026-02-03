<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'assignment_id',
        'user_id',
        'enrollment_id',
        'submission_number',
        'content',
        'files',
        'status',
        'points_earned',
        'is_late',
        'late_penalty_applied',
        'feedback',
        'inline_comments',
        'graded_by',
        'graded_at',
        'submitted_at',
    ];

    protected $casts = [
        'files' => 'array',
        'inline_comments' => 'array',
        'points_earned' => 'decimal:2',
        'is_late' => 'boolean',
        'graded_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            if (empty($submission->uuid)) {
                $submission->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ========== Scopes ==========

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeNeedsGrading($query)
    {
        return $query->whereIn('status', ['submitted', 'grading']);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function submit(): void
    {
        $isLate = $this->assignment->isOverdue();

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $isLate,
            'late_penalty_applied' => $isLate ? $this->assignment->late_submission_penalty : 0,
        ]);
    }

    public function grade(float $points, string $feedback, User $grader): void
    {
        // Apply late penalty if applicable
        if ($this->is_late && $this->late_penalty_applied > 0) {
            $points = $points * (1 - ($this->late_penalty_applied / 100));
        }

        $this->update([
            'status' => 'graded',
            'points_earned' => $points,
            'feedback' => $feedback,
            'graded_by' => $grader->id,
            'graded_at' => now(),
        ]);
    }

    public function requestResubmission(string $feedback): void
    {
        $this->update([
            'status' => 'resubmit',
            'feedback' => $feedback,
        ]);
    }

    public function isPassed(): bool
    {
        if (!$this->isGraded()) {
            return false;
        }

        return $this->points_earned >= $this->assignment->passing_points;
    }

    public function getScorePercentage(): ?float
    {
        if (!$this->isGraded() || $this->assignment->total_points == 0) {
            return null;
        }

        return round(($this->points_earned / $this->assignment->total_points) * 100, 2);
    }
}
