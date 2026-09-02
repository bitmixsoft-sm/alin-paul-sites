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
        // Reuses the existing generic key/value `settings` table (same pattern as
        // TIKTOK_PIXEL_ACTIVE/TIKTOK_PIXEL_ID) - looked up by name, not id, so this never
        // collides with the main admin Settings page's numeric-id-keyed rows. The dedicated
        // admin Themes page (AdminThemeController) is the primary UI for this value, but it
        // also shows up automatically on the generic /admin/settings page as a plain dropdown
        // since its type is "select|...".
        DB::table('settings')->updateOrInsert(
            ['name' => 'SITE_ACTIVE_THEME'],
            ['value' => 'classic', 'type' => 'select|classic~aurora~nordic', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('name', 'SITE_ACTIVE_THEME')->delete();
    }
};
