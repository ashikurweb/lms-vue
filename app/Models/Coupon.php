<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'discount_value',
        'max_discount',
        'min_order_value',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_courses',
        'applicable_categories',
        'excluded_courses',
        'first_purchase_only',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'applicable_courses' => 'array',
        'applicable_categories' => 'array',
        'excluded_courses' => 'array',
        'first_purchase_only' => 'boolean',
        'meta' => 'array',
    ];

    // ========== Relationships ==========

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    // ========== Helpers ==========

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check per-user limit
        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        if ($userUsageCount >= $this->per_user_limit) {
            return false;
        }

        // Check first purchase only
        if ($this->first_purchase_only) {
            $hasOrders = Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->exists();
            if ($hasOrders) {
                return false;
            }
        }

        return true;
    }

    public function isApplicableTo(Course $course): bool
    {
        // Check excluded courses
        if (!empty($this->excluded_courses) && in_array($course->id, $this->excluded_courses)) {
            return false;
        }

        // If specific courses are set, check if course is in the list
        if (!empty($this->applicable_courses)) {
            return in_array($course->id, $this->applicable_courses);
        }

        // If specific categories are set, check if course's category is in the list
        if (!empty($this->applicable_categories)) {
            return in_array($course->category_id, $this->applicable_categories);
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_order_value) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                $discount = $amount * ($this->discount_value / 100);
                if ($this->max_discount) {
                    $discount = min($discount, $this->max_discount);
                }
                return $discount;

            case 'fixed':
                return min($this->discount_value, $amount);

            case 'free':
                return $amount;

            default:
                return 0;
        }
    }

    public function recordUsage(User $user, Order $order, float $discountAmount): void
    {
        $this->usages()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
        ]);

        $this->increment('used_count');
    }
}
