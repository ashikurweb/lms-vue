<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'cover_image',
        'price',
        'compare_price',
        'currency',
        'is_published',
        'is_featured',
        'total_courses',
        'total_duration_hours',
        'total_enrollments',
        'rating',
        'meta_title',
        'meta_description',
        'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'total_duration_hours' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bundle) {
            if (empty($bundle->uuid)) {
                $bundle->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'bundle_courses')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('bundle_courses.order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

    // ========== Helpers ==========

    public function updateStats(): void
    {
        $courses = $this->courses;

        $this->update([
            'total_courses' => $courses->count(),
            'total_duration_hours' => $courses->sum('duration_minutes') / 60,
            'rating' => $courses->avg('rating'),
        ]);
    }

    public function getSavingsPercentage(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getIndividualPrice(): float
    {
        return $this->courses()->sum('price');
    }

    public function calculateSavings(): float
    {
        return max(0, $this->getIndividualPrice() - $this->price);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
