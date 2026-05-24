<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // slug: student, women, mess, corporate, worker, medical
            $table->string('label');          // display: Student, Women's, Bachelor Mess, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_types');
    }
};
