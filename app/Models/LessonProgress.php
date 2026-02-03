<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    use HasFactory;

    protected $table = 'lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'enrollment_id',
        'is_completed',
        'completed_at',
        'watch_time',
        'last_position',
        'progress_percentage',
        'views_count',
        'last_watched_at',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'notes' => 'array',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // ========== Scopes ==========

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
            ->where('progress_percentage', '>', 0);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        // Update enrollment progress
        $this->enrollment->updateProgress();

        // Increment lesson completions
        $this->lesson->increment('total_completions');
    }

    public function updateWatchTime(int $position): void
    {
        $this->update([
            'last_position' => $position,
            'watch_time' => $this->watch_time + max(0, $position - $this->last_position),
            'last_watched_at' => now(),
            'views_count' => $this->views_count + 1,
        ]);

        // Calculate progress percentage based on video duration
        if ($this->lesson->duration_seconds > 0) {
            $progress = min(100, ($position / $this->lesson->duration_seconds) * 100);
            $this->update(['progress_percentage' => round($progress, 2)]);

            // Auto-complete if watched 90% or more
            if ($progress >= 90 && !$this->is_completed) {
                $this->markAsCompleted();
            }
        }
    }

    public function getWatchTimeFormatted(): string
    {
        $minutes = floor($this->watch_time / 60);
        $seconds = $this->watch_time % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getResumePosition(): int
    {
        return $this->last_position ?? 0;
    }
}
