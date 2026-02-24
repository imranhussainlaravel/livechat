<?php

use Illuminate\Support\Facades\Route;
use Src\Api\Controllers\AdminAuthController;
use Src\Api\Controllers\AgentController;
use Src\Api\Controllers\AgentCrudController;
use Src\Api\Controllers\DashboardController;
use Src\Api\Controllers\VisitorController;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public ──────────────────────────────────────────────

    Route::post('admin/login', [AdminAuthController::class, 'login']);
    Route::post('agent/login', [AgentController::class, 'login']);

    // ── Visitor (no auth — identified by session token) ────

    Route::prefix('visitor')->group(function () {
        Route::post('session', [VisitorController::class, 'initSession']);
        Route::post('conversations', [VisitorController::class, 'startConversation']);
        Route::post('conversations/{id}/messages', [VisitorController::class, 'sendMessage']);
        Route::get('conversations/{id}/messages', [VisitorController::class, 'messages']);
    });

    // ── Admin (Sanctum token required) ─────────────────────

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('agents', [AgentCrudController::class, 'index']);
        Route::post('agents', [AgentCrudController::class, 'store']);
        Route::get('agents/{id}', [AgentCrudController::class, 'show']);
        Route::put('agents/{id}', [AgentCrudController::class, 'update']);
        Route::delete('agents/{id}', [AgentCrudController::class, 'destroy']);
    });

    // ── Agent (Sanctum token required) ─────────────────────

    Route::middleware('auth:sanctum')->prefix('agent')->group(function () {
        Route::post('heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('status', [AgentController::class, 'status']);
        Route::post('conversations/{id}/messages', [AgentController::class, 'sendMessage']);
        Route::post('conversations/{id}/close', [AgentController::class, 'closeConversation']);
    });
});
