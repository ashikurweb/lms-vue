<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\Api\Admin\CourseRequest;
use App\Http\Resources\Api\Admin\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return CourseResource::collection($courses);
    }

    /**
     * Store a newly created course.
     */
    public function store(CourseRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $course = Course::create($data);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => new CourseResource($course)
        ], 201);
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): CourseResource
    {
        return new CourseResource($course->load(['instructor', 'category', 'subcategory', 'tags']));
    }

    /**
     * Update the specified course.
     */
    public function update(CourseRequest $request, Course $course): JsonResponse
    {
        $course->update($request->validated());

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => new CourseResource($course)
        ]);
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course): JsonResponse
    {
        // Add checks if needed (e.g., if students are enrolled)
        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Course $course): JsonResponse
    {
        $course->update(['is_featured' => !$course->is_featured]);

        return response()->json([
            'message' => 'Featured status updated successfully',
            'course' => new CourseResource($course)
        ]);
    }

    /**
     * Toggle active status (published/unpublished).
     */
    public function toggleStatus(Course $course): JsonResponse
    {
        $newStatus = $course->status === 'published' ? 'unpublished' : 'published';
        $course->update([
            'status' => $newStatus,
            'is_published' => $newStatus === 'published'
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'course' => new CourseResource($course)
        ]);
    }
}
