<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_amenity', function (Blueprint $table) {
            $table->foreignUuid('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->primary(['hostel_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_amenity');
    }
};
