<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        // Shared Data for sidebar/header (Internal Messages).
        // Memoized with a `static` so the two views sharing this composer
        // (sidebar + header, both rendered on every authenticated page) run
        // this query set ONCE per request instead of twice.
        \Illuminate\Support\Facades\View::composer(['components.sidebar', 'components.header'], function ($view) {
            if (! auth()->check()) {
                return;
            }

            static $data = null;

            if ($data === null) {
                $unreadAgents = \App\Models\User::whereHas('sentInternalMessages', function ($q) {
                    $q->where('receiver_id', auth()->id())->where('is_read', false);
                })->withCount(['sentInternalMessages as unread_count' => function ($q) {
                    $q->where('receiver_id', auth()->id())->where('is_read', false);
                }])->get();

                // Group-channel unread (per-user read marker) added to the DM total
                // so the sidebar/header badge reflects channels too. Wrapped so a
                // pending migration (missing channel_reads table) can never 500
                // every page — it just falls back to the DM-only count. Uses a
                // single grouped query instead of one COUNT per channel.
                $channelUnread = 0;
                try {
                    $channels = array_keys(\App\Http\Controllers\Agent\AgentChatController::CHANNELS);
                    $reads = \App\Models\ChannelRead::where('user_id', auth()->id())
                        ->pluck('last_read_message_id', 'channel');

                    $channelUnread = \App\Models\InternalMessage::whereIn('channel', $channels)
                        ->where('sender_id', '!=', auth()->id())
                        ->where(function ($q) use ($channels, $reads) {
                            foreach ($channels as $ch) {
                                $q->orWhere(function ($qq) use ($ch, $reads) {
                                    $qq->where('channel', $ch)->where('id', '>', ($reads[$ch] ?? 0));
                                });
                            }
                        })
                        ->count();
                } catch (\Throwable $e) {
                    $channelUnread = 0;
                }

                $data = [
                    'unreadAgents' => $unreadAgents,
                    'totalUnreadInternal' => $unreadAgents->sum('unread_count') + $channelUnread,
                ];
            }

            $view->with($data);
        });
    }
}
