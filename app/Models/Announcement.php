<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'title',
        'content',
        'type',
        'audience',
        'is_pinned',
        'send_email',
        'send_push',
        'published_at',
        'expires_at',
        'views_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'send_email' => 'boolean',
        'send_push' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            if (empty($announcement->uuid)) {
                $announcement->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    // ========== Scopes ==========

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    // ========== Helpers ==========

    public function isPublished(): bool
    {
        if (!$this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    public function markAsRead(User $user): void
    {
        if (!$this->isReadBy($user)) {
            $this->reads()->create(['user_id' => $user->id]);
            $this->increment('views_count');
        }
    }

    public function publish(): void
    {
        $this->update(['published_at' => now()]);
    }
}
