<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sender_id')->references('id')->on('users');
            $table->text('text');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('chat_id');
            $table->index(['chat_id', 'created_at']);
            $table->index(['chat_id', 'is_read']);
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
