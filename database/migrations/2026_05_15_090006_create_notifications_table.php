<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('title');
            $table->text('body');
            $table->uuid('reference_id')->nullable();
            $table->boolean('is_unread')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_unread']);
            $table->index(['user_id', 'kind']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
