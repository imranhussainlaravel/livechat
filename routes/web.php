<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Guest Routes — Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect('/admin/dashboard')
            : redirect('/agent/dashboard');
    }
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Agent Routes — Session Auth + Agent Role
|--------------------------------------------------------------------------
*/

Route::prefix('agent')
    ->middleware(['auth', 'role.agent'])
    ->name('agent.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Agent\DashboardController::class, 'index'])
            ->name('dashboard');

        // Queue
        Route::get('/queue', [\App\Http\Controllers\Agent\AgentQueueController::class, 'getQueueChats'])
            ->name('queue.index');
        Route::post('/queue/{id}/join', [\App\Http\Controllers\Agent\AgentQueueController::class, 'joinChat'])
            ->name('queue.join');

        // Chats
        Route::get('/chats', [\App\Http\Controllers\Agent\ChatController::class, 'index'])
            ->name('chats.index');
        Route::get('/chats/{id}', [\App\Http\Controllers\Agent\ChatController::class, 'show'])
            ->name('chats.show');
        Route::post('/chats/{id}/accept', [\App\Http\Controllers\Agent\ChatController::class, 'accept'])
            ->name('chats.accept');
        Route::post('/chats/{id}/message', [\App\Http\Controllers\Agent\ChatController::class, 'message'])
            ->name('chats.message');
        Route::post('/chats/{id}/transfer', [\App\Http\Controllers\Agent\ChatController::class, 'transfer'])
            ->name('chats.transfer');
        Route::post('/chats/{id}/close', [\App\Http\Controllers\Agent\ChatController::class, 'close'])
            ->name('chats.close');
        Route::patch('/chats/{id}/status', [\App\Http\Controllers\Agent\ChatController::class, 'updateStatus'])
            ->name('chats.updateStatus');
        Route::post('/chats/{id}/visitor-note', [\App\Http\Controllers\Agent\ChatController::class, 'addVisitorNote'])
            ->name('chats.addVisitorNote');

        // WebSocket triggers (AJAX only)
        Route::post('/chats/{id}/typing', [\App\Http\Controllers\Agent\ChatController::class, 'typing'])
            ->name('chats.typing');
        Route::post('/chats/{id}/join', [\App\Http\Controllers\Agent\ChatController::class, 'joinChat'])
            ->name('chats.join');
        Route::post('/chats/{id}/leave', [\App\Http\Controllers\Agent\ChatController::class, 'leaveChat'])
            ->name('chats.leave');

        // Agent status (AJAX)
        Route::patch('/status', [\App\Http\Controllers\Agent\StatusController::class, 'update'])
            ->name('status.update');

        // Followups
        Route::get('/followups', [\App\Http\Controllers\Agent\FollowupController::class, 'index'])
            ->name('followups.index');
        Route::post('/followups', [\App\Http\Controllers\Agent\FollowupController::class, 'store'])
            ->name('followups.store');
        Route::patch('/followups/{id}/complete', [\App\Http\Controllers\Agent\FollowupController::class, 'complete'])
            ->name('followups.complete');
        Route::patch('/followups/{id}/cancel', [\App\Http\Controllers\Agent\FollowupController::class, 'cancel'])
            ->name('followups.cancel');

        // Tickets
        Route::post('/tickets', [\App\Http\Controllers\Agent\TicketController::class, 'store'])
            ->name('tickets.store');
        Route::patch('/tickets/{id}', [\App\Http\Controllers\Agent\TicketController::class, 'update'])
            ->name('tickets.update');
        Route::patch('/tickets/{id}/interested', [\App\Http\Controllers\Agent\TicketController::class, 'markInterested'])
            ->name('tickets.markInterested');
        Route::patch('/tickets/{id}/not-interested', [\App\Http\Controllers\Agent\TicketController::class, 'markNotInterested'])
            ->name('tickets.markNotInterested');
        Route::post('/tickets/{id}/quotation', [\App\Http\Controllers\Agent\TicketController::class, 'sendQuotation'])
            ->name('tickets.sendQuotation');
    });

/*
|--------------------------------------------------------------------------
| Admin Routes — Session Auth + Admin Role
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'role.admin'])
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Chats (view all)
        Route::get('/chats', [\App\Http\Controllers\Admin\ChatController::class, 'index'])
            ->name('chats.index');

        // Activities
        Route::get('/activities', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])
            ->name('activities.index');

        // Agent Management
        Route::get('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'index'])
            ->name('agents.index');
        Route::post('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'store'])
            ->name('agents.store');
        Route::delete('/agents/{id}', [\App\Http\Controllers\Admin\AgentController::class, 'destroy'])
            ->name('agents.destroy');

        // Queue
        Route::get('/queue', [\App\Http\Controllers\Admin\QueueController::class, 'index'])
            ->name('queue.index');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
            ->name('settings.update');

        // Reports
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])
            ->name('reports.index');
    });
