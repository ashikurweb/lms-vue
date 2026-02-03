<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'vote',
    ];

    // ========== Relationships ==========

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeHelpful($query)
    {
        return $query->where('vote', 'helpful');
    }

    public function scopeNotHelpful($query)
    {
        return $query->where('vote', 'not_helpful');
    }
}
