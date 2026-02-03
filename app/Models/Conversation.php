<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'subject',
        'type',
        'last_message_at',
        'is_closed',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_closed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // ========== Scopes ==========

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_message_at');
    }

    // ========== Helpers ==========

    public static function startBetween(User $user1, User $user2, string $subject = null): self
    {
        // Check if conversation already exists
        $existingConversation = static::forUser($user1)
            ->whereHas('participants', function ($q) use ($user2) {
                $q->where('user_id', $user2->id);
            })
            ->where('type', 'direct')
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        $conversation = static::create([
            'subject' => $subject,
            'type' => 'direct',
        ]);

        $conversation->participants()->createMany([
            ['user_id' => $user1->id],
            ['user_id' => $user2->id],
        ]);

        return $conversation;
    }

    public function addMessage(User $sender, string $content, array $attachments = []): Message
    {
        $message = $this->messages()->create([
            'sender_id' => $sender->id,
            'content' => $content,
            'attachments' => $attachments,
        ]);

        $this->update(['last_message_at' => now()]);

        // Update unread count for other participants
        $this->participants()
            ->where('user_id', '!=', $sender->id)
            ->increment('unread_count');

        return $message;
    }

    public function markAsRead(User $user): void
    {
        $this->participants()
            ->where('user_id', $user->id)
            ->update([
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);
    }

    public function getUnreadCountFor(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        return $participant ? $participant->unread_count : 0;
    }

    public function close(): void
    {
        $this->update(['is_closed' => true]);
    }

    public function reopen(): void
    {
        $this->update(['is_closed' => false]);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function getOtherParticipant(User $currentUser): ?User
    {
        $participant = $this->participants()
            ->where('user_id', '!=', $currentUser->id)
            ->first();

        return $participant?->user;
    }
}
