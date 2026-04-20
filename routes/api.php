<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\WidgetController;

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
    Route::get('/{id}/details',    [ChatController::class, 'details']);
    Route::post('/{id}/send',      [ChatController::class, 'send']);
    Route::get('/{id}/messages',   [ChatController::class, 'messages']);
    Route::post('/{id}/typing',    [ChatController::class, 'typing']);
});
