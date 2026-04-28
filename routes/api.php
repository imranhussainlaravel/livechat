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
 * Custom Broadcast Auth for Visitors & Agents
 *
 * Authenticated agents → Broadcast::auth()
 * Unauthenticated visitors → verify via X-Session-Token, then Pusher auth
 */
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    // Visitor auth via session token (uses header)
    $sessionToken = $request->header('X-Session-Token');
    $channelName  = $request->input('channel_name');
    $socketId     = $request->input('socket_id');

    if (!$sessionToken || !$channelName || !$socketId) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Extract chat ID from "private-chat.123"
    if (!preg_match('/^private-chat\.(\d+)$/', $channelName, $matches)) {
        return response()->json(['message' => 'Invalid channel'], 403);
    }

    $chatId = (int) $matches[1];
    $chat   = \App\Models\Chat::with('visitor')->find($chatId);

    if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $sessionToken) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Generate Pusher auth signature for visitors
    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        [
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'useTLS' => true
        ]
    );

    return response($pusher->authorizeChannel($channelName, $socketId), 200, [
        'Content-Type' => 'application/json',
    ]);
});



