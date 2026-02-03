<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'currency',
        'coupon_id',
        'coupon_code',
        'status',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'payment_details',
        'paid_at',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zip',
        'invoice_number',
        'invoice_path',
        'notes',
        'meta',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = strtoupper(Str::random(6));

        return "{$prefix}{$date}{$random}";
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && $this->isPaid();
    }

    public function markAsPaid(string $transactionId, array $paymentDetails = []): void
    {
        $this->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'transaction_id' => $transactionId,
            'payment_details' => $paymentDetails,
            'paid_at' => now(),
        ]);

        $this->createEnrollments();
        $this->processInstructorEarnings();
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'payment_status' => 'failed',
        ]);
    }

    public function createEnrollments(): void
    {
        foreach ($this->items as $item) {
            Enrollment::firstOrCreate(
                [
                    'user_id' => $this->user_id,
                    'course_id' => $item->course_id,
                ],
                [
                    'order_id' => $this->id,
                    'enrollment_type' => $this->coupon_id ? 'coupon' : 'paid',
                    'price_paid' => $item->final_price,
                    'currency' => $this->currency,
                    'enrolled_at' => now(),
                    'status' => 'active',
                ]
            );
        }
    }

    public function processInstructorEarnings(): void
    {
        foreach ($this->items as $item) {
            InstructorEarning::create([
                'instructor_id' => $item->instructor_id,
                'course_id' => $item->course_id,
                'order_id' => $this->id,
                'order_item_id' => $item->id,
                'order_amount' => $item->final_price,
                'commission_rate' => 100 - $item->platform_share / $item->final_price * 100,
                'instructor_amount' => $item->instructor_share,
                'platform_amount' => $item->platform_share,
                'status' => 'pending',
            ]);
        }
    }

    public function applyCoupon(Coupon $coupon): void
    {
        $discount = $coupon->calculateDiscount($this->subtotal);

        $this->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
            'total' => $this->subtotal - $discount + $this->tax_amount,
        ]);
    }

    public function getRefundedAmount(): float
    {
        return $this->refunds()->where('status', 'processed')->sum('amount');
    }

    public function getRemainingRefundable(): float
    {
        return $this->total - $this->getRefundedAmount();
    }
}
