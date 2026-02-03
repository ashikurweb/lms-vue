<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'sender_id',
        'content',
        'attachments',
        'read_at',
        'is_system_message',
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
        'is_system_message' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ========== Scopes ==========

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeFromUser($query, User $user)
    {
        return $query->where('sender_id', $user->id);
    }

    // ========== Helpers ==========

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isFromUser(User $user): bool
    {
        return $this->sender_id === $user->id;
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }
}
