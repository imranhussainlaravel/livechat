<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            // Group-channel messages (e.g. "general") have a channel + no receiver.
            $table->string('channel')->nullable()->after('receiver_id')->index();
            $table->foreignId('receiver_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->dropColumn('channel');
            // receiver_id left nullable — harmless; original data unaffected.
        });
    }
};
