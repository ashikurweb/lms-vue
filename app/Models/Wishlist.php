<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'price_alert',
    ];

    protected $casts = [
        'price_alert' => 'boolean',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ========== Scopes ==========

    public function scopeWithPriceAlert($query)
    {
        return $query->where('price_alert', true);
    }

    // ========== Helpers ==========

    public function togglePriceAlert(): void
    {
        $this->update(['price_alert' => !$this->price_alert]);
    }
}
