<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'instructor_id',
        'category_id',
        'subcategory_id',
        'title',
        'slug',
        'subtitle',
        'short_description',
        'description',
        'requirements',
        'what_you_learn',
        'target_audience',
        'thumbnail',
        'cover_image',
        'promo_video',
        'promo_video_type',
        'price_type',
        'price',
        'compare_price',
        'currency',
        'level',
        'language',
        'captions',
        'duration_minutes',
        'total_lectures',
        'total_sections',
        'total_resources',
        'is_published',
        'is_approved',
        'is_featured',
        'is_bestseller',
        'is_trending',
        'is_new',
        'drip_content',
        'has_certificate',
        'allow_qa',
        'allow_reviews',
        'allow_discussions',
        'access_type',
        'access_days',
        'rating',
        'total_reviews',
        'total_enrollments',
        'total_views',
        'total_wishlist',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'published_at',
        'meta',
    ];

    protected $casts = [
        'requirements' => 'array',
        'what_you_learn' => 'array',
        'target_audience' => 'array',
        'captions' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_published' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_trending' => 'boolean',
        'is_new' => 'boolean',
        'drip_content' => 'boolean',
        'has_certificate' => 'boolean',
        'allow_qa' => 'boolean',
        'allow_reviews' => 'boolean',
        'allow_discussions' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->uuid)) {
                $course->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'course_tag');
    }

    public function coInstructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors', 'course_id', 'instructor_id')
            ->withPivot('revenue_share', 'role')
            ->withTimestamps();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, CourseSection::class, 'course_id', 'section_id');
    }

    public function directLessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(CourseView::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'course_id', 'prerequisite_course_id')
            ->withPivot('is_required', 'order')
            ->withTimestamps();
    }

    public function relatedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'related_courses', 'course_id', 'related_course_id')
            ->withPivot('relationship', 'order')
            ->withTimestamps();
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses')
            ->withPivot('order')
            ->withTimestamps();
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('is_approved', true)
            ->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price_type', 'free');
    }

    public function scopePaid($query)
    {
        return $query->where('price_type', 'paid');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeTopRated($query)
    {
        return $query->orderByDesc('rating');
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('total_enrollments');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    // ========== Helpers ==========

    public function isFree(): bool
    {
        return $this->price_type === 'free';
    }

    public function isPublished(): bool
    {
        return $this->is_published && $this->is_approved && $this->status === 'published';
    }

    public function getDiscountPercentage(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return null;
    }

    public function getDurationFormatted(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . ' min';
    }

    public function updateStats(): void
    {
        $this->update([
            'total_sections' => $this->sections()->count(),
            'total_lectures' => $this->directLessons()->count(),
            'duration_minutes' => (int) ($this->directLessons()->sum('duration_seconds') / 60),
            'total_enrollments' => $this->enrollments()->active()->count(),
            'total_reviews' => $this->reviews()->approved()->count(),
            'rating' => $this->reviews()->approved()->avg('rating') ?? 0,
        ]);
    }

    public function isEnrolledBy(User $user): bool
    {
        return $this->enrollments()->where('user_id', $user->id)->active()->exists();
    }

    public function isInWishlist(User $user): bool
    {
        return $this->wishlists()->where('user_id', $user->id)->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
