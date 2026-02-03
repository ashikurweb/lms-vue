<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'language',
        'label',
        'file_path',
        'kind',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ========== Relationships ==========

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    // ========== Scopes ==========

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeSubtitles($query)
    {
        return $query->where('kind', 'subtitles');
    }

    public function scopeCaptions($query)
    {
        return $query->where('kind', 'captions');
    }
}
