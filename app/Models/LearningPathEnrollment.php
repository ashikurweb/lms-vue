<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_path_id',
        'progress_percentage',
        'completed_steps',
        'current_step_id',
        'enrolled_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(LearningPathStep::class, 'current_step_id');
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ========== Helpers ==========

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function updateProgress(): void
    {
        $totalSteps = $this->learningPath->steps()->required()->count();
        $completedSteps = 0;

        foreach ($this->learningPath->steps()->required()->get() as $step) {
            if ($step->isCompletedBy($this->user)) {
                $completedSteps++;
            }
        }

        $progress = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;

        $this->update([
            'completed_steps' => $completedSteps,
            'progress_percentage' => round($progress, 2),
        ]);

        if ($progress >= 100) {
            $this->markAsCompleted();
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        $this->learningPath->updateStats();
    }

    public function moveToNextStep(): void
    {
        $nextStep = $this->currentStep?->getNextStep();

        if ($nextStep) {
            $this->update(['current_step_id' => $nextStep->id]);
        }
    }
}
