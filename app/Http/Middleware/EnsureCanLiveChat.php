<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks Live Chat features for users whose Live Chat access has been
 * switched off (CRM-only users). Admins always pass.
 */
class EnsureCanLiveChat
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canLiveChat()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Live Chat access is disabled for your account.'], 403);
            }

            return redirect()->route('crm.leads.index')
                ->with('error', 'Live Chat access is disabled for your account.');
        }

        return $next($request);
    }
}
