<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Http\Requests\Api\Admin\BlogCategoryRequest;
use App\Http\Resources\Api\Admin\BlogCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = BlogCategory::query()
            ->with(['parent']) // Eager Load parent to prevent N+1 in listing
            ->withCount('posts')
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return BlogCategoryResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(BlogCategoryRequest $request): JsonResponse
    {
        $category = BlogCategory::create($request->validated());

        return response()->json([
            'message' => 'Category created successfully',
            'category' => new BlogCategoryResource($category)
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(BlogCategory $blogCategory): BlogCategoryResource
    {
        return new BlogCategoryResource($blogCategory->load('parent'));
    }

    /**
     * Update the specified category.
     */
    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => new BlogCategoryResource($blogCategory)
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        if ($blogCategory->posts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with associated posts'
            ], 422);
        }

        $blogCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get all categories for selection.
     */
    public function getAll(): JsonResponse
    {
        $categories = BlogCategory::select('id', 'name')->orderBy('name')->get();
        return response()->json($categories);
    }
}
