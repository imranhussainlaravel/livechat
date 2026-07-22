<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Whether this user may use the Live Chat side of the app.
            // CRM-only users have this set to false; admins always have access.
            $table->boolean('can_live_chat')->default(true)->after('account_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_live_chat');
        });
    }
};
