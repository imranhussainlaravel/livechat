<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['USER', 'AGENT', 'SYSTEM', 'EVENT']);
            $table->unsignedBigInteger('sender_id')->nullable(); // Can be user_id, visitor_id, or null depending on type
            $table->text('content');
            $table->timestamps(); // includes created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};