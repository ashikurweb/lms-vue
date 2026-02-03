<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quiz_id',
        'type',
        'question',
        'explanation',
        'image',
        'audio',
        'video',
        'points',
        'order',
        'is_required',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    const TYPE_SINGLE_CHOICE = 'single_choice';
    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_TRUE_FALSE = 'true_false';
    const TYPE_SHORT_ANSWER = 'short_answer';
    const TYPE_LONG_ANSWER = 'long_answer';
    const TYPE_FILL_BLANK = 'fill_blank';
    const TYPE_MATCHING = 'matching';
    const TYPE_ORDERING = 'ordering';
    const TYPE_IMAGE_CHOICE = 'image_choice';
    const TYPE_ESSAY = 'essay';

    // ========== Relationships ==========

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    // ========== Scopes ==========

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // ========== Helpers ==========

    public function isMCQ(): bool
    {
        return in_array($this->type, [
            self::TYPE_SINGLE_CHOICE,
            self::TYPE_MULTIPLE_CHOICE,
            self::TYPE_TRUE_FALSE,
            self::TYPE_IMAGE_CHOICE,
        ]);
    }

    public function isTextBased(): bool
    {
        return in_array($this->type, [
            self::TYPE_SHORT_ANSWER,
            self::TYPE_LONG_ANSWER,
            self::TYPE_FILL_BLANK,
            self::TYPE_ESSAY,
        ]);
    }

    public function requiresManualGrading(): bool
    {
        return in_array($this->type, [
            self::TYPE_LONG_ANSWER,
            self::TYPE_ESSAY,
        ]);
    }

    public function getCorrectOptions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->options()->where('is_correct', true)->get();
    }

    public function checkAnswer($userAnswer): array
    {
        if ($this->requiresManualGrading()) {
            return ['is_correct' => null, 'points' => 0];
        }

        switch ($this->type) {
            case self::TYPE_SINGLE_CHOICE:
            case self::TYPE_TRUE_FALSE:
            case self::TYPE_IMAGE_CHOICE:
                return $this->checkSingleChoice($userAnswer);

            case self::TYPE_MULTIPLE_CHOICE:
                return $this->checkMultipleChoice($userAnswer);

            case self::TYPE_SHORT_ANSWER:
            case self::TYPE_FILL_BLANK:
                return $this->checkTextAnswer($userAnswer);

            case self::TYPE_MATCHING:
                return $this->checkMatching($userAnswer);

            case self::TYPE_ORDERING:
                return $this->checkOrdering($userAnswer);

            default:
                return ['is_correct' => null, 'points' => 0];
        }
    }

    protected function checkSingleChoice($optionId): array
    {
        $correctOption = $this->getCorrectOptions()->first();
        $isCorrect = $correctOption && $correctOption->id == $optionId;

        return [
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $this->points : 0,
        ];
    }

    protected function checkMultipleChoice(array $optionIds): array
    {
        $correctIds = $this->getCorrectOptions()->pluck('id')->toArray();
        sort($correctIds);
        sort($optionIds);

        $isCorrect = $correctIds === $optionIds;

        return [
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $this->points : 0,
        ];
    }

    protected function checkTextAnswer(string $answer): array
    {
        $settings = $this->settings ?? [];
        $caseSensitive = $settings['case_sensitive'] ?? false;

        $correctAnswers = $this->getCorrectOptions()->pluck('option_text')->toArray();

        foreach ($correctAnswers as $correct) {
            $userAnswer = $caseSensitive ? trim($answer) : strtolower(trim($answer));
            $correctAnswer = $caseSensitive ? trim($correct) : strtolower(trim($correct));

            if ($userAnswer === $correctAnswer) {
                return ['is_correct' => true, 'points' => $this->points];
            }
        }

        return ['is_correct' => false, 'points' => 0];
    }

    protected function checkMatching(array $matches): array
    {
        $correctMatches = $this->options()->pluck('match_with', 'id')->toArray();
        $isCorrect = true;

        foreach ($matches as $optionId => $matchedWith) {
            if (!isset($correctMatches[$optionId]) || $correctMatches[$optionId] !== $matchedWith) {
                $isCorrect = false;
                break;
            }
        }

        return [
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $this->points : 0,
        ];
    }

    protected function checkOrdering(array $orderedIds): array
    {
        $correctOrder = $this->options()->orderBy('order')->pluck('id')->toArray();
        $isCorrect = $correctOrder === $orderedIds;

        return [
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $this->points : 0,
        ];
    }
}
