<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users');
            $table->string('title');
            $table->string('area');
            $table->string('road_and_house')->nullable();
            $table->string('type');
            $table->integer('price');
            $table->integer('deposit')->nullable();
            $table->integer('beds');
            $table->integer('baths');
            $table->integer('size')->nullable();
            $table->text('description')->nullable();
            $table->float('coord_x')->nullable();
            $table->float('coord_y')->nullable();
            $table->json('amenities')->nullable();
            $table->string('status')->default('pending');
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('type');
            $table->index('price');
            $table->index('area');
            $table->index(['status', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
