<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait AuthResponse
{
    /**
     * Standardized Auth Response with Token and User Data
     */
    protected function respondWithToken($token, $user, $message = 'Success', $code = 200): JsonResponse
    {
        return $this->successResponse([
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], $message, $code);
    }

    /**
     * Format user data for API consumption
     */
    protected function formatUser($user)
    {
        if (!$user) return null;
        
        $userArray = $user->toArray();
        
        // Ensure roles and permissions are flat arrays
        $userArray['roles'] = $user->getRoleNames();
        $userArray['permissions'] = $user->getAllPermissions()->pluck('name');
        
        // Remove nested default relations if they exist as objects
        unset($userArray['roles_relation'], $userArray['permissions_relation']); 
        
        return $userArray;
    }
}
