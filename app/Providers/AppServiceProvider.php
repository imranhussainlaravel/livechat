<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fix for older MySQL versions with utf8mb4 max key length
        Schema::defaultStringLength(191);

        // Security: Define standard API Rate Limit (60 req / min per User or IP)
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Security: Define stricter Chat Rate Limit for public widget (20 req / min per IP)
        \Illuminate\Support\Facades\RateLimiter::for('chat', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->ip());
        });
        // Shared Data for sidebar/header (Internal Messages)
        \Illuminate\Support\Facades\View::composer(['components.sidebar', 'components.header'], function ($view) {
            if (auth()->check()) {
                $unreadAgents = \App\Models\User::whereHas('sentInternalMessages', function($q) {
                    $q->where('receiver_id', auth()->id())->where('is_read', false);
                })->withCount(['sentInternalMessages as unread_count' => function($q) {
                    $q->where('receiver_id', auth()->id())->where('is_read', false);
                }])->get();
                
                $view->with('unreadAgents', $unreadAgents);
                $view->with('totalUnreadInternal', $unreadAgents->sum('unread_count'));
            }
        });
    }
}
