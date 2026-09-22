<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Global admin choice of how paid photo/album unlocks are paid for - credits (instant, like
     * Boost's credits mode) or real money (reuses Boost's hidden-Pack + /payments flow - see
     * App\Services\ContentUnlockService::checkout()). Same "one mode for the whole feature"
     * design as BOOST_PRICE_MODE, not a per-item choice - client's answer #2 ("ambele variante,
     * la alegerea adminului") is the admin picking the mode, not the buyer.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'CONTENT_UNLOCK_PRICE_MODE'],
            ['value' => 'credits', 'type' => 'select|credits~money', 'category' => 'Poze si albume cu pret', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'CONTENT_UNLOCK_ENABLED'],
            ['value' => 'yes', 'type' => 'toggle', 'category' => 'Poze si albume cu pret', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['CONTENT_UNLOCK_PRICE_MODE', 'CONTENT_UNLOCK_ENABLED'])->delete();
    }
};
