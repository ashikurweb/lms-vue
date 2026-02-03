<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'quiz_id',
        'enrollment_id',
        'attempt_number',
        'started_at',
        'submitted_at',
        'time_spent',
        'total_questions',
        'answered_questions',
        'correct_answers',
        'wrong_answers',
        'skipped_questions',
        'score_earned',
        'score_total',
        'percentage',
        'is_passed',
        'status',
        'feedback',
        'graded_by',
        'graded_at',
        'meta',
    ];

    protected $casts = [
        'score_earned' => 'decimal:2',
        'score_total' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attempt) {
            if (empty($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }
            if (empty($attempt->started_at)) {
                $attempt->started_at = now();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ========== Scopes ==========

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function isExpired(): bool
    {
        if ($this->status !== 'in_progress' || !$this->quiz->time_limit) {
            return false;
        }

        return $this->started_at->addMinutes($this->quiz->time_limit)->isPast();
    }

    public function getRemainingTime(): int
    {
        if (!$this->quiz->time_limit || $this->status !== 'in_progress') {
            return 0;
        }

        $endTime = $this->started_at->addMinutes($this->quiz->time_limit);
        return max(0, now()->diffInSeconds($endTime, false));
    }

    public function submit(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'time_spent' => $this->started_at->diffInSeconds(now()),
        ]);

        $this->calculateScore();
    }

    public function calculateScore(): void
    {
        $answers = $this->answers()->with('question')->get();

        $totalQuestions = $this->quiz->questions()->count();
        $totalPoints = $this->quiz->questions()->sum('points');
        $earnedPoints = 0;
        $correct = 0;
        $wrong = 0;
        $skipped = 0;
        $answered = 0;
        $needsManualGrading = false;

        foreach ($this->quiz->questions as $question) {
            $answer = $answers->firstWhere('question_id', $question->id);

            if (!$answer) {
                $skipped++;
                continue;
            }

            $answered++;

            if ($answer->is_correct === null) {
                // Needs manual grading
                $needsManualGrading = true;
            } elseif ($answer->is_correct) {
                $correct++;
                $earnedPoints += $answer->points_earned;
            } else {
                $wrong++;
            }
        }

        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $isPassed = $percentage >= $this->quiz->passing_score;

        $this->update([
            'total_questions' => $totalQuestions,
            'answered_questions' => $answered,
            'correct_answers' => $correct,
            'wrong_answers' => $wrong,
            'skipped_questions' => $skipped,
            'score_earned' => $earnedPoints,
            'score_total' => $totalPoints,
            'percentage' => round($percentage, 2),
            'is_passed' => $isPassed,
            'status' => $needsManualGrading ? 'submitted' : 'graded',
            'graded_at' => $needsManualGrading ? null : now(),
        ]);
    }

    public function getTimeSpentFormatted(): string
    {
        $minutes = floor($this->time_spent / 60);
        $seconds = $this->time_spent % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
