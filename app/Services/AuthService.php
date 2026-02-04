<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetOTP;
use App\Notifications\VerifyEmailOTP;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Resolve user by identity (email, username, or phone)
     */
    protected function resolveUser(string $identity): ?User
    {
        $fieldType = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 
                    (preg_match('/^\+?\d+$/', $identity) ? 'phone' : 'username');

        return User::where($fieldType, $identity)->first();
    }

    /**
     * Register a new user
     */
    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'pending';
        $data['otp'] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['otp_expires_at'] = Carbon::now()->addSeconds(150);

        $user = User::create($data);
        $user->assignRole('user');
        $user->notify(new VerifyEmailOTP($user->otp));

        return $user;
    }

    /**
     * Forgot Password - Send OTP
     */
    public function forgotPassword(string $identity): void
    {
        $user = $this->resolveUser($identity);
        
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $user->update([
            'otp' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'otp_expires_at' => Carbon::now()->addSeconds(150),
        ]);

        $user->notify(new PasswordResetOTP($user->otp));
    }

    /**
     * Reset Password using OTP
     */
    public function resetPassword(string $identity, string $otp, string $password): bool
    {
        $user = $this->resolveUser($identity);

        if (!$user || $user->otp !== $otp || $user->otp_expires_at->isPast()) {
            return false;
        }

        $user->update([
            'password' => Hash::make($password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Toggle 2FA
     */
    public function toggle2FA(User $user, bool $status): void
    {
        $user->update(['two_factor_enabled' => $status]);
    }

    /**
     * Verify User Email OTP
     */
    public function verifyOtp(User $user, string $otp): bool
    {
        if ($user->otp !== $otp || $user->otp_expires_at->isPast()) {
            return false;
        }

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
            'otp_expires_at' => Carbon::now()->addSeconds(150),
        ]);

        $user->notify(new VerifyEmailOTP($user->otp));
    }

    /**
     * Login process with identity check and 2FA handling
     */
    public function login(array $credentials)
    {
        $user = $this->resolveUser($credentials['identity']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['status' => 'error', 'message' => 'Invalid credentials'];
        }

        if (!$user->hasVerifiedEmail()) {
            return ['status' => 'unverified', 'user' => $user];
        }

        if ($user->two_factor_enabled) {
            // Generate OTP for 2FA login
            $user->update([
                'otp' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'otp_expires_at' => Carbon::now()->addSeconds(150),
            ]);
            $user->notify(new VerifyEmailOTP($user->otp)); // Reuse verification template or create new one
            
            return ['status' => '2fa_required', 'user' => $user];
        }

        $token = auth('api')->login($user);
        return ['status' => 'success', 'token' => $token, 'user' => $user];
    }

    /**
     * Complete 2FA Login
     */
    public function verify2fa(User $user, string $otp)
    {
        if ($user->otp !== $otp || $user->otp_expires_at->isPast()) {
            return null;
        }

        $user->update(['otp' => null, 'otp_expires_at' => null]);
        return auth('api')->login($user);
    }

    /**
     * Generate JWT Token for user
     */
    public function generateToken($user)
    {
        return auth('api')->login($user);
    }
}
