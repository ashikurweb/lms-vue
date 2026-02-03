<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClassRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_class_id',
        'user_id',
        'attended',
        'joined_at',
        'left_at',
        'watch_time',
        'reminder_sent',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    // ========== Relationships ==========

    public function liveClass(): BelongsTo
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeAttended($query)
    {
        return $query->where('attended', true);
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('reminder_sent', false);
    }

    // ========== Helpers ==========

    public function recordJoin(): void
    {
        $this->update([
            'attended' => true,
            'joined_at' => now(),
        ]);
    }

    public function recordLeave(): void
    {
        $watchTime = $this->joined_at
            ? now()->diffInSeconds($this->joined_at)
            : 0;

        $this->update([
            'left_at' => now(),
            'watch_time' => $watchTime,
        ]);
    }

    public function markReminderSent(): void
    {
        $this->update(['reminder_sent' => true]);
    }
}
