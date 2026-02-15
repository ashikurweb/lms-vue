<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * Display a listing of published courses (public).
     */
    public function index(Request $request): JsonResponse
    {
        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->published()
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->category, fn($q, $catId) => $q->where('category_id', $catId))
            ->when($request->level, fn($q, $level) => $q->where('level', $level))
            ->when($request->price_type, fn($q, $type) => $q->where('price_type', $type))
            ->when($request->sort === 'popular', fn($q) => $q->orderByDesc('total_enrollments'))
            ->when($request->sort === 'rating', fn($q) => $q->orderByDesc('rating'))
            ->when($request->sort === 'newest', fn($q) => $q->orderByDesc('published_at'))
            ->when($request->sort === 'price_low', fn($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_high', fn($q) => $q->orderByDesc('price'))
            ->when(!$request->sort, fn($q) => $q->orderByDesc('published_at'))
            ->paginate($request->per_page ?? 12);

        $data = $courses->through(function ($course) {
            return $this->formatCourse($course);
        });

        return response()->json($data);
    }

    /**
     * Get featured courses for homepage.
     */
    public function featured(Request $request): JsonResponse
    {
        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->published()
            ->where('is_featured', true)
            ->orderByDesc('rating')
            ->limit($request->limit ?? 6)
            ->get()
            ->map(fn($course) => $this->formatCourse($course));

        return response()->json($courses);
    }

    /**
     * Display a single course by slug.
     */
    public function show($slug): JsonResponse
    {
        $course = Course::query()
            ->with(['instructor', 'category', 'subcategory', 'sections.lessons' => function ($q) {
                $q->select('id', 'section_id', 'title', 'type', 'duration_seconds', 'is_free', 'is_published', 'order')
                  ->where('is_published', true)
                  ->orderBy('order');
            }])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'id' => $course->id,
            'uuid' => $course->uuid,
            'title' => $course->title,
            'slug' => $course->slug,
            'subtitle' => $course->subtitle,
            'short_description' => $course->short_description,
            'description' => $course->description,
            'requirements' => $course->requirements,
            'what_you_learn' => $course->what_you_learn,
            'target_audience' => $course->target_audience,
            'thumbnail' => $this->formatUrl($course->thumbnail),
            'cover_image' => $this->formatUrl($course->cover_image),
            'promo_video' => $this->formatUrl($course->promo_video),
            'promo_video_type' => $course->promo_video_type,
            'price_type' => $course->price_type,
            'price' => $course->price,
            'compare_price' => $course->compare_price,
            'level' => $course->level,
            'language' => $course->language,
            'duration' => $course->getDurationFormatted(),
            'total_lectures' => $course->total_lectures,
            'total_sections' => $course->total_sections,
            'rating' => $course->rating,
            'total_reviews' => $course->total_reviews,
            'total_enrollments' => $course->total_enrollments,
            'is_bestseller' => $course->is_bestseller,
            'is_new' => $course->is_new,
            'is_featured' => $course->is_featured,
            'has_certificate' => $course->has_certificate,
            'discount_percentage' => $course->getDiscountPercentage(),
            'instructor' => [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'avatar' => $course->instructor->avatar ?? null,
            ],
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
                'slug' => $course->category->slug ?? null,
            ] : null,
            'sections' => $course->sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'lessons' => $section->lessons->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'type' => $lesson->type,
                            'duration_seconds' => $lesson->duration_seconds,
                            'is_free' => $lesson->is_free,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get all categories with course counts.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['courses' => fn($q) => $q->published()])
            ->having('courses_count', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug ?? null,
                'icon' => $cat->icon ?? null,
                'courses_count' => $cat->courses_count,
            ]);

        return response()->json($categories);
    }

    /**
     * Format a course for the public API response.
     */
    private function formatCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'subtitle' => $course->subtitle,
            'short_description' => $course->short_description,
            'thumbnail' => $this->formatUrl($course->thumbnail),
            'price_type' => $course->price_type,
            'price' => $course->price,
            'compare_price' => $course->compare_price,
            'level' => $course->level,
            'language' => $course->language,
            'duration' => $course->getDurationFormatted(),
            'total_lectures' => $course->total_lectures,
            'rating' => $course->rating,
            'total_reviews' => $course->total_reviews,
            'total_enrollments' => $course->total_enrollments,
            'is_bestseller' => $course->is_bestseller,
            'is_new' => $course->is_new,
            'is_featured' => $course->is_featured,
            'discount_percentage' => $course->getDiscountPercentage(),
            'instructor' => [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'avatar' => $course->instructor->avatar ?? null,
            ],
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
            ] : null,
        ];
    }

    /**
     * Format URL for public access.
     */
    private function formatUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset($path);
    }
}
