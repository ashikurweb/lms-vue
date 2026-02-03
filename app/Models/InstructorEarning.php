<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'course_id',
        'order_id',
        'order_item_id',
        'order_amount',
        'commission_rate',
        'instructor_amount',
        'platform_amount',
        'payout_id',
        'status',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'instructor_amount' => 'decimal:2',
        'platform_amount' => 'decimal:2',
    ];

    // ========== Relationships ==========

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(InstructorPayout::class, 'payout_id');
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'cleared']);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    // ========== Helpers ==========

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function clearForPayout(): void
    {
        $this->update(['status' => 'cleared']);
    }
}
