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
 * Custom Broadcast Auth for Visitors
 */
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
});
