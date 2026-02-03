<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_id',
        'title',
        'note',
        'timestamp',
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

    // ========== Helpers ==========

    public function getTimestampFormatted(): string
    {
        if (!$this->timestamp) {
            return '00:00';
        }

        $minutes = floor($this->timestamp / 60);
        $seconds = $this->timestamp % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
