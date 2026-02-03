<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstructorProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'short_bio',
        'expertise',
        'website',
        'linkedin',
        'twitter',
        'facebook',
        'youtube',
        'github',
        'profile_video',
        'rating',
        'total_reviews',
        'total_students',
        'total_courses',
        'commission_rate',
        'total_earnings',
        'pending_earnings',
        'status',
        'approved_at',
        'certifications',
        'achievements',
        'meta',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'approved_at' => 'datetime',
        'certifications' => 'array',
        'achievements' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id', 'user_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(InstructorPayout::class, 'instructor_id', 'user_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(InstructorEarning::class, 'instructor_id', 'user_id');
    }

    // ========== Scopes ==========

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTopRated($query)
    {
        return $query->orderByDesc('rating');
    }

    // ========== Helpers ==========

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function updateStats(): void
    {
        $this->update([
            'total_courses' => $this->courses()->published()->count(),
            'total_students' => $this->courses()->withCount('enrollments')->get()->sum('enrollments_count'),
            'total_reviews' => $this->courses()->withCount('reviews')->get()->sum('reviews_count'),
        ]);
    }

    public function getSocialLinks(): array
    {
        return array_filter([
            'website' => $this->website,
            'linkedin' => $this->linkedin,
            'twitter' => $this->twitter,
            'facebook' => $this->facebook,
            'youtube' => $this->youtube,
            'github' => $this->github,
        ]);
    }
}
