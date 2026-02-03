<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'order_id',
        'enrollment_type',
        'price_paid',
        'currency',
        'enrolled_at',
        'expires_at',
        'is_active',
        'progress_percentage',
        'completed_lessons',
        'total_lessons',
        'last_accessed_at',
        'last_lesson_id',
        'completed_at',
        'total_watch_time',
        'status',
        'notes',
        'meta',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'enrolled_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->uuid)) {
                $enrollment->uuid = (string) Str::uuid();
            }
            if (empty($enrollment->enrolled_at)) {
                $enrollment->enrolled_at = now();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lastLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'last_lesson_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('enrolled_at');
    }

    // ========== Helpers ==========

    public function isActive(): bool
    {
        if ($this->status !== 'active' || !$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || ($this->progress_percentage >= 100);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function updateProgress(): void
    {
        $totalLessons = $this->course->directLessons()->published()->count();
        $completedLessons = $this->progress()->where('is_completed', true)->count();

        $this->update([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100, 2)
                : 0,
            'last_accessed_at' => now(),
        ]);

        // Mark as completed if all lessons are done
        if ($completedLessons >= $totalLessons && $totalLessons > 0) {
            $this->markAsCompleted();
        }
    }

    public function markAsCompleted(): void
    {
        if ($this->completed_at) {
            return;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        // Generate certificate if course has certificates enabled
        if ($this->course->has_certificate) {
            // Trigger certificate generation event
            // event(new CourseCompleted($this));
        }
    }

    public function getWatchTimeFormatted(): string
    {
        $hours = floor($this->total_watch_time / 3600);
        $minutes = floor(($this->total_watch_time % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} min";
    }

    public function getDaysEnrolled(): int
    {
        return $this->enrolled_at->diffInDays(now());
    }

    public function getLastAccessedFormatted(): string
    {
        if (!$this->last_accessed_at) {
            return 'Never';
        }

        return $this->last_accessed_at->diffForHumans();
    }
}
