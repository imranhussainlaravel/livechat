<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\AgentWebController;
use App\Http\Controllers\ChatWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json(['status' => 'running', 'app' => 'LiveChat UI', 'version' => 'v1']);
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('login', [AdminWebController::class, 'loginForm'])->name('admin.login');
    Route::post('login', [AdminWebController::class, 'login']);

    Route::middleware(['admin.auth'])->group(function () {
        Route::get('dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('conversations', [AdminWebController::class, 'conversations'])->name('admin.conversations');
        Route::get('conversation/{id}', [AdminWebController::class, 'conversation'])->name('admin.conversation');
    });
});

// Agent Routes
Route::prefix('agent')->group(function () {
    Route::get('login', [AgentWebController::class, 'loginForm'])->name('agent.login');
    Route::post('login', [AgentWebController::class, 'login']);

    Route::middleware(['agent.auth'])->group(function () {
        Route::get('dashboard', [AgentWebController::class, 'dashboard'])->name('agent.dashboard');
        Route::get('conversations', [AgentWebController::class, 'conversations'])->name('agent.conversations');
        Route::get('conversation/{id}', [AgentWebController::class, 'conversation'])->name('agent.conversation');
    });
});

// Chat UI
Route::get('chat/{conversation_id}', [ChatWebController::class, 'show'])->name('chat.show');
