<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ========== Relationships ==========

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_tag');
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
