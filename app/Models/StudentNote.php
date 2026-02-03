<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'lesson_id',
        'course_id',
        'content',
        'timestamp',
        'color',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($note) {
            if (empty($note->uuid)) {
                $note->uuid = (string) Str::uuid();
            }
        });
    }

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

    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
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
