<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\VideoTrack;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonAttachmentController extends Controller
{
    // ==================== LESSON RESOURCES ====================

    /**
     * Get all resources for a lesson.
     */
    public function resourceIndex(Lesson $lesson): JsonResponse
    {
        $resources = $lesson->resources()->orderBy('order')->get();
        return response()->json($resources);
    }

    /**
     * Store a new resource for a lesson.
     */
    public function resourceStore(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB max
            'is_downloadable' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $fileName = Str::random(20) . '.' . $extension;
        $path = $file->storeAs('uploads/lesson-resources', $fileName, 'public');

        $resource = $lesson->resources()->create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'is_downloadable' => $request->boolean('is_downloadable', true),
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'message' => 'Resource uploaded successfully',
            'resource' => $resource
        ], 201);
    }

    /**
     * Update a lesson resource (title/description/order only — file cannot be re-uploaded).
     */
    public function resourceUpdate(Request $request, LessonResource $resource): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_downloadable' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $resource->update($request->only(['title', 'description', 'is_downloadable', 'order']));

        return response()->json([
            'message' => 'Resource updated successfully',
            'resource' => $resource
        ]);
    }

    /**
     * Delete a lesson resource and its file.
     */
    public function resourceDestroy(LessonResource $resource): JsonResponse
    {
        // Delete the physical file
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully']);
    }

    // ==================== VIDEO TRACKS (SUBTITLES) ====================

    /**
     * Get all video tracks for a lesson.
     */
    public function trackIndex(Lesson $lesson): JsonResponse
    {
        $tracks = $lesson->videoTracks()->get();
        return response()->json($tracks);
    }

    /**
     * Store a new video track (subtitle/caption).
     */
    public function trackStore(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|max:10',
            'label' => 'required|string|max:255',
            'file' => 'required|file|mimes:srt,vtt,txt,ass,sub|max:5120', // 5MB max
            'kind' => 'required|in:subtitles,captions,descriptions',
            'is_default' => 'boolean',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension() ?: 'vtt';
        $fileName = Str::random(20) . '.' . $extension;
        $path = $file->storeAs('uploads/video-tracks', $fileName, 'public');

        // If this is set as default, unset other defaults for this lesson
        if ($request->boolean('is_default')) {
            $lesson->videoTracks()->update(['is_default' => false]);
        }

        $track = $lesson->videoTracks()->create([
            'language' => $request->language,
            'label' => $request->label,
            'file_path' => $path,
            'kind' => $request->kind,
            'is_default' => $request->boolean('is_default', false),
        ]);

        return response()->json([
            'message' => 'Video track added successfully',
            'track' => $track
        ], 201);
    }

    /**
     * Update a video track.
     */
    public function trackUpdate(Request $request, VideoTrack $track): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|max:10',
            'label' => 'required|string|max:255',
            'kind' => 'required|in:subtitles,captions,descriptions',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            $track->lesson->videoTracks()->where('id', '!=', $track->id)->update(['is_default' => false]);
        }

        $track->update($request->only(['language', 'label', 'kind', 'is_default']));

        return response()->json([
            'message' => 'Video track updated successfully',
            'track' => $track
        ]);
    }

    /**
     * Delete a video track and its file.
     */
    public function trackDestroy(VideoTrack $track): JsonResponse
    {
        if ($track->file_path && Storage::disk('public')->exists($track->file_path)) {
            Storage::disk('public')->delete($track->file_path);
        }

        $track->delete();

        return response()->json(['message' => 'Video track deleted successfully']);
    }
}
