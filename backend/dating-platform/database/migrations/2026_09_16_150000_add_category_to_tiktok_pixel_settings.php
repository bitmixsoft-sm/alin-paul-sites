<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * TIKTOK_PIXEL_ACTIVE is a `type = 'toggle'` row ending in `_ACTIVE`, so it started
     * rendering as an enabler switch (admin/settings.blade.php) as soon as that branch was
     * extended to match `_ACTIVE`, not just `_ENABLED` (see the payment-settings grouping
     * migration). Left uncategorized, it landed in "General" alongside ~30 unrelated settings -
     * and the enabler JS grays out every OTHER field in the same category card, so toggling
     * this one off was disabling completely unrelated settings too. Giving it (and its only
     * companion field, TIKTOK_PIXEL_ID) their own small category scopes that effect to just
     * the two TikTok Pixel settings, same fix as the payment providers got.
     */
    public function up(): void
    {
        DB::table('settings')
            ->whereIn('name', ['TIKTOK_PIXEL_ACTIVE', 'TIKTOK_PIXEL_ID'])
            ->update(['category' => 'TikTok Pixel']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('name', ['TIKTOK_PIXEL_ACTIVE', 'TIKTOK_PIXEL_ID'])
            ->update(['category' => null]);
    }
};
