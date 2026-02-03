<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'image',
        'is_correct',
        'feedback',
        'order',
        'match_with',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // ========== Relationships ==========

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    // ========== Scopes ==========

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
