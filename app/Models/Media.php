<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'size',
        'type',
        'width',
        'height',
        'duration',
        'conversions',
        'alt_text',
        'caption',
    ];

    protected $casts = [
        'conversions' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($media) {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public function getUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getConversionUrl(string $conversion): ?string
    {
        if (!isset($this->conversions[$conversion])) {
            return null;
        }

        return asset('storage/' . $this->conversions[$conversion]);
    }

    public function getSizeFormatted(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
