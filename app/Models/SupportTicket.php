<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'ticket_number',
        'user_id',
        'course_id',
        'order_id',
        'subject',
        'message',
        'attachments',
        'category',
        'priority',
        'status',
        'assigned_to',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected $casts = [
        'attachments' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    // ========== Scopes ==========

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // ========== Helpers ==========

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}{$date}{$random}";
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function assign(User $agent): void
    {
        $this->update([
            'assigned_to' => $agent->id,
            'status' => 'pending',
        ]);
    }

    public function addReply(User $user, string $message, array $attachments = [], bool $isInternal = false): SupportTicketReply
    {
        $reply = $this->replies()->create([
            'user_id' => $user->id,
            'message' => $message,
            'attachments' => $attachments,
            'is_internal_note' => $isInternal,
        ]);

        // Record first response time
        if (!$this->first_response_at && $user->id !== $this->user_id) {
            $this->update(['first_response_at' => now()]);
        }

        return $reply;
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    public function rate(int $rating, ?string $feedback = null): void
    {
        $this->update([
            'satisfaction_rating' => $rating,
            'satisfaction_feedback' => $feedback,
        ]);
    }

    public function getResponseTime(): ?string
    {
        if (!$this->first_response_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->first_response_at, true);
    }
}
