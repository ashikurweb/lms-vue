<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscussionUpvote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'upvoteable_type',
        'upvoteable_id',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function upvoteable(): MorphTo
    {
        return $this->morphTo();
    }
}
