<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'orientation',
        'size',
        'background_image',
        'background_color',
        'html_content',
        'styles',
        'elements',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'styles' => 'array',
        'elements' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========== Relationships ==========

    public function certificates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ========== Helpers ==========

    public static function getDefault(): ?self
    {
        return static::active()->default()->first();
    }

    public function renderHtml(Certificate $certificate): string
    {
        $content = $this->html_content;

        $placeholders = [
            '{{student_name}}' => $certificate->student_name,
            '{{course_title}}' => $certificate->course_title,
            '{{instructor_name}}' => $certificate->instructor_name ?? '',
            '{{completion_date}}' => $certificate->completion_date->format('F j, Y'),
            '{{issue_date}}' => $certificate->issue_date->format('F j, Y'),
            '{{certificate_number}}' => $certificate->certificate_number,
            '{{course_duration}}' => $certificate->course_duration_hours . ' hours',
            '{{final_score}}' => $certificate->final_score ? $certificate->final_score . '%' : 'N/A',
            '{{grade}}' => $certificate->grade ?? 'Pass',
            '{{verification_url}}' => $certificate->verification_url ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }
}
