<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InstructorPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'instructor_id',
        'amount',
        'currency',
        'period_start',
        'period_end',
        'total_orders',
        'gross_amount',
        'platform_fee',
        'tax_withheld',
        'net_amount',
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
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'tax_withheld' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
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

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
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

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
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

        // Mark all earnings in this payout as paid
        InstructorEarning::where('payout_id', $this->id)
            ->update(['status' => 'paid']);

        // Update instructor profile
        $profile = InstructorProfile::where('user_id', $this->instructor_id)->first();
        if ($profile) {
            $profile->decrement('pending_earnings', $this->amount);
        }
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }
}
