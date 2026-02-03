<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'affiliate_code',
        'referral_link',
        'commission_rate',
        'cookie_days',
        'total_clicks',
        'total_referrals',
        'total_conversions',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'status',
        'approved_at',
        'payment_method',
        'payment_details',
        'minimum_payout',
        'meta',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'minimum_payout' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_details' => 'array',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliate) {
            if (empty($affiliate->uuid)) {
                $affiliate->uuid = (string) Str::uuid();
            }
            if (empty($affiliate->affiliate_code)) {
                $affiliate->affiliate_code = static::generateAffiliateCode();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
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

    public function scopeActive($query)
    {
        return $query->approved();
    }

    // ========== Helpers ==========

    public static function generateAffiliateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('affiliate_code', $code)->exists());

        return $code;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    public function getReferralUrl(): string
    {
        if ($this->referral_link) {
            return $this->referral_link;
        }

        return url("/?ref={$this->affiliate_code}");
    }

    public function recordClick(): void
    {
        $this->increment('total_clicks');
    }

    public function recordReferral(User $user): AffiliateReferral
    {
        $this->increment('total_referrals');

        return $this->referrals()->create([
            'referred_user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);
    }

    public function recordConversion(AffiliateReferral $referral, Order $order): void
    {
        $commissionAmount = $order->total * ($this->commission_rate / 100);

        $referral->update([
            'order_id' => $order->id,
            'status' => 'converted',
            'order_amount' => $order->total,
            'commission_amount' => $commissionAmount,
            'commission_rate' => $this->commission_rate,
            'converted_at' => now(),
        ]);

        $this->increment('total_conversions');
        $this->increment('total_earnings', $commissionAmount);
        $this->increment('pending_earnings', $commissionAmount);
    }

    public function canRequestPayout(): bool
    {
        return $this->pending_earnings >= $this->minimum_payout;
    }

    public function getConversionRate(): float
    {
        if ($this->total_referrals === 0) {
            return 0;
        }

        return round(($this->total_conversions / $this->total_referrals) * 100, 2);
    }
}
