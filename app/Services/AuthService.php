<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerifyEmailOTP;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data)
    {
        // Hash Password
        $data['password'] = Hash::make($data['password']);

        // Set status to pending for email verification
        $data['status'] = 'pending';

        // Generate 6-digit OTP
        $data['otp'] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['otp_expires_at'] = Carbon::now()->addMinutes(10);

        // Create User
        $user = User::create($data);

        // Assign Default Role (Spatie)
        $user->assignRole('user');

        // Send Verification OTP
        $user->notify(new VerifyEmailOTP($user->otp));

        return $user;
    }

    /**
     * Verify User Email OTP
     */
    public function verifyOtp(User $user, string $otp): bool
    {
        if ($user->otp !== $otp || $user->otp_expires_at->isPast()) {
            return false;
        }

        // Update User Status
        $user->update([
            'status' => 'active',
            'email_verified_at' => Carbon::now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Resend Verification OTP
     */
    public function resendOtp(User $user): void
    {
        $user->update([
            'otp' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $user->notify(new VerifyEmailOTP($user->otp));
    }

    public function login(array $credentials)
    {
        $identity = $credentials['identity'];
        $password = $credentials['password'];

        // Check if identity is email, phone or username
        $fieldType = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 
                    (preg_match('/^\+?\d+$/', $identity) ? 'phone' : 'username');

        // Check if user is verified
        $user = User::where($fieldType, $identity)->first();
        if ($user && !$user->hasVerifiedEmail()) {
            throw new \Exception('Email not verified. Please verify your email first.');
        }

        if (!$token = auth('api')->attempt([$fieldType => $identity, 'password' => $password])) {
            return null;
        }

        return $token;
    }

    /**
     * Generate JWT Token for user
     */
    public function generateToken($user)
    {
        return auth('api')->login($user);
    }
}
