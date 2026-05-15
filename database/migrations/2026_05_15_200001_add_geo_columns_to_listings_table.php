<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete()->after('area');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete()->after('division_id');
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete()->after('district_id');
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete()->after('upazila_id');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['upazila_id']);
            $table->dropForeign(['union_id']);
            $table->dropColumn(['division_id', 'district_id', 'upazila_id', 'union_id']);
        });
    }
};