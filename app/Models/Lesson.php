<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'section_id',
        'title',
        'description',
        'content',
        'order',
        'type',
        'video_url',
        'video_provider',
        'video_id',
        'duration_seconds',
        'video_thumbnail',
        'video_qualities',
        'audio_url',
        'audio_duration',
        'document_url',
        'document_type',
        'embed_code',
        'is_free',
        'is_published',
        'is_downloadable',
        'is_prerequisite',
        'is_locked',
        'drip_enabled',
        'drip_type',
        'drip_days',
        'drip_date',
        'drip_after_lesson_id',
        'total_views',
        'total_completions',
        'meta',
    ];

    protected $casts = [
        'video_qualities' => 'array',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'is_downloadable' => 'boolean',
        'is_prerequisite' => 'boolean',
        'is_locked' => 'boolean',
        'drip_enabled' => 'boolean',
        'drip_date' => 'date',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->uuid)) {
                $lesson->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class)->orderBy('order');
    }

    public function videoTracks(): HasMany
    {
        return $this->hasMany(VideoTrack::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function dripAfterLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'drip_after_lesson_id');
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeVideo($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ========== Helpers ==========

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isQuiz(): bool
    {
        return $this->type === 'quiz';
    }

    public function isAssignment(): bool
    {
        return $this->type === 'assignment';
    }

    public function getDurationFormatted(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getProgressFor(User $user): ?LessonProgress
    {
        return $this->progress()->where('user_id', $user->id)->first();
    }

    public function isCompletedBy(User $user): bool
    {
        $progress = $this->getProgressFor($user);
        return $progress && $progress->is_completed;
    }

    public function isAccessibleBy(User $user): bool
    {
        // Free lessons are always accessible
        if ($this->is_free) {
            return true;
        }

        // Check if user is enrolled
        $enrollment = $this->course->enrollments()
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Check drip content
        if (!$this->drip_enabled) {
            return true;
        }

        switch ($this->drip_type) {
            case 'days_after_enrollment':
                return $enrollment->enrolled_at->addDays($this->drip_days)->isPast();
            case 'date':
                return now()->gte($this->drip_date);
            case 'after_lesson':
                if ($this->drip_after_lesson_id) {
                    return $this->dripAfterLesson->isCompletedBy($user);
                }
                return true;
            default:
                return true;
        }
    }

    public function getNextLesson(): ?Lesson
    {
        return $this->course->directLessons()
            ->where(function ($query) {
                $query->where('section_id', $this->section_id)
                    ->where('order', '>', $this->order);
            })
            ->orWhere(function ($query) {
                $query->whereHas('section', function ($q) {
                    $q->where('order', '>', $this->section->order);
                });
            })
            ->orderBy('section_id')
            ->orderBy('order')
            ->first();
    }

    public function getPreviousLesson(): ?Lesson
    {
        return $this->course->directLessons()
            ->where('section_id', $this->section_id)
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }
}
