<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_course_updates',
        'email_new_lessons',
        'email_announcements',
        'email_promotions',
        'email_qa_replies',
        'email_review_replies',
        'email_assignment_feedback',
        'email_certificate_issued',
        'push_course_updates',
        'push_new_lessons',
        'push_announcements',
        'push_promotions',
        'push_qa_replies',
        'instructor_new_enrollments',
        'instructor_new_reviews',
        'instructor_new_questions',
        'instructor_payout_updates',
    ];

    protected $casts = [
        'email_course_updates' => 'boolean',
        'email_new_lessons' => 'boolean',
        'email_announcements' => 'boolean',
        'email_promotions' => 'boolean',
        'email_qa_replies' => 'boolean',
        'email_review_replies' => 'boolean',
        'email_assignment_feedback' => 'boolean',
        'email_certificate_issued' => 'boolean',
        'push_course_updates' => 'boolean',
        'push_new_lessons' => 'boolean',
        'push_announcements' => 'boolean',
        'push_promotions' => 'boolean',
        'push_qa_replies' => 'boolean',
        'instructor_new_enrollments' => 'boolean',
        'instructor_new_reviews' => 'boolean',
        'instructor_new_questions' => 'boolean',
        'instructor_payout_updates' => 'boolean',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Helpers ==========

    public static function getOrCreateForUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_course_updates' => true,
                'email_new_lessons' => true,
                'email_announcements' => true,
                'email_promotions' => true,
                'email_qa_replies' => true,
                'email_review_replies' => true,
                'email_assignment_feedback' => true,
                'email_certificate_issued' => true,
                'push_course_updates' => true,
                'push_new_lessons' => true,
                'push_announcements' => true,
                'push_promotions' => false,
                'push_qa_replies' => true,
                'instructor_new_enrollments' => true,
                'instructor_new_reviews' => true,
                'instructor_new_questions' => true,
                'instructor_payout_updates' => true,
            ]
        );
    }

    public function shouldNotify(string $type, string $channel = 'email'): bool
    {
        $key = "{$channel}_{$type}";

        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        // For instructor notifications
        $instructorKey = "instructor_{$type}";
        if (property_exists($this, $instructorKey)) {
            return $this->{$instructorKey};
        }

        return true;
    }
}
