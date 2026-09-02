<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Uses the existing generic key/value `settings` table (already used for similar
        // site-wide toggles like "Activare Chat Bot") rather than a new table, since this is
        // just two simple values (on/off + Pixel ID). Looked up by name (not id) everywhere
        // this is read/written, so it never collides with the numeric-id-keyed rows the main
        // admin Settings page manages.
        DB::table('settings')->updateOrInsert(
            ['name' => 'TIKTOK_PIXEL_ACTIVE'],
            ['value' => 'no', 'type' => 'toggle', 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'TIKTOK_PIXEL_ID'],
            ['value' => '', 'type' => 'text', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['TIKTOK_PIXEL_ACTIVE', 'TIKTOK_PIXEL_ID'])->delete();
    }
};
