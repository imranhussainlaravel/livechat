<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\AgentController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\FollowupController;
use App\Http\Controllers\API\TicketController;

/*
|--------------------------------------------------------------------------
| Public Endpoints — Website Chat Widget
|--------------------------------------------------------------------------
*/

Route::prefix('chat')->group(function () {
    Route::post('/start',          [ChatController::class, 'start']);
    Route::post('/{id}/send',      [ChatController::class, 'send']);
    Route::get('/{id}/messages',   [ChatController::class, 'messages']);
});

/*
|--------------------------------------------------------------------------
| Auth Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Agent Endpoints (Auth + Agent Role Required)
|--------------------------------------------------------------------------
*/
Route::prefix('agent')
    ->middleware(['auth:sanctum', 'role.agent'])
    ->group(function () {
        Route::get('/chats',                [AgentController::class, 'chats']);
        Route::post('/chat/{id}/accept',    [AgentController::class, 'accept']);
        Route::post('/chat/{id}/message',   [AgentController::class, 'message']);
        Route::post('/chat/{id}/transfer',  [AgentController::class, 'transfer']);
        Route::post('/chat/{id}/close',     [AgentController::class, 'close']);
        Route::patch('/status',             [AgentController::class, 'updateStatus']);

        // Followups
        Route::get('/followups',               [FollowupController::class, 'index']);
        Route::post('/followups',              [FollowupController::class, 'store']);
        Route::patch('/followups/{id}/complete', [FollowupController::class, 'complete']);
        Route::patch('/followups/{id}/cancel',   [FollowupController::class, 'cancel']);

        // Tickets
        Route::post('/tickets',         [TicketController::class, 'store']);
        Route::patch('/tickets/{id}',   [TicketController::class, 'update']);
    });

/*
|--------------------------------------------------------------------------
| Admin Endpoints (Auth + Admin Role Required)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role.admin'])
    ->group(function () {
        Route::get('/dashboard',   [AdminController::class, 'dashboard']);
        Route::get('/chats',       [AdminController::class, 'chats']);
        Route::get('/activities',  [AdminController::class, 'activities']);

        // Agent Management
        Route::get('/agents',      [AdminController::class, 'agents']);
        Route::post('/agents',     [AdminController::class, 'storeAgent']);
        Route::delete('/agents/{id}', [AdminController::class, 'destroyAgent']);

        // Settings
        Route::get('/settings',    [AdminController::class, 'settings']);
        Route::put('/settings',    [AdminController::class, 'updateSettings']);
    });
