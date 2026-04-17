<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_messages', function (Blueprint $box) {
            $box->id();
            $box->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $box->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $box->text('message');
            $box->boolean('is_read')->default(false);
            $box->timestamps();

            $box->index(['sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_messages');
    }
};
