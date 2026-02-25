<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== UserRole::AGENT) {
            return response()->json([
                'message' => 'Forbidden. Agent access required.',
            ], 403);
        }

        return $next($request);
    }
}
