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
        // Same name-keyed settings pattern as SITE_ACTIVE_THEME (see
        // 2026_08_28_010000_add_site_active_theme_setting.php) - shows up automatically on the
        // generic /admin/settings page as a plain text field, no dedicated admin UI needed.
        // Read by BoostController@activate.
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_COST_CREDITS'],
            ['value' => '50', 'type' => '', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_DURATION_MINUTES'],
            ['value' => '45', 'type' => '', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('name', 'BOOST_COST_CREDITS')->delete();
        DB::table('settings')->where('name', 'BOOST_DURATION_MINUTES')->delete();
    }
};
