<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\WidgetController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Public API — Chat Widget Only
|--------------------------------------------------------------------------
|
| These are the ONLY API endpoints. They power the public-facing chat
| widget embedded on client websites. No authentication required.
|
*/

Route::get('/widget/config', [WidgetController::class, 'config']);

Route::prefix('chat')->middleware('throttle:chat')->group(function () {
    Route::post('/start',          [ChatController::class, 'start']);
    Route::get('/recover',        [ChatController::class, 'recover']);
    Route::get('/details',         [ChatController::class, 'details']); // Updated to match flat pattern
    Route::post('/send',           [ChatController::class, 'send']);
    Route::get('/messages',        [ChatController::class, 'messages']);
    Route::post('/typing',         [ChatController::class, 'typing']);
});

/**
 * Custom Broadcast Auth for Visitors (unauthenticated) & Agents (authenticated)
 *
 * Laravel's default Broadcast::auth() requires an authenticated user,
 * which causes a 403 for unauthenticated widget visitors.
 * This custom endpoint handles both cases:
 *   - Authenticated agents/admins → delegate to Broadcast::auth()
 *   - Unauthenticated visitors   → verify via X-Session-Token header
 */
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    // If the user is authenticated (agent/admin), use default auth
    if ($request->user()) {
        return Broadcast::auth($request);
    }

    // For unauthenticated visitors: manually authorize using session token
    $sessionToken = $request->header('X-Session-Token');
    $channelName  = $request->input('channel_name'); // e.g. "private-chat.123"
    $socketId     = $request->input('socket_id');

    if (!$sessionToken || !$channelName || !$socketId) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Extract chat ID from channel name (e.g. "private-chat.123" → 123)
    if (!preg_match('/^private-chat\.(\d+)$/', $channelName, $matches)) {
        return response()->json(['message' => 'Invalid channel'], 403);
    }

    $chatId = (int) $matches[1];
    $chat   = \App\Models\Chat::with('visitor')->find($chatId);

    if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $sessionToken) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Visitor is verified — generate auth signature (Pusher protocol)
    $key    = config('broadcasting.connections.reverb.key');
    $secret = config('broadcasting.connections.reverb.secret');
    $stringToSign = $socketId . ':' . $channelName;
    $signature    = hash_hmac('sha256', $stringToSign, $secret);

    return response()->json([
        'auth' => $key . ':' . $signature,
    ]);
});

