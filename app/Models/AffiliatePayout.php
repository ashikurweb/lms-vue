<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AffiliatePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'affiliate_id',
        'amount',
        'currency',
        'payment_method',
        'payment_details',
        'transaction_id',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payout) {
            if (empty($payout->uuid)) {
                $payout->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ========== Helpers ==========

    public function process(string $transactionId, User $admin): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        // Update affiliate earnings
        $this->affiliate->decrement('pending_earnings', $this->amount);
        $this->affiliate->increment('paid_earnings', $this->amount);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }
}
