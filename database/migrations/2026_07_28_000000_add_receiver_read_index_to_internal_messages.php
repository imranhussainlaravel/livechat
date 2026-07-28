<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            // The unread-count/poll queries filter by receiver_id + is_read
            // (AppServiceProvider composer, AgentChatController::unreadSummary,
            // both hit on every page load / every ~12s poll). The existing
            // ['sender_id', 'receiver_id'] index can't serve that filter.
            $table->index(['receiver_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->dropIndex(['receiver_id', 'is_read']);
        });
    }
};
