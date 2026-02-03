<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LiveClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'course_id',
        'lesson_id',
        'instructor_id',
        'title',
        'description',
        'thumbnail',
        'scheduled_at',
        'duration_minutes',
        'timezone',
        'platform',
        'meeting_id',
        'meeting_password',
        'meeting_url',
        'host_url',
        'recording_url',
        'is_free',
        'price',
        'max_attendees',
        'enable_chat',
        'enable_qa',
        'enable_recording',
        'enable_replay',
        'status',
        'started_at',
        'ended_at',
        'registered_count',
        'attended_count',
        'peak_viewers',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'enable_chat' => 'boolean',
        'enable_qa' => 'boolean',
        'enable_recording' => 'boolean',
        'enable_replay' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($liveClass) {
            if (empty($liveClass->uuid)) {
                $liveClass->uuid = (string) Str::uuid();
            }
        });
    }

    // ========== Relationships ==========

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(LiveClassRegistration::class);
    }

    // ========== Scopes ==========

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at');
    }

    public function scopePast($query)
    {
        return $query->whereIn('status', ['ended', 'cancelled'])
            ->orderByDesc('scheduled_at');
    }

    // ========== Helpers ==========

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function hasStarted(): bool
    {
        return $this->started_at !== null;
    }

    public function canJoin(): bool
    {
        if (!$this->isLive()) {
            return false;
        }

        return true;
    }

    public function startClass(): void
    {
        $this->update([
            'status' => 'live',
            'started_at' => now(),
        ]);
    }

    public function endClass(): void
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isRegisteredBy(User $user): bool
    {
        return $this->registrations()->where('user_id', $user->id)->exists();
    }

    public function register(User $user): LiveClassRegistration
    {
        if ($this->max_attendees && $this->registered_count >= $this->max_attendees) {
            throw new \Exception('Class is full');
        }

        $registration = $this->registrations()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $this->update(['registered_count' => $this->registrations()->count()]);

        return $registration;
    }

    public function recordAttendance(User $user): void
    {
        $registration = $this->registrations()->where('user_id', $user->id)->first();

        if ($registration && !$registration->attended) {
            $registration->update([
                'attended' => true,
                'joined_at' => now(),
            ]);

            $this->increment('attended_count');
        }
    }

    public function getTimeUntilStart(): ?string
    {
        if ($this->hasStarted() || !$this->scheduled_at) {
            return null;
        }

        return $this->scheduled_at->diffForHumans();
    }
}
