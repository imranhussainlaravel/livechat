<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /** Token lifetime in minutes (24 hours). */
    private const TOKEN_EXPIRATION_MINUTES = 1440;

    public function __construct(
        private UserRepositoryInterface $users,
        private ActivityService         $activity,
    ) {}

    /**
     * Authenticate a user and return a Sanctum token with role-based redirect.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $ip = null, ?string $userAgent = null): array
    {
        $user = $this->users->findByEmail($email);

        // ── Credential check ────────────────────────────────────────
        if (! $user || ! Hash::check($password, $user->password)) {
            // Log failed attempt
            $this->activity->log(
                userId: $user?->id,
                action: 'auth.login_failed',
                referenceType: 'User',
                referenceId: $user?->id,
                metadata: ['ip' => $ip, 'email' => $email],
            );

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // ── Rehash password if needed (auto-upgrade bcrypt rounds) ──
        if (Hash::needsRehash($user->password)) {
            $user->update(['password' => Hash::make($password)]);
        }

        // ── Revoke all previous tokens (single-session enforcement) ─
        $user->tokens()->delete();

        // ── Create new token with expiration ────────────────────────
        $token = $user->createToken(
            'livechat',
            ['*'],
            now()->addMinutes(self::TOKEN_EXPIRATION_MINUTES),
        )->plainTextToken;

        // ── Set user online ─────────────────────────────────────────
        $this->users->updateStatus($user->id, 'online');

        // ── Log successful login ────────────────────────────────────
        $this->activity->log(
            userId: $user->id,
            action: 'auth.login',
            referenceType: 'User',
            referenceId: $user->id,
            metadata: [
                'ip'         => $ip,
                'user_agent' => $userAgent,
                'role'       => $user->role->value,
            ],
        );

        // ── Determine redirect based on role ────────────────────────
        $redirect = match ($user->role) {
            UserRole::ADMIN => '/admin/dashboard',
            UserRole::AGENT => '/agent/dashboard',
        };

        return [
            'user'       => $user->fresh(),
            'token'      => $token,
            'redirect_to' => $redirect,
            'expires_in' => self::TOKEN_EXPIRATION_MINUTES * 60, // seconds
        ];
    }

    /**
     * Logout the authenticated user, revoke token and set offline.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            // Log logout activity
            $this->activity->log(
                userId: $user->id,
                action: 'auth.logout',
                referenceType: 'User',
                referenceId: $user->id,
            );

            $user->currentAccessToken()->delete();
            $this->users->updateStatus($user->id, 'offline');
        }
    }
}
