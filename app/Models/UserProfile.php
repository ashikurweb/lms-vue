<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'avatar',
        'cover_image',
        'date_of_birth',
        'gender',
        'headline',
        'bio',
        'country',
        'city',
        'timezone',
        'language',
        'website',
        'twitter',
        'linkedin',
        'facebook',
        'youtube',
        'github',
        'instagram',
        'occupation',
        'company',
        'skills',
        'interests',
        'learning_goals',
        'profile_completed',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'skills' => 'array',
        'interests' => 'array',
        'learning_goals' => 'array',
        'profile_completed' => 'boolean',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Helpers ==========

    public function getFullName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }

        return $this->user->name;
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Return default avatar or gravatar
        return 'https://www.gravatar.com/avatar/' . md5($this->user->email) . '?d=mp';
    }

    public function getSocialLinks(): array
    {
        return array_filter([
            'website' => $this->website,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'facebook' => $this->facebook,
            'youtube' => $this->youtube,
            'github' => $this->github,
            'instagram' => $this->instagram,
        ]);
    }

    public function calculateCompleteness(): int
    {
        $fields = [
            'first_name', 'last_name', 'avatar', 'date_of_birth',
            'headline', 'bio', 'country', 'occupation',
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    public function updateCompleteness(): void
    {
        $this->update([
            'profile_completed' => $this->calculateCompleteness() >= 80,
        ]);
    }
}
