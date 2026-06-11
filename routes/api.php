<?php

use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\WidgetController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

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
    Route::post('/start', [ChatController::class, 'start']);
    Route::get('/recover', [ChatController::class, 'recover']);
    Route::get('/details', [ChatController::class, 'details']); // Updated to match flat pattern
    Route::post('/send', [ChatController::class, 'send']);
    Route::get('/messages', [ChatController::class, 'messages']);
    Route::post('/typing', [ChatController::class, 'typing']);
});

/**
 * Custom Broadcast Auth for Visitors & Agents
 *
 * Authenticated agents → Broadcast::auth()
 * Unauthenticated visitors → verify via X-Session-Token, then Pusher auth
 */
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $sessionToken = $request->header('X-Session-Token') ?? $request->input('session_token');
    $channelName = $request->input('channel_name');
    $socketId = $request->input('socket_id');

    if (! $sessionToken || ! $channelName || ! $socketId) {
        return response()->json(['message' => 'Unauthorized - missing fields'], 403);
    }

    // Extract chat ID from "private-chat.123" or just "chat.123"
    if (! preg_match('/^(private-)?chat\.(\d+)$/', $channelName, $matches)) {
        return response()->json(['message' => 'Invalid channel format'], 403);
    }

    $chatId = (int) ($matches[2] ?? $matches[1]);
    $chat = \App\Models\Chat::with('visitor')->find($chatId);

    if (! $chat) {
        return response()->json(['message' => 'Chat not found'], 403);
    }

    if (! $chat->visitor || $chat->visitor->session_token !== $sessionToken) {
        \Log::warning('Broadcast auth failed for visitor channel', [
            'chat_id' => $chatId,
            'channel' => $channelName,
        ]);

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        [
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'useTLS' => true,
        ]
    );

    // authorizeChannel returns a JSON string, decode it so response()->json() doesn't double-encode
    $authPayload = json_decode($pusher->authorizeChannel($channelName, $socketId), true);

    return response()->json($authPayload);
});
