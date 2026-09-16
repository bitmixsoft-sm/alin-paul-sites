<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lets the admin choose, per the client's request (2026-09-16), whether Boost costs
     * in-app credits (the existing behavior - BoostController@activate) or a real one-off
     * money payment via CentralPay (BoostController@checkout / FeatureActivationRegistry).
     * Same "Functii platite" category as the existing Boost settings, added by
     * 2026_09_16_100000_add_category_to_settings_table.php.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_PRICE_MODE'],
            ['value' => 'credits', 'type' => 'select|credits~money', 'category' => 'Functii platite (Boost si similare)', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'BOOST_PRICE_EUR'],
            ['value' => '4.00', 'type' => '', 'category' => 'Functii platite (Boost si similare)', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['BOOST_PRICE_MODE', 'BOOST_PRICE_EUR'])->delete();
    }
};
