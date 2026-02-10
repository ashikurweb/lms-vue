<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseCurriculumController extends Controller
{
    /**
     * Get the curriculum (sections and lessons) of a course.
     */
    public function index(Course $course): JsonResponse
    {
        $curriculum = $course->sections()->with('lessons')->get();

        return response()->json($curriculum);
    }

    /**
     * Get all lessons across all courses.
     */
    public function lessonsIndex(Request $request): JsonResponse
    {
        $query = Lesson::with(['course:id,title', 'section:id,title']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $lessons = $query->paginate($request->get('per_page', 10));

        return response()->json($lessons);
    }

    /**
     * Add a section to a course.
     */
    public function storeSection(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'is_published' => 'boolean',
            'duration_minutes' => 'nullable|integer',
            'drip_enabled' => 'boolean',
            'drip_type' => 'nullable|string|in:days_after_enrollment,date,after_section',
            'drip_days' => 'nullable|integer',
            'drip_date' => 'nullable|date',
            'drip_after_section_id' => 'nullable|exists:course_sections,id',
        ]);

        $section = $course->sections()->create($request->all());

        return response()->json([
            'message' => 'Section created successfully',
            'section' => $section
        ], 201);
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, CourseSection $section): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'is_published' => 'boolean',
            'duration_minutes' => 'nullable|integer',
            'drip_enabled' => 'boolean',
            'drip_type' => 'nullable|string|in:days_after_enrollment,date,after_section',
            'drip_days' => 'nullable|integer',
            'drip_date' => 'nullable|date',
            'drip_after_section_id' => 'nullable|exists:course_sections,id',
        ]);

        $section->update($request->all());

        return response()->json([
            'message' => 'Section updated successfully',
            'section' => $section
        ]);
    }

    /**
     * Remove a section.
     */
    public function destroySection(CourseSection $section): JsonResponse
    {
        $section->delete();
        return response()->json(['message' => 'Section deleted successfully']);
    }

    /**
     * Lesson validation rules (shared between store and update).
     */
    private function lessonRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,text,audio,document,quiz,assignment,live_class,embed,scorm',
            'order' => 'integer',
            'description' => 'nullable|string',
            'content' => 'nullable|string',

            // Video Settings
            'video_provider' => 'nullable|string|in:upload,bunny,external',
            'video_url' => 'nullable|string|max:500',
            'video_id' => 'nullable|string|max:100',
            'duration_seconds' => 'nullable|integer',
            'video_thumbnail' => 'nullable|string|max:500',
            'video_qualities' => 'nullable|array',

            // Audio Settings
            'audio_url' => 'nullable|string|max:500',
            'audio_duration' => 'nullable|integer',

            // Document Settings
            'document_url' => 'nullable|string|max:500',
            'document_type' => 'nullable|string|in:pdf,ppt,word,excel,zip,other',

            // Embed Settings
            'embed_code' => 'nullable|string',

            // Boolean Settings
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_prerequisite' => 'boolean',
            'is_locked' => 'boolean',

            // Drip Settings
            'drip_enabled' => 'boolean',
            'drip_type' => 'nullable|string|in:days_after_enrollment,date,after_lesson',
            'drip_days' => 'nullable|integer',
            'drip_date' => 'nullable|date',
            'drip_after_lesson_id' => 'nullable|exists:lessons,id',

            // Meta
            'meta' => 'nullable|array',
        ];
    }

    /**
     * Add a lesson to a section.
     */
    public function storeLesson(Request $request, CourseSection $section): JsonResponse
    {
        $request->validate($this->lessonRules());

        $lesson = $section->lessons()->create(array_merge($request->all(), [
            'course_id' => $section->course_id
        ]));

        return response()->json([
            'message' => 'Lesson created successfully',
            'lesson' => $lesson
        ], 201);
    }

    /**
     * Update section order.
     */
    public function reorderSections(Request $request): JsonResponse
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:course_sections,id',
            'sections.*.order' => 'required|integer'
        ]);

        foreach ($request->sections as $item) {
            CourseSection::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Curriculum reordered successfully']);
    }

    /**
     * Update a lesson.
     */
    public function updateLesson(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate($this->lessonRules());

        $lesson->update($request->all());

        return response()->json([
            'message' => 'Lesson updated successfully',
            'lesson' => $lesson
        ]);
    }

    /**
     * Remove a lesson.
     */
    public function destroyLesson(Lesson $lesson): JsonResponse
    {
        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted successfully']);
    }
}
