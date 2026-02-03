<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'category',
        'question',
        'answer',
        'is_published',
        'order',
        'helpful_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('course_id');
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========== Helpers ==========

    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }
}
