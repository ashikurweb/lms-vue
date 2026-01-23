<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use App\Traits\AuthResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse, AuthResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * User Registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $token = $this->authService->generateToken($user);

            return $this->respondWithToken($token, $user, 'Registration successful. Please verify your email with the OTP sent to your email.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify Email OTP
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            
            if ($this->authService->verifyOtp($user, $request->otp)) {
                return $this->successResponse($this->formatUser($user), 'Email verified successfully');
            }

            return $this->errorResponse('Invalid or expired OTP', 422);
        } catch (Exception $e) {
            return $this->errorResponse('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resend Verification OTP
     */
    public function resendOtp(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            
            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse('Email already verified', 422);
            }

            $this->authService->resendOtp($user);
            return $this->successResponse(null, 'Verification code resent to your email.');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to resend OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->login($request->validated());
            return $token 
                ? $this->respondWithToken($token, auth('api')->user(), 'Login successful')
                : $this->errorResponse('Invalid credentials', 401);
        } catch (Exception $e) {
            // Check if it's a verification error
            if (str_contains($e->getMessage(), 'not verified')) {
                // We still give a token so they can call the verify route
                $user = User::where('email', $request->identity)
                    ->orWhere('username', $request->identity)
                    ->orWhere('phone', $request->identity)
                    ->first();
                $token = $this->authService->generateToken($user);
                return $this->respondWithToken($token, $user, $e->getMessage(), 403);
            }
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function me(): JsonResponse
    {
        return $this->successResponse($this->formatUser(auth('api')->user()), 'User profile fetched successfully');
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(auth('api')->refresh(), auth('api')->user(), 'Token refreshed successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Refresh failed: ' . $e->getMessage(), 401);
        }
    }
}
