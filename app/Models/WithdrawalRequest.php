<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'payout_method_id',
        'amount',
        'currency',
        'status',
        'notes',
        'admin_notes',
        'transaction_id',
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

        static::creating(function ($request) {
            if (empty($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payoutMethod(): BelongsTo
    {
        return $this->belongsTo(PayoutMethod::class);
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

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
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

    public function process(string $transactionId, User $admin): void
    {
        $this->update([
            'status' => 'processed',
            'transaction_id' => $transactionId,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);
    }
}
