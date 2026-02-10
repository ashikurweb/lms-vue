<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Http\Requests\Api\Admin\DiscussionRequest;
use App\Http\Resources\Api\Admin\DiscussionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the discussions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $discussions = Discussion::query()
            ->with(['user', 'course']) // Eager Load user and course to prevent N+1 in listing
            ->withCount('replies', 'upvotes', 'followers')
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->orderBy('last_activity_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return DiscussionResource::collection($discussions);
    }

    /**
     * Store a newly created discussion.
     */
    public function store(DiscussionRequest $request): JsonResponse
    {
        $discussion = Discussion::create($request->validated());

        return response()->json([
            'message' => 'Discussion created successfully',
            'discussion' => new DiscussionResource($discussion)
        ], 201);
    }

    /**
     * Display the specified discussion.
     */
    public function show(Discussion $discussion): DiscussionResource
    {
        return new DiscussionResource($discussion->load('user', 'course', 'lesson', 'bestAnswer'));
    }

    /**
     * Update the specified discussion.
     */
    public function update(DiscussionRequest $request, Discussion $discussion): JsonResponse
    {
        $discussion->update($request->validated());

        return response()->json([
            'message' => 'Discussion updated successfully',
            'discussion' => new DiscussionResource($discussion)
        ]);
    }

    /**
     * Remove the specified discussion.
     */
    public function destroy(Discussion $discussion): JsonResponse
    {
        if ($discussion->replies()->exists()) {
            return response()->json([
                'message' => 'Cannot delete discussion with associated replies'
            ], 422);
        }

        $discussion->delete();

        return response()->json([
            'message' => 'Discussion deleted successfully'
        ]);
    }

    /**
     * Get all discussions for selection.
     */
    public function getAll(): JsonResponse
    {
        $discussions = Discussion::select('id', 'title')->orderBy('title')->get();
        return response()->json($discussions);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Discussion $discussion): JsonResponse
    {
        $discussion->update(['is_featured' => !$discussion->is_featured]);

        return response()->json([
            'message' => 'Featured status updated successfully',
            'discussion' => new DiscussionResource($discussion)
        ]);
    }

    /**
     * Toggle pinned status.
     */
    public function togglePinned(Discussion $discussion): JsonResponse
    {
        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        return response()->json([
            'message' => 'Pinned status updated successfully',
            'discussion' => new DiscussionResource($discussion)
        ]);
    }

    /**
     * Toggle status between open and closed.
     */
    public function toggleStatus(Discussion $discussion): JsonResponse
    {
        $newStatus = $discussion->status === 'open' ? 'closed' : 'open';
        $discussion->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Status updated successfully',
            'discussion' => new DiscussionResource($discussion)
        ]);
    }
}
