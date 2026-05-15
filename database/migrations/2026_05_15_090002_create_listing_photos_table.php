<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index('listing_id');
            $table->index(['listing_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_photos');
    }
};
