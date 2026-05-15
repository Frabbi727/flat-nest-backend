<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('listing_type_id')
                ->nullable()
                ->constrained('listing_types')
                ->nullOnDelete()
                ->after('owner_id');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('type')->nullable()->after('owner_id');
            $table->dropForeign(['listing_type_id']);
            $table->dropColumn('listing_type_id');
        });
    }
};
