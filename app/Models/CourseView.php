<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseView extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'device_type',
        'viewed_date',
    ];

    protected $casts = [
        'viewed_date' => 'date',
    ];

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeByDate($query, $date)
    {
        return $query->where('viewed_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_date', [$startDate, $endDate]);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    // ========== Helpers ==========

    public static function record(Course $course, ?User $user = null): void
    {
        static::create([
            'course_id' => $course->id,
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
            'viewed_date' => now()->toDateString(),
        ]);

        $course->increment('total_views');
    }
}
