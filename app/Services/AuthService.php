<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    /**
     * Authenticate a user and return a Sanctum token.
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens for clean single-session
        $user->tokens()->delete();

        $token = $user->createToken('livechat')->plainTextToken;

        $this->users->updateStatus($user->id, 'online');

        return [
            'user'  => $user->fresh(),
            'token' => $token,
        ];
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();
            $this->users->updateStatus($user->id, 'offline');
        }
    }
}
