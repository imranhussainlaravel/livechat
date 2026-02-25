<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    /**
     * POST /api/auth/login
     *
     * Single login endpoint for all roles.
     * Returns a Sanctum token + role-based redirect path.
     *
     *  Admin  → { redirect_to: "/admin/dashboard" }
     *  Agent  → { redirect_to: "/agent/dashboard" }
     *  Invalid → 422 error
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Login successful.',
            'data'    => [
                'user'        => new UserResource($result['user']),
                'token'       => $result['token'],
                'redirect_to' => $result['redirect_to'],
                'expires_in'  => $result['expires_in'],
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * Revokes the current token and sets user offline.
     */
    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/auth/me
     *
     * Returns the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }
}
