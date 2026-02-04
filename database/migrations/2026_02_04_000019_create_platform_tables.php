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
        // Platform Settings (Key-Value)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general'); // general, email, payment, seo, etc.
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, file
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed via API
            $table->timestamps();

            $table->index(['group']);
        });

        // Pages (For CMS - About, Terms, Privacy, etc.)
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('template')->default('default');
            
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_navigation')->default(true);
            $table->boolean('show_in_footer')->default(false);
            
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->integer('order')->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['slug', 'is_published']);
        });

        // FAQs
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->string('category')->nullable();
            
            $table->string('question');
            $table->text('answer');
            
            $table->boolean('is_published')->default(true);
            $table->integer('order')->default(0);
            $table->integer('helpful_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'is_published']);
            $table->index(['category', 'is_published']);
        });

        // Media Library
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            
            $table->enum('type', ['image', 'video', 'audio', 'document', 'other'])->default('other');
            
            // Image specific
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            
            // Video/Audio specific
            $table->integer('duration')->nullable(); // seconds
            
            $table->json('conversions')->nullable(); // Thumbnails, resized versions
            $table->json('meta')->nullable();
            
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index(['mime_type']);
        });

        // Activity Logs (For analytics and audit)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('log_name')->default('default');
            $table->string('event'); // created, updated, deleted, viewed, etc.
            $table->text('description');
            
            $table->nullableMorphs('subject'); // The model being acted on
            $table->nullableMorphs('causer'); // Who caused this activity
            
            $table->json('properties')->nullable(); // old values, new values, etc.
            
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'event']);
            $table->index(['created_at']);
        });

        // Course Views (For analytics)
        Schema::create('course_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            
            $table->date('viewed_date');
            $table->timestamps();

            $table->index(['course_id', 'viewed_date']);
            $table->index(['user_id', 'viewed_date']);
        });

        // Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            
            $table->string('subject');
            $table->longText('message');
            $table->json('attachments')->nullable();
            
            $table->enum('category', ['technical', 'billing', 'course_content', 'refund', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
            
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            $table->decimal('satisfaction_rating', 2, 1)->nullable(); // 1-5
            $table->text('satisfaction_feedback')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['ticket_number']);
        });

        // Support Ticket Replies
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->longText('message');
            $table->json('attachments')->nullable();
            
            $table->boolean('is_internal_note')->default(false); // Only visible to staff
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('course_views');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('media');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('settings');
    }
};
