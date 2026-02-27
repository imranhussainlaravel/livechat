<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private ActivityService         $activity,
    ) {}

    /**
     * Authenticate a user via session and return role-based redirect path.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $ip = null, ?string $userAgent = null): array
    {
        $user = $this->users->findByEmail($email);

        // ── Credential check ────────────────────────────────────────
        if (! $user || ! Hash::check($password, $user->password)) {
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

        // ── Create session (replaces Sanctum token) ─────────────────
        Auth::login($user, remember: true);
        request()->session()->regenerate();

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
            'user'        => $user->fresh(),
            'redirect_to' => $redirect,
        ];
    }

    /**
     * Logout the authenticated user and set offline.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->activity->log(
                userId: $user->id,
                action: 'auth.logout',
                referenceType: 'User',
                referenceId: $user->id,
            );

            $this->users->updateStatus($user->id, 'offline');
        }

        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
