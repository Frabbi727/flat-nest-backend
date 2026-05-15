<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('district_id')
                ->constrained('districts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name', 25);
            $table->string('bn_name', 25);
            $table->string('url', 50);

            $table->timestamps();

            // index (optional but good for fast lookup)
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upazilas');
    }
};
