<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningPath extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'created_by',
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'cover_image',
        'difficulty',
        'estimated_hours',
        'skill_outcome',
        'is_free',
        'price',
        'currency',
        'is_published',
        'is_featured',
        'is_sequential',
        'total_enrollments',
        'completion_rate',
        'meta_title',
        'meta_description',
        'meta',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'price' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_sequential' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($path) {
            if (empty($path->uuid)) {
                $path->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(LearningPathStep::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningPathEnrollment::class);
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // ========== Helpers ==========

    public function getTotalCourses(): int
    {
        return $this->steps()->whereNotNull('course_id')->count();
    }

    public function getTotalSteps(): int
    {
        return $this->steps()->count();
    }

    public function isEnrolledBy(User $user): bool
    {
        return $this->enrollments()->where('user_id', $user->id)->exists();
    }

    public function getEnrollmentFor(User $user): ?LearningPathEnrollment
    {
        return $this->enrollments()->where('user_id', $user->id)->first();
    }

    public function updateStats(): void
    {
        $totalEnrollments = $this->enrollments()->count();
        $completedEnrollments = $this->enrollments()->where('status', 'completed')->count();

        $this->update([
            'total_enrollments' => $totalEnrollments,
            'completion_rate' => $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
                : 0,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
