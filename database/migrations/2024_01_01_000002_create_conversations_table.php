<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // maps to visitor_sessions
            $table->string('queue')->nullable();
            $table->enum('state', ['NEW', 'PENDING', 'ACTIVE', 'WAITING', 'TRANSFERRED', 'ESCALATED', 'CLOSED'])->default('NEW');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('sla_state', ['NORMAL', 'WARNING', 'BREACHED'])->default('NORMAL');
            $table->timestamps();
            
            $table->foreign('session_id')->references('session_id')->on('visitor_sessions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};