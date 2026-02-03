<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'amount',
        'currency',
        'reason',
        'status',
        'transaction_id',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->uuid)) {
                $refund->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    // ========== Helpers ==========

    public function approve(User $admin): void
    {
        $this->update([
            'status' => 'approved',
            'processed_by' => $admin->id,
        ]);
    }

    public function reject(User $admin, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);
    }

    public function process(string $transactionId): void
    {
        $this->update([
            'status' => 'processed',
            'transaction_id' => $transactionId,
            'processed_at' => now(),
        ]);

        // Update order status
        $totalRefunded = $this->order->getRefundedAmount();
        if ($totalRefunded >= $this->order->total) {
            $this->order->update([
                'status' => 'refunded',
                'payment_status' => 'refunded',
            ]);
        } else {
            $this->order->update(['status' => 'partially_refunded']);
        }

        // Revoke enrollments if fully refunded
        if ($totalRefunded >= $this->order->total) {
            $this->order->enrollments()->update(['status' => 'refunded', 'is_active' => false]);
        }
    }
}
