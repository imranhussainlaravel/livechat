<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'assigned', 'active', 'closed', 'transferred'])
                ->default('pending')->index();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal')->index();
            $table->string('subject')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('followup_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['assigned_agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
