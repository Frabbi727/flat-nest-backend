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
        Schema::table('listings', function (Blueprint $table) {
            $table->date('available_from')->nullable()->after('description');
            $table->unsignedTinyInteger('floor_no')->nullable()->after('available_from');
            $table->unsignedTinyInteger('facing_id')->nullable()->after('floor_no');
            $table->foreign('facing_id')->references('id')->on('listing_facings')->nullOnDelete();

            $table->string('road')->nullable()->after('facing_id');
            $table->string('house_name')->nullable()->after('road');
            $table->string('block', 100)->nullable()->after('house_name');
            $table->string('section', 100)->nullable()->after('block');

            $table->string('owner_name')->nullable()->after('section');
            $table->string('owner_phone', 20)->nullable()->after('owner_name');
            $table->string('owner_alt_phone', 20)->nullable()->after('owner_phone');
            $table->string('owner_email')->nullable()->after('owner_alt_phone');
            $table->enum('preferred_contact', ['call', 'whatsapp', 'both'])->nullable()->default('call')->after('owner_email');

            $table->index('available_from', 'listings_available_from_index');
            $table->index('floor_no', 'listings_floor_no_index');
            $table->index('facing_id', 'listings_facing_id_index');
            $table->index(['status', 'listing_type_id', 'available_from'], 'listings_status_type_available_index');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_status_type_available_index');
            $table->dropIndex('listings_facing_id_index');
            $table->dropIndex('listings_floor_no_index');
            $table->dropIndex('listings_available_from_index');
            $table->dropForeign(['facing_id']);
            $table->dropColumn([
                'available_from', 'floor_no', 'facing_id',
                'road', 'house_name', 'block', 'section',
                'owner_name', 'owner_phone', 'owner_alt_phone', 'owner_email', 'preferred_contact',
            ]);
        });
    }
};
