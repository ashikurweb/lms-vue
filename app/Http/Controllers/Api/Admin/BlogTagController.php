<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use App\Http\Requests\Api\Admin\BlogTagRequest;
use App\Http\Resources\Api\Admin\BlogTagResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogTagController extends Controller
{
    /**
     * Display a listing of the tags.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = BlogTag::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return BlogTagResource::collection($tags);
    }

    /**
     * Store a newly created tag.
     */
    public function store(BlogTagRequest $request): JsonResponse
    {
        $tag = BlogTag::create($request->validated());

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => new BlogTagResource($tag)
        ], 201);
    }

    /**
     * Display the specified tag.
     */
    public function show(BlogTag $blogTag): BlogTagResource
    {
        return new BlogTagResource($blogTag);
    }

    /**
     * Update the specified tag.
     */
    public function update(BlogTagRequest $request, BlogTag $blogTag): JsonResponse
    {
        $blogTag->update($request->validated());

        return response()->json([
            'message' => 'Tag updated successfully',
            'tag' => new BlogTagResource($blogTag)
        ]);
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(BlogTag $blogTag): JsonResponse
    {
        $blogTag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully'
        ]);
    }

    /**
     * Get all tags for selection.
     */
    public function getAll(): JsonResponse
    {
        $tags = BlogTag::select('id', 'name')->orderBy('name')->get();
        return response()->json($tags);
    }
}
