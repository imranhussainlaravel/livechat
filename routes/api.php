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
    Route::post('/{id}/typing',    [ChatController::class, 'typing']);
});

/*
|--------------------------------------------------------------------------
| Auth Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute per IP

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
        Route::get('/chat/{id}',            [AgentController::class, 'show']);
        Route::post('/chat/{id}/accept',    [AgentController::class, 'accept']);
        Route::post('/chat/{id}/message',   [AgentController::class, 'message']);
        Route::post('/chat/{id}/transfer',  [AgentController::class, 'transfer']);
        Route::post('/chat/{id}/close',     [AgentController::class, 'close']);
        Route::patch('/chat/{id}/status',   [AgentController::class, 'updateChatStatus']);
        Route::post('/chat/{id}/visitor-note', [AgentController::class, 'addVisitorNote']);
        Route::patch('/status',             [AgentController::class, 'updateStatus']);
        Route::get('/metrics',              [AgentController::class, 'metrics']);

        // WebSocket triggers
        Route::post('/chat/{id}/typing',    [AgentController::class, 'typing']);
        Route::post('/chat/{id}/join',      [AgentController::class, 'joinChat']);
        Route::post('/chat/{id}/leave',     [AgentController::class, 'leaveChat']);

        // Followups
        Route::get('/followups',               [FollowupController::class, 'index']);
        Route::post('/followups',              [FollowupController::class, 'store']);
        Route::patch('/followups/{id}/complete', [FollowupController::class, 'complete']);
        Route::patch('/followups/{id}/cancel',   [FollowupController::class, 'cancel']);

        // Tickets
        Route::post('/tickets',                       [TicketController::class, 'store']);
        Route::patch('/tickets/{id}',                 [TicketController::class, 'update']);
        Route::patch('/tickets/{id}/interested',      [TicketController::class, 'markInterested']);
        Route::patch('/tickets/{id}/not-interested',  [TicketController::class, 'markNotInterested']);
        Route::post('/tickets/{id}/quotation',        [TicketController::class, 'sendQuotation']);
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

        // Reports & Insights
        Route::get('/reports',     [\App\Http\Controllers\API\InsightsController::class, 'index']);
    });
