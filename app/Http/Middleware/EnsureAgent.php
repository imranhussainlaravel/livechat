<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgent
{
    /**
     * Allow both agents AND admins through — admins can see agent views too.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, [UserRole::AGENT, UserRole::ADMIN], true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Agent access required.'], 403);
            }
            abort(403, 'Forbidden. Agent access required.');
        }

        return $next($request);
    }
}
