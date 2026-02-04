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
        // Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable(); // For UI badge color
            $table->foreignId('parent_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'order']);
            $table->index(['is_featured', 'is_active']);
        });

        // Blog Posts
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            
            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            
            // Media
            $table->string('featured_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable();
            
            // Settings
            $table->enum('status', ['draft', 'pending', 'published', 'scheduled', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('allow_comments')->default(true);
            
            // Reading
            $table->integer('reading_time')->default(0); // minutes
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            
            // Scheduling
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['author_id', 'status']);
            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['is_featured', 'status']);
            $table->index(['status', 'published_at']);
        });

        // Blog Tags
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('posts_count')->default(0);
            $table->timestamps();
        });

        // Blog Post Tags Pivot
        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('blog_tags')->onDelete('cascade');
            $table->primary(['post_id', 'tag_id']);
        });

        // Blog Comments
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->onDelete('cascade');
            
            // Guest info (if not logged in)
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'spam', 'trash'])->default('pending');
            $table->integer('likes_count')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['post_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
