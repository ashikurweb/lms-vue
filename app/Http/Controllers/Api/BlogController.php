<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Http\Resources\Api\Admin\BlogPostResource;
use App\Http\Resources\Api\Admin\BlogCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = BlogPost::query()
            ->with(['author', 'category', 'tags'])
            ->published()
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->category, fn($q, $slug) => $q->whereHas('category', fn($cq) => $cq->where('slug', $slug)))
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($request->per_page ?? 12);

        return BlogPostResource::collection($posts);
    }

    /**
     * Display the specified published blog post.
     */
    public function show($slug)
    {
        $post = BlogPost::query()
            ->with(['author', 'category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $post->incrementViews();

        return new BlogPostResource($post);
    }

    /**
     * Get all active categories for the sidebar.
     */
    public function categories()
    {
        $categories = BlogCategory::query()
            ->where('is_active', true)
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        return BlogCategoryResource::collection($categories);
    }

    /**
     * Get featured posts.
     */
    public function featured()
    {
        $posts = BlogPost::query()
            ->with(['author', 'category'])
            ->published()
            ->featured()
            ->limit(5)
            ->get();

        return BlogPostResource::collection($posts);
    }
}
