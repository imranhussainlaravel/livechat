<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;

/*
|--------------------------------------------------------------------------
| Public API — Chat Widget Only
|--------------------------------------------------------------------------
|
| These are the ONLY API endpoints. They power the public-facing chat
| widget embedded on client websites. No authentication required.
|
*/

Route::prefix('chat')->middleware('throttle:chat')->group(function () {
    Route::post('/start',          [ChatController::class, 'start']);
    Route::post('/{id}/send',      [ChatController::class, 'send']);
    Route::get('/{id}/messages',   [ChatController::class, 'messages']);
    Route::post('/{id}/typing',    [ChatController::class, 'typing']);
});
