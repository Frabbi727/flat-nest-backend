<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_seats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->string('seat_number'); // e.g. "A", "B", "1", "2"
            $table->enum('status', ['vacant', 'taken', 'reserved'])->default('vacant');
            $table->timestamps();

            $table->index(['hostel_id', 'status']);
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_seats');
    }
};
