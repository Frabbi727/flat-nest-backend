<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldBase = 'http://localhost:8000/storage';
        $newBase = rtrim(config('app.url'), '/') . '/storage';

        if ($oldBase === $newBase) {
            return;
        }

        DB::table('listing_photos')
            ->where('url', 'like', $oldBase . '%')
            ->update(['url' => DB::raw("REPLACE(url, '$oldBase', '$newBase')")]);

        DB::table('users')
            ->whereNotNull('avatar_url')
            ->where('avatar_url', 'like', $oldBase . '%')
            ->update(['avatar_url' => DB::raw("REPLACE(avatar_url, '$oldBase', '$newBase')")]);
    }

    public function down(): void {}
};
