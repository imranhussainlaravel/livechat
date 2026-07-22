<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

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
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        }

        // CRM-only users (Live Chat disabled) land in the CRM.
        return $user->canLiveChat()
            ? redirect('/agent/dashboard')
            : redirect()->route('crm.leads.index');
    }

    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Agent Routes — Session Auth + Agent Role
|--------------------------------------------------------------------------
*/

Route::prefix('agent')
    ->middleware(['auth', 'role.agent', 'can.livechat'])
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
        Route::delete('/queue/{id}', [\App\Http\Controllers\Agent\AgentQueueController::class, 'destroy'])
            ->name('queue.destroy');

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
        // Create a CRM lead from a chat (Phase 5) — separate controller so live chat is untouched
        Route::post('/chats/{id}/create-lead', [\App\Http\Controllers\Agent\ChatLeadController::class, 'store'])
            ->name('chats.createLead');
        Route::patch('/visitor/{id}', [\App\Http\Controllers\Agent\ChatController::class, 'updateVisitor'])
            ->name('visitor.update');

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

        // Team Chat (internal messaging: DMs + group channels)
        Route::get('/other-agents', [\App\Http\Controllers\Agent\AgentChatController::class, 'index'])
            ->name('agents.index');
        // NOTE: 'messages' and 'channel' declared before '{id}' so they aren't captured as an id.
        Route::get('/other-agents/messages', [\App\Http\Controllers\Agent\AgentChatController::class, 'fetch'])
            ->name('agents.fetch');
        Route::post('/other-agents/channel/{key}/message', [\App\Http\Controllers\Agent\AgentChatController::class, 'storeChannel'])
            ->name('agents.channelMessage');
        Route::get('/other-agents/{id}', [\App\Http\Controllers\Agent\AgentChatController::class, 'show'])
            ->name('agents.show');
        Route::post('/other-agents/{id}/message', [\App\Http\Controllers\Agent\AgentChatController::class, 'store'])
            ->name('agents.message');
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

        // Agent Management
        Route::get('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'index'])
            ->name('agents.index');
        Route::post('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'store'])
            ->name('agents.store');
        Route::delete('/agents/{id}', [\App\Http\Controllers\Admin\AgentController::class, 'destroy'])
            ->name('agents.destroy');
        Route::patch('/agents/{id}/live-chat', [\App\Http\Controllers\Admin\AgentController::class, 'toggleLiveChat'])
            ->name('agents.toggleLiveChat');

        // Queue
        Route::get('/queue', [\App\Http\Controllers\Admin\QueueController::class, 'index'])
            ->name('queue.index');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
            ->name('settings.update');
        Route::put('/settings/ai', [\App\Http\Controllers\Admin\SettingsController::class, 'updateAi'])
            ->name('settings.updateAi');

        // Reports
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])
            ->name('reports.index');
    });

/*
|--------------------------------------------------------------------------
| CRM Routes — shared by agents and admins (records are scoped per-user
| in the controllers). Placeholder pages for now; real modules land in
| Phase 3+. Route names (crm.*.index) are stable so the sidebar is final.
|--------------------------------------------------------------------------
*/

Route::prefix('crm')
    ->middleware(['auth'])
    ->name('crm.')
    ->group(function () {
        Route::resource('companies', \App\Http\Controllers\Crm\CompanyController::class);
        Route::resource('contacts', \App\Http\Controllers\Crm\ContactController::class);

        Route::resource('leads', \App\Http\Controllers\Crm\LeadController::class);
        Route::post('leads/{lead}/status', [\App\Http\Controllers\Crm\LeadController::class, 'updateStatus'])->name('leads.updateStatus');
        Route::post('leads/{lead}/activity', [\App\Http\Controllers\Crm\LeadController::class, 'addActivity'])->name('leads.activity');
        Route::post('leads/{lead}/mark-lost', [\App\Http\Controllers\Crm\LeadController::class, 'markLost'])->name('leads.markLost');
        Route::post('leads/{lead}/convert', [\App\Http\Controllers\Crm\LeadController::class, 'convert'])->name('leads.convert');

        Route::resource('deals', \App\Http\Controllers\Crm\DealController::class);
        Route::post('deals/{deal}/stage', [\App\Http\Controllers\Crm\DealController::class, 'updateStage'])->name('deals.updateStage');
        Route::post('deals/{deal}/mark-won', [\App\Http\Controllers\Crm\DealController::class, 'markWon'])->name('deals.markWon');
        Route::post('deals/{deal}/mark-lost', [\App\Http\Controllers\Crm\DealController::class, 'markLost'])->name('deals.markLost');

        Route::resource('products', \App\Http\Controllers\Crm\ProductController::class);

        Route::resource('orders', \App\Http\Controllers\Crm\OrderController::class)->only(['index', 'show', 'edit', 'update']);

        // Quotations — 'create' declared before '{quotation}' so it isn't captured as a param
        Route::get('quotations/create', [\App\Http\Controllers\Crm\QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations', [\App\Http\Controllers\Crm\QuotationController::class, 'store'])->name('quotations.store');
        Route::get('quotations/{quotation}', [\App\Http\Controllers\Crm\QuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/edit', [\App\Http\Controllers\Crm\QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('quotations/{quotation}', [\App\Http\Controllers\Crm\QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('quotations/{quotation}', [\App\Http\Controllers\Crm\QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::get('quotations/{quotation}/pdf', [\App\Http\Controllers\Crm\QuotationController::class, 'pdf'])->name('quotations.pdf');
        Route::post('quotations/{quotation}/approve-discount', [\App\Http\Controllers\Crm\QuotationController::class, 'approveDiscount'])->name('quotations.approveDiscount');
    });

// Standard broadcast auth for agents (uses session)
Broadcast::routes();
require __DIR__.'/channels.php';
