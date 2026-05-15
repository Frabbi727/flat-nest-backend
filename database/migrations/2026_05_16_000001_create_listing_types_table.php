<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // stored value: Family, Bachelor …
            $table->string('label');            // display: Family Flat, Bachelor Room …
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_types');
    }
};
