<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_facings', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('label', 50);
            $table->string('slug', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_facings');
    }
};
