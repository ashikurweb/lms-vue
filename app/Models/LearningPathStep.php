<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_path_id',
        'course_id',
        'title',
        'description',
        'order',
        'type',
        'is_required',
        'is_locked',
        'completion_requirements',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_locked' => 'boolean',
        'completion_requirements' => 'array',
    ];

    // ========== Relationships ==========

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ========== Scopes ==========

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeCourseSteps($query)
    {
        return $query->where('type', 'course');
    }

    // ========== Helpers ==========

    public function isCourse(): bool
    {
        return $this->type === 'course' && $this->course_id !== null;
    }

    public function isMilestone(): bool
    {
        return $this->type === 'milestone';
    }

    public function isCompletedBy(User $user): bool
    {
        if (!$this->isCourse()) {
            return true; // Non-course steps are considered complete
        }

        $enrollment = $this->course->enrollments()
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return false;
        }

        $requirements = $this->completion_requirements ?? [];
        $minProgress = $requirements['min_progress'] ?? 100;

        return $enrollment->progress_percentage >= $minProgress;
    }

    public function isAccessibleBy(User $user): bool
    {
        if (!$this->is_locked) {
            return true;
        }

        // Check if learning path is sequential
        if (!$this->learningPath->is_sequential) {
            return true;
        }

        // Check if previous step is completed
        $previousStep = $this->learningPath->steps()
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();

        if (!$previousStep) {
            return true;
        }

        return $previousStep->isCompletedBy($user);
    }

    public function getNextStep(): ?self
    {
        return $this->learningPath->steps()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    public function getPreviousStep(): ?self
    {
        return $this->learningPath->steps()
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }
}
