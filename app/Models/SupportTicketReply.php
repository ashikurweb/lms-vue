<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_internal_note',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal_note' => 'boolean',
    ];

    // ========== Relationships ==========

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }

    // ========== Helpers ==========

    public function isFromTicketOwner(): bool
    {
        return $this->user_id === $this->ticket->user_id;
    }

    public function isFromAgent(): bool
    {
        return $this->user_id !== $this->ticket->user_id && !$this->is_internal_note;
    }
}
