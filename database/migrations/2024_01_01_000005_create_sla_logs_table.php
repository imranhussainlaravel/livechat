<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('breach_type');
            $table->timestamp('triggered_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_logs');
    }
};