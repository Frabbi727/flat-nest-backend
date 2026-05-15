<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // slug: wifi, ac, parking
            $table->string('label');           // display: WiFi, Air Conditioning
            $table->timestamps();
        });

        Schema::create('listing_amenity', function (Blueprint $table) {
            $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->primary(['listing_id', 'amenity_id']);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->json('amenities')->nullable();
        });

        Schema::dropIfExists('listing_amenity');
        Schema::dropIfExists('amenities');
    }
};