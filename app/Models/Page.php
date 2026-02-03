<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'is_published',
        'show_in_navigation',
        'show_in_footer',
        'meta_title',
        'meta_description',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_navigation' => 'boolean',
        'show_in_footer' => 'boolean',
    ];

    // ========== Relationships ==========

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeInNavigation($query)
    {
        return $query->where('show_in_navigation', true);
    }

    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========== Helpers ==========

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
