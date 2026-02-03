<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'badge_text',
        'badge_color',
        'price',
        'currency',
        'billing_period',
        'billing_interval',
        'has_trial',
        'trial_days',
        'features',
        'access_all_courses',
        'included_categories',
        'excluded_courses',
        'max_courses',
        'download_resources',
        'certificates',
        'priority_support',
        'is_popular',
        'is_active',
        'order',
        'stripe_product_id',
        'stripe_price_id',
        'paypal_plan_id',
        'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_trial' => 'boolean',
        'features' => 'array',
        'access_all_courses' => 'boolean',
        'included_categories' => 'array',
        'excluded_courses' => 'array',
        'download_resources' => 'boolean',
        'certificates' => 'boolean',
        'priority_support' => 'boolean',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    // ========== Relationships ==========

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========== Helpers ==========

    public function getPriceFormatted(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getBillingDescription(): string
    {
        return match ($this->billing_period) {
            'monthly' => 'per month',
            'quarterly' => 'per quarter',
            'yearly' => 'per year',
            'lifetime' => 'one-time',
            default => '',
        };
    }

    public function canAccessCourse(Course $course): bool
    {
        if ($this->access_all_courses) {
            return !in_array($course->id, $this->excluded_courses ?? []);
        }

        if (!empty($this->included_categories)) {
            return in_array($course->category_id, $this->included_categories);
        }

        return false;
    }
}
