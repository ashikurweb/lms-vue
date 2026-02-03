<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_downloadable',
        'order',
        'download_count',
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
    ];

    // ========== Relationships ==========

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    // ========== Helpers ==========

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }
}
