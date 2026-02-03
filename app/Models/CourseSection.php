<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'is_published',
        'duration_minutes',
        'drip_enabled',
        'drip_type',
        'drip_days',
        'drip_date',
        'drip_after_section_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'drip_enabled' => 'boolean',
        'drip_date' => 'date',
    ];

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'section_id')->orderBy('order');
    }

    public function dripAfterSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'drip_after_section_id');
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========== Helpers ==========

    public function getTotalDuration(): int
    {
        return $this->lessons()->sum('duration_seconds');
    }

    public function getLessonsCount(): int
    {
        return $this->lessons()->published()->count();
    }

    public function isAccessibleBy(User $user): bool
    {
        if (!$this->drip_enabled) {
            return true;
        }

        $enrollment = $this->course->enrollments()
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$enrollment) {
            return false;
        }

        switch ($this->drip_type) {
            case 'days_after_enrollment':
                return $enrollment->enrolled_at->addDays($this->drip_days)->isPast();
            case 'date':
                return now()->gte($this->drip_date);
            case 'after_section':
                // Check if previous section is completed
                return $this->isPreviousSectionCompleted($user);
            default:
                return true;
        }
    }

    protected function isPreviousSectionCompleted(User $user): bool
    {
        if (!$this->drip_after_section_id) {
            return true;
        }

        $previousSection = $this->dripAfterSection;
        if (!$previousSection) {
            return true;
        }

        $lessonIds = $previousSection->lessons()->pluck('id');
        $completedCount = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        return $completedCount >= $lessonIds->count();
    }
}
