<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('renter_id')->references('id')->on('users');
            $table->foreignUuid('owner_id')->references('id')->on('users');
            $table->foreignUuid('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->timestamps();

            $table->unique(['renter_id', 'owner_id', 'listing_id']);
            $table->index('renter_id');
            $table->index('owner_id');
            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
