<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Src\Core\ConversationEngine\ConversationEngine;
use Src\Core\ConversationEngine\Contracts\ConversationEngineInterface;
use Src\Core\MessageTimeline\MessageTimeline;
use Src\Core\MessageTimeline\Contracts\MessageTimelineInterface;
use Src\Agent\AgentManagement\AgentManagement;
use Src\Agent\AgentManagement\Contracts\AgentManagementInterface;

class LiveChatServiceProvider extends ServiceProvider
{
    /**
     * Bind interfaces to implementations.
     */
    public function register(): void
    {
        $this->app->bind(ConversationEngineInterface::class, ConversationEngine::class);
        $this->app->bind(MessageTimelineInterface::class, MessageTimeline::class);
        $this->app->bind(AgentManagementInterface::class, AgentManagement::class);
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        // MySQL utf8mb4 compat
        Schema::defaultStringLength(191);

        // Load migrations from src/Database/Migrations
        $this->loadMigrationsFrom(base_path('src/Database/Migrations'));

        // Load API routes from src/Api/Routes
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('src/Api/Routes/api.php'));
    }
}
