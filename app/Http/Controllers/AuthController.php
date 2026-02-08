<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use App\Traits\AuthResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Forgot Password - Request OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->forgotPassword($request->identity);
            return $this->successResponse(null, 'Password reset code sent to your email.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Reset Password using OTP
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = $this->authService->resetPassword(
                $request->identity,
                $request->otp,
                $request->password
            );

            return $status 
                ? $this->successResponse(null, 'Password reset successful. You can now login.')
                : $this->errorResponse('Invalid or expired OTP', 422);
        } catch (Exception $e) {
            return $this->errorResponse('Reset failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enable/Disable 2FA
     */
    public function toggle2fa(Request $request): JsonResponse
    {
        $request->validate(['enable' => 'required|boolean']);
        $this->authService->toggle2FA(auth('api')->user(), $request->enable);
        
        $message = $request->enable ? '2FA enabled successfully' : '2FA disabled successfully';
        return $this->successResponse(null, $message);
    }

    /**
     * Verify 2FA Login
     */
    public function verify2fa(Request $request): JsonResponse
    {
        $request->validate(['otp' => 'required|string|size:6']);
        $user = auth('api')->user(); 
        
        $token = $this->authService->verify2fa($user, $request->otp);
        
        return $token 
            ? $this->respondWithToken($token, $user, '2FA verification successful')
            : $this->errorResponse('Invalid or expired 2FA code', 422);
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
            $result = $this->authService->login($request->validated());
            
            switch ($result['status']) {
                case 'success':
                    return $this->respondWithToken($result['token'], $result['user'], 'Login successful');
                
                case '2fa_required':
                    $token = $this->authService->generateToken($result['user']);
                    return $this->respondWithToken($token, $result['user'], '2FA code sent to your email.', 200);

                case 'unverified':
                    $token = $this->authService->generateToken($result['user']);
                    return $this->respondWithToken($token, $result['user'], 'Email not verified.', 403);
                
                default:
                    return $this->errorResponse($result['message'], 401);
            }
        } catch (Exception $e) {
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
            // Ensure token is present
            if (!$token = auth('api')->getToken()) {
                // Try grabbing from request if not set on guard
                $token = request()->bearerToken();
                if ($token) {
                    auth('api')->setToken($token);
                } else {
                     throw new Exception('Token not provided');
                }
            }
            
            $newToken = auth('api')->refresh();
            $user = auth('api')->setToken($newToken)->user();
            
            return $this->respondWithToken($newToken, $user, 'Token refreshed successfully');
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Refresh error: ' . $e->getMessage());
            return $this->errorResponse('Refresh failed: ' . $e->getMessage(), 401);
        }
    }
}
