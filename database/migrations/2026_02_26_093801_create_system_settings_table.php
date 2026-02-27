<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_chats_per_agent')->default(5);
            $table->integer('queue_timeout_minutes')->default(10);
            $table->integer('auto_close_minutes')->default(30);
            $table->integer('followup_reminder_minutes')->default(15);
            $table->time('working_hours_start')->default('09:00:00');
            $table->time('working_hours_end')->default('17:00:00');
            $table->string('widget_primary_color')->default('#000000');
            $table->string('widget_name')->default('LiveChat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
