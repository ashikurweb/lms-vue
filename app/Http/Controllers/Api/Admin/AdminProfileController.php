<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\Admin\AdminProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminProfileController extends Controller
{
    /**
     * Display the current admin profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'language' => $user->language,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
                'last_login_at' => $user->last_login_at,
                'roles' => $user->roles,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Update the current admin profile.
     */
    public function update(AdminProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validated();

        // Handle avatar if it's a URL from upload
        if (isset($validated['avatar']) && str_starts_with($validated['avatar'], config('app.url'))) {
            $validated['avatar'] = str_replace(config('app.url') . '/storage/', '', $validated['avatar']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }
}
