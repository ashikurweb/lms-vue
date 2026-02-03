<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'subscription_id',
        'user_id',
        'amount',
        'tax',
        'total',
        'currency',
        'status',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'payment_method',
        'transaction_id',
        'pdf_path',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    // ========== Relationships ==========

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'open')
            ->where('due_date', '<', now());
    }

    // ========== Helpers ==========

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->due_date && $this->due_date->isPast();
    }

    public function markAsPaid(string $transactionId): void
    {
        $this->update([
            'status' => 'paid',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);
    }
}
