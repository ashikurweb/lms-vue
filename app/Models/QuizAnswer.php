<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_options',
        'text_answer',
        'order_answer',
        'matching_answer',
        'is_correct',
        'points_earned',
        'feedback',
        'is_flagged',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'order_answer' => 'array',
        'matching_answer' => 'array',
        'is_correct' => 'boolean',
        'is_flagged' => 'boolean',
        'points_earned' => 'decimal:2',
    ];

    // ========== Relationships ==========

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    // ========== Scopes ==========

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeWrong($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeNeedsGrading($query)
    {
        return $query->whereNull('is_correct');
    }

    // ========== Helpers ==========

    public function grade(bool $isCorrect, float $points, ?string $feedback = null): void
    {
        $this->update([
            'is_correct' => $isCorrect,
            'points_earned' => $points,
            'feedback' => $feedback,
        ]);

        // Recalculate attempt score
        $this->attempt->calculateScore();
    }

    public function toggleFlag(): void
    {
        $this->update(['is_flagged' => !$this->is_flagged]);
    }
}
