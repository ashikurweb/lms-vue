<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Http\Requests\Api\Admin\BlogPostRequest;
use App\Http\Resources\Api\Admin\BlogPostResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = BlogPost::query()
            ->with(['author', 'category', 'tags'])
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return BlogPostResource::collection($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(BlogPostRequest $request): JsonResponse
    {
        $post = BlogPost::create($request->validated());

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json([
            'message' => 'Post created successfully',
            'post' => new BlogPostResource($post->load(['author', 'category', 'tags']))
        ], 201);
    }

    /**
     * Display the specified post.
     */
    public function show(BlogPost $blogPost): BlogPostResource
    {
        return new BlogPostResource($blogPost->load(['author', 'category', 'tags']));
    }

    /**
     * Update the specified post.
     */
    public function update(BlogPostRequest $request, BlogPost $blogPost): JsonResponse
    {
        $blogPost->update($request->validated());

        if ($request->has('tags')) {
            $blogPost->tags()->sync($request->tags);
        }

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => new BlogPostResource($blogPost->load(['author', 'category', 'tags']))
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(BlogPost $blogPost): JsonResponse
    {
        $blogPost->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
