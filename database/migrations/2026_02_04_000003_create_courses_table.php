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
        // Courses
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            
            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->text('requirements')->nullable(); // JSON array
            $table->text('what_you_learn')->nullable(); // JSON array - Learning outcomes
            $table->text('target_audience')->nullable(); // JSON array
            
            // Media
            $table->string('thumbnail')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('promo_video')->nullable();
            $table->string('promo_video_type')->nullable(); // youtube, vimeo, upload
            $table->json('video_resolutions')->nullable();
            
            // Pricing
            $table->enum('price_type', ['free', 'paid', 'subscription'])->default('free');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('compare_price', 10, 2)->nullable(); // Original price for discount display
            $table->string('currency', 3)->default('USD');
            
            // Course Details
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'all_levels'])->default('all_levels');
            $table->string('language', 10)->default('en');
            $table->json('captions')->nullable(); // Available subtitle languages
            $table->integer('duration_minutes')->default(0); // Total course duration
            $table->integer('total_lectures')->default(0);
            $table->integer('total_sections')->default(0);
            $table->integer('total_resources')->default(0);
            
            // Settings
            $table->boolean('is_published')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('drip_content')->default(false); // Enable drip content
            $table->boolean('has_certificate')->default(true);
            $table->boolean('allow_qa')->default(true); // Q&A section
            $table->boolean('allow_reviews')->default(true);
            $table->boolean('allow_discussions')->default(true);
            
            // Access
            $table->enum('access_type', ['lifetime', 'limited', 'subscription'])->default('lifetime');
            $table->integer('access_days')->nullable(); // If limited access
            
            // Statistics
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_enrollments')->default(0);
            $table->integer('total_views')->default(0);
            $table->integer('total_wishlist')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending_review', 'published', 'unpublished', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['instructor_id', 'status']);
            $table->index(['category_id', 'status', 'is_published']);
            $table->index(['price_type', 'status', 'is_published']);
            $table->index(['is_featured', 'is_published', 'status']);
            $table->index(['is_bestseller', 'is_published']);
            $table->index(['rating', 'is_published']);
            $table->index(['created_at', 'is_published']);
        });

        // Course Tags Pivot
        Schema::create('course_tag', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['course_id', 'tag_id']);
        });

        // Course co-instructors (multiple instructors per course)
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('revenue_share', 5, 2)->default(0.00); // Percentage share
            $table->enum('role', ['primary', 'co_instructor', 'assistant'])->default('co_instructor');
            $table->timestamps();
            
            $table->unique(['course_id', 'instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
        Schema::dropIfExists('course_tag');
        Schema::dropIfExists('courses');
    }
};
