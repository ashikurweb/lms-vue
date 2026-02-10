<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
{
    /**
     * Handle Image Upload (File or URL or Base64)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'url' => 'nullable|url',
            'folder' => 'nullable|string'
        ]);

        // Clean and validate folder name to prevent directory traversal
        $folder = $request->input('folder', 'uploads/others');
        $folder = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $folder);
        
        $fileName = Str::random(20) . '.webp';

        try {
            // Handle File Upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $fileName = Str::random(20) . '.' . $extension;
                
                // Store on 'public' disk
                $path = $file->storeAs($folder, $fileName, 'public');
                $url = Storage::url($path);
                
                return response()->json([
                    'url' => $url,
                    'path' => $path
                ]);
            }

            // Handle URL Upload
            if ($request->input('url')) {
                $urlInput = $request->input('url');
                $extension = pathinfo(parse_url($urlInput, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $fileName = Str::random(20) . '.' . $extension;
                
                $response = Http::get($urlInput);
                if ($response->successful()) {
                    $content = $response->body();
                    $path = $folder . '/' . $fileName;
                    
                    // Store on 'public' disk
                    Storage::disk('public')->put($path, $content);
                    $url = Storage::url($path);
                    
                    return response()->json([
                        'url' => $url,
                        'path' => $path
                    ]);
                }
            }

            return response()->json(['message' => 'No image provided'], 422);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Video Upload
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,wmv,mkv|max:102400', // max 100MB
            'folder' => 'nullable|string'
        ]);

        $folder = $request->input('folder', 'uploads/videos');
        $folder = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $folder);
        
        try {
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $extension = $file->getClientOriginalExtension() ?: 'mp4';
                $fileName = Str::random(25) . '.' . $extension;
                
                $path = $file->storeAs($folder, $fileName, 'public');
                $url = Storage::url($path);
                
                return response()->json([
                    'url' => $url,
                    'path' => $path,
                    'file_name' => $file->getClientOriginalName()
                ]);
            }

            return response()->json(['message' => 'No video file provided'], 422);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
}
