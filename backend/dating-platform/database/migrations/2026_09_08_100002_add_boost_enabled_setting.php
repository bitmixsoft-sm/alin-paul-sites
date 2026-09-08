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
        // On/off switch for the whole Boost feature (header button, Find Friends banner,
        // Profile Settings section, /profile/boost route) - moved here from an env var
        // (BOOST_FEATURE_ENABLED) so the admin can flip it from the web /admin/settings page
        // instead of needing server access, same as BOOST_COST_CREDITS/BOOST_DURATION_MINUTES.
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_FEATURE_ENABLED'],
            ['value' => 'yes', 'type' => 'toggle', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('name', 'BOOST_FEATURE_ENABLED')->delete();
    }
};
