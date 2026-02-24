<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AdminController;

// VISITOR Endpoints
Route::prefix('chat')->group(function () {
    Route::post('/start', [VisitorController::class, 'start']);
    Route::post('/{id}/message', [VisitorController::class, 'message']);
    Route::get('/{id}/messages', [VisitorController::class, 'messages']);
});

// AGENT Endpoints (Assuming auth middleware would be applied here in a real app)
Route::prefix('agent')->group(function () {
    Route::get('/conversations', [AgentController::class, 'index']); // ?state=PENDING
    Route::get('/conversation/{id}', [AgentController::class, 'show']);
    Route::post('/conversation/{id}/accept', [AgentController::class, 'accept']);
    Route::post('/conversation/{id}/message', [AgentController::class, 'message']);
    Route::post('/conversation/{id}/transfer', [AgentController::class, 'transfer']);
    Route::post('/conversation/{id}/close', [AgentController::class, 'close']);
    // Heartbeat ping
    Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
});

// ADMIN Endpoints
Route::prefix('admin')->group(function () {
    Route::get('/conversations', [AdminController::class, 'conversations']);
    Route::get('/agents', [AdminController::class, 'agents']);
    Route::get('/analytics', [AdminController::class, 'analytics']);
});
