<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'referred_user_id',
        'order_id',
        'ip_address',
        'user_agent',
        'landing_page',
        'status',
        'order_amount',
        'commission_amount',
        'commission_rate',
        'clicked_at',
        'registered_at',
        'converted_at',
        'expires_at',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'clicked_at' => 'datetime',
        'registered_at' => 'datetime',
        'converted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // ========== Relationships ==========

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ========== Scopes ==========

    public function scopeClicked($query)
    {
        return $query->where('status', 'clicked');
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['clicked', 'registered'])
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // ========== Helpers ==========

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
