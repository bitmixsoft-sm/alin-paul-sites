<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * BOOST_PRICE_EUR (2026_09_16_120000_add_boost_price_mode_settings.php) hardcoded the
     * currency into its very name, but this app's CentralPay integration only ever charges in
     * one site-wide currency (CENTRALPAY_CURRENCY, an env value shared by every payment - Boost
     * included) - a name baking in "EUR" would be actively misleading on any deployment
     * configured for a different currency. Renamed rather than dropped/recreated so an admin
     * who already typed a real price into it on some environment doesn't lose that value.
     */
    public function up(): void
    {
        DB::table('settings')->where('name', 'BOOST_PRICE_EUR')->update(['name' => 'BOOST_PRICE_AMOUNT']);

        // Covers a fresh environment where the earlier migration never ran / this one runs
        // standalone - same updateOrInsert pattern as the settings it's paired with.
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_PRICE_AMOUNT'],
            ['value' => '4.00', 'type' => '', 'category' => 'Functii platite (Boost si similare)', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('name', 'BOOST_PRICE_AMOUNT')->update(['name' => 'BOOST_PRICE_EUR']);
    }
};
