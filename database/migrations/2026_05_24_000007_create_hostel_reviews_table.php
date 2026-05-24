<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['hostel_id', 'user_id']); // one review per user per hostel
            $table->index('hostel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_reviews');
    }
};
