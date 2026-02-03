<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'certificate_number',
        'user_id',
        'course_id',
        'enrollment_id',
        'template_id',
        'student_name',
        'course_title',
        'instructor_name',
        'completion_date',
        'issue_date',
        'expiry_date',
        'verification_url',
        'qr_code',
        'pdf_path',
        'image_path',
        'course_duration_hours',
        'final_score',
        'grade',
        'is_valid',
        'notes',
        'meta',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'course_duration_hours' => 'decimal:2',
        'final_score' => 'decimal:2',
        'is_valid' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->uuid)) {
                $certificate->uuid = (string) Str::uuid();
            }
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = static::generateCertificateNumber();
            }
            if (empty($certificate->issue_date)) {
                $certificate->issue_date = now();
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

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    // ========== Scopes ==========

    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== Helpers ==========

    public static function generateCertificateNumber(): string
    {
        $prefix = 'CERT';
        $year = date('Y');
        $random = strtoupper(Str::random(8));

        return "{$prefix}-{$year}-{$random}";
    }

    public function isValid(): bool
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getVerificationUrl(): string
    {
        if ($this->verification_url) {
            return $this->verification_url;
        }

        return url("/certificates/verify/{$this->certificate_number}");
    }

    public function invalidate(string $reason = null): void
    {
        $this->update([
            'is_valid' => false,
            'notes' => $reason,
        ]);
    }

    public function generateQrCode(): string
    {
        // Return QR code URL or generate using a package
        return "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($this->getVerificationUrl());
    }

    public static function createForEnrollment(Enrollment $enrollment): self
    {
        $course = $enrollment->course;
        $user = $enrollment->user;

        return static::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'template_id' => CertificateTemplate::getDefault()?->id,
            'student_name' => $user->name,
            'course_title' => $course->title,
            'instructor_name' => $course->instructor->name,
            'completion_date' => $enrollment->completed_at ?? now(),
            'course_duration_hours' => $course->duration_minutes / 60,
            'final_score' => $enrollment->progress_percentage,
        ]);
    }
}
