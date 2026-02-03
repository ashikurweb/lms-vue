<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_name',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // ========== Scopes ==========

    public function scopeByLogName($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ========== Helpers ==========

    public static function log(
        string $event,
        string $description,
        ?User $user = null,
        $subject = null,
        array $properties = []
    ): self {
        $data = [
            'user_id' => $user?->id,
            'log_name' => 'default',
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($subject) {
            $data['subject_type'] = get_class($subject);
            $data['subject_id'] = $subject->id ?? null;
        }

        return static::create($data);
    }
}
