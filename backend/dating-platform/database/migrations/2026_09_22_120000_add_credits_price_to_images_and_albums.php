<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes a real bug found while testing (2026-09-22): `price` (decimal EUR, e.g. 1.50) was
     * being charged directly against `users.credits`, which is a plain `int` column - a 1.50
     * price silently truncated to 1 credit, undercharging every non-whole price in credits mode.
     *
     * The actual site convention (see BoostController: BOOST_COST_CREDITS vs
     * BOOST_PRICE_AMOUNT) is that a credits cost and a money price are always two INDEPENDENT
     * numbers the admin sets separately, never one converted from the other - so this adds a
     * second, integer `price_credits` column rather than trying to derive credits from the EUR
     * price. `price` keeps meaning "the EUR price" (used in money mode); `price_credits` is the
     * credits cost (used in credits mode) - see ContentUnlockService.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('images', 'price_credits')) {
            Schema::table('images', function (Blueprint $table) {
                $table->unsignedInteger('price_credits')->nullable()->default(0)->after('price');
            });
        }

        if (! Schema::hasColumn('albums', 'price_credits')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->unsignedInteger('price_credits')->nullable()->default(0)->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('albums', 'price_credits')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->dropColumn('price_credits');
            });
        }

        if (Schema::hasColumn('images', 'price_credits')) {
            Schema::table('images', function (Blueprint $table) {
                $table->dropColumn('price_credits');
            });
        }
    }
};
