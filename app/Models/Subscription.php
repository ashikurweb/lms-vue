<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'plan_id',
        'subscription_id',
        'status',
        'price',
        'currency',
        'payment_method',
        'starts_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'cancellation_reason',
        'cancel_at_period_end',
        'stripe_subscription_id',
        'paypal_subscription_id',
        'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->uuid)) {
                $subscription->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', 'trialing');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    public function willCancel(): bool
    {
        return $this->cancel_at_period_end;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
            ($this->ends_at && $this->ends_at->isPast());
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }

        return now()->diffInDays($this->current_period_end, false);
    }

    public function canAccessCourse(Course $course): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->plan->canAccessCourse($course);
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancel_at_period_end' => true,
        ]);
    }

    public function cancelImmediately(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'ends_at' => now(),
        ]);
    }

    public function resume(): void
    {
        if (!$this->cancel_at_period_end) {
            return;
        }

        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'cancel_at_period_end' => false,
        ]);
    }

    public function renew(): void
    {
        $this->update([
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(), // Adjust based on billing period
        ]);
    }
}
