<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Allow the new "production" role. The column was an enum('admin','agent');
            // convert it to a plain string so additional roles don't require a schema change.
            $table->string('role')->default('agent')->change();

            // CRM fields. `account_status` is the CRM "active/inactive" concept, kept
            // separate from LiveChat's presence `status` (online/away/busy/offline).
            $table->string('work_scope')->nullable()->after('max_chats');
            $table->string('account_status')->default('active')->after('work_scope');
            $table->foreignId('created_by_admin_id')->nullable()->after('account_status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_admin_id');
            $table->dropColumn(['work_scope', 'account_status']);
            $table->enum('role', ['admin', 'agent'])->default('agent')->change();
        });
    }
};
