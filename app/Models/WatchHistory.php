<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    use HasFactory;

    protected $table = 'watch_history';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_id',
        'last_position',
        'duration_watched',
        'progress_percentage',
        'last_watched_at',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'last_watched_at' => 'datetime',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ========== Scopes ==========

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_watched_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public static function record(User $user, Lesson $lesson, int $position): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $lesson->course_id,
                'last_position' => $position,
                'duration_watched' => $position,
                'progress_percentage' => $lesson->duration_seconds > 0
                    ? min(100, ($position / $lesson->duration_seconds) * 100)
                    : 0,
                'last_watched_at' => now(),
            ]
        );
    }
}
