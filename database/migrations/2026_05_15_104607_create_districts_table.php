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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('division_id')
                ->constrained('divisions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name', 25);
            $table->string('bn_name', 25);
            $table->string('lat', 15)->nullable();
            $table->string('lon', 15)->nullable();
            $table->string('url', 50);

            $table->timestamps();

            // index (optional, Laravel auto indexes FK but we add explicit for clarity)
            $table->index('division_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
