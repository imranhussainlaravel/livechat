<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. Add soft deletes where needed
        // ---------------------------------------------------------------
        Schema::table('chats', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('followups', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->softDeletes();
        });

        // ---------------------------------------------------------------
        // 2. Expand priority ENUM to include 'urgent'
        // ---------------------------------------------------------------
        DB::statement("ALTER TABLE `chats` MODIFY `priority` ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal'");

        // ---------------------------------------------------------------
        // 3. Add additional performance indexes
        // ---------------------------------------------------------------
        Schema::table('chats', function (Blueprint $table) {
            $table->index('visitor_id');                       // fast visitor lookup
            $table->index(['status', 'created_at']);           // queue ordering
            $table->index(['assigned_agent_id', 'created_at']); // agent history
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('sender_id');                        // sender lookup
        });

        Schema::table('chat_transfers', function (Blueprint $table) {
            $table->index('from_agent_id');
            $table->index('to_agent_id');
        });

        Schema::table('followups', function (Blueprint $table) {
            $table->index(['status', 'followup_time']);        // pending followup scans
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('chat_id');
            $table->index('agent_id');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        // Remove soft deletes
        Schema::table('chats', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['visitor_id']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['assigned_agent_id', 'created_at']);
        });

        Schema::table('followups', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['status', 'followup_time']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['chat_id']);
            $table->dropIndex(['agent_id']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id']);
        });

        Schema::table('chat_transfers', function (Blueprint $table) {
            $table->dropIndex(['from_agent_id']);
            $table->dropIndex(['to_agent_id']);
        });

        DB::statement("ALTER TABLE `chats` MODIFY `priority` ENUM('low','normal','high') NOT NULL DEFAULT 'normal'");
    }
};
