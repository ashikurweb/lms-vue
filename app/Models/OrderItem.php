<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'course_id',
        'instructor_id',
        'course_title',
        'price',
        'discount',
        'final_price',
        'bundle_id',
        'instructor_share',
        'platform_share',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'instructor_share' => 'decimal:2',
        'platform_share' => 'decimal:2',
    ];

    // ========== Relationships ==========

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    // ========== Helpers ==========

    public function calculateShares(float $instructorCommission = 70): void
    {
        $this->instructor_share = $this->final_price * ($instructorCommission / 100);
        $this->platform_share = $this->final_price - $this->instructor_share;
        $this->save();
    }
}
