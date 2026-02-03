<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'lesson_id',
        'title',
        'description',
        'instructions',
        'time_limit',
        'passing_score',
        'max_attempts',
        'show_answers_after_submission',
        'show_correct_answers',
        'randomize_questions',
        'randomize_options',
        'questions_per_page',
        'allow_review',
        'is_required',
        'total_points',
        'total_questions',
        'is_published',
        'order',
        'meta',
    ];

    protected $casts = [
        'show_answers_after_submission' => 'boolean',
        'show_correct_answers' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'allow_review' => 'boolean',
        'is_required' => 'boolean',
        'is_published' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quiz) {
            if (empty($quiz->uuid)) {
                $quiz->uuid = (string) Str::uuid();
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

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
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

    // ========== Helpers ==========

    public function updateStats(): void
    {
        $this->update([
            'total_questions' => $this->questions()->count(),
            'total_points' => $this->questions()->sum('points'),
        ]);
    }

    public function getAttemptsCount(User $user): int
    {
        return $this->attempts()->where('user_id', $user->id)->count();
    }

    public function canAttempt(User $user): bool
    {
        if ($this->max_attempts === 0) {
            return true;
        }

        return $this->getAttemptsCount($user) < $this->max_attempts;
    }

    public function getBestAttempt(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'graded')
            ->orderByDesc('percentage')
            ->first();
    }

    public function hasUserPassed(User $user): bool
    {
        $bestAttempt = $this->getBestAttempt($user);
        return $bestAttempt && $bestAttempt->is_passed;
    }

    public function getQuestionsForAttempt(): \Illuminate\Database\Eloquent\Collection
    {
        $questions = $this->questions()->with('options')->get();

        if ($this->randomize_questions) {
            $questions = $questions->shuffle();
        }

        return $questions;
    }
}
