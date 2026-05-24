<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users');
            $table->foreignId('hostel_type_id')->constrained('hostel_types');

            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('gender_policy', ['male', 'female', 'mixed'])->default('mixed');

            $table->integer('price');
            $table->enum('price_unit', ['seat/month', 'day'])->default('seat/month');
            $table->integer('advance')->nullable();

            $table->boolean('meal_included')->default(false);
            $table->integer('meal_price')->nullable();
            $table->string('curfew_time')->nullable();

            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->string('landmark')->nullable();
            $table->string('landmark_distance')->nullable();
            $table->string('address')->nullable();

            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas');
            $table->foreignId('union_id')->nullable()->constrained('unions');
            $table->float('coord_x')->nullable(); // longitude
            $table->float('coord_y')->nullable(); // latitude

            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();

            $table->text('rules')->nullable(); // JSON-encoded array of rule strings

            $table->enum('status', ['draft', 'pending', 'active', 'rejected'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->integer('views')->default(0);
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('hostel_type_id');
            $table->index('gender_policy');
            $table->index(['status', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostels');
    }
};
