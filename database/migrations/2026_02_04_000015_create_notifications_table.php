<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('type'); // Class name of notification
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('icon_color')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            
            // Related Entity
            $table->nullableMorphs('notifiable');
            
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        // Notification Preferences
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Email Notifications
            $table->boolean('email_course_updates')->default(true);
            $table->boolean('email_new_lessons')->default(true);
            $table->boolean('email_announcements')->default(true);
            $table->boolean('email_promotions')->default(true);
            $table->boolean('email_qa_replies')->default(true);
            $table->boolean('email_review_replies')->default(true);
            $table->boolean('email_assignment_feedback')->default(true);
            $table->boolean('email_certificate_issued')->default(true);
            
            // Push Notifications
            $table->boolean('push_course_updates')->default(true);
            $table->boolean('push_new_lessons')->default(true);
            $table->boolean('push_announcements')->default(true);
            $table->boolean('push_promotions')->default(false);
            $table->boolean('push_qa_replies')->default(true);
            
            // Instructor Specific
            $table->boolean('email_new_enrollments')->default(true);
            $table->boolean('email_new_reviews')->default(true);
            $table->boolean('email_new_questions')->default(true);
            $table->boolean('email_payout_updates')->default(true);
            
            $table->timestamps();

            $table->unique(['user_id']);
        });

        // Announcements (Course-level or Platform-level)
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Author
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            
            $table->string('title');
            $table->longText('content');
            
            $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->enum('audience', ['all', 'enrolled', 'specific'])->default('enrolled');
            
            $table->boolean('is_pinned')->default(false);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push')->default(false);
            
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->integer('views_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'published_at']);
        });

        // Announcement Read Status
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
