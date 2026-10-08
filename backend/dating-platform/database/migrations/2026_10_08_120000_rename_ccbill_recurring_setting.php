<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fixes a side effect found live right after introducing CCBILL_RECURRING_ENABLED
     * (2026-10-08): admin/settings.blade.php's data-settings-enabler script grays out/
     * disables EVERY other field in the same category card whenever any *_ENABLED/*_ACTIVE
     * switch in it is off - designed for a card's one true "master switch" (CCBILL_ACTIVE
     * here), not for a second, narrower switch living in the same "CCBill" card. With
     * CCBILL_RECURRING_ENABLED off by default, it incorrectly grayed out CCBILL_ACC and the
     * other core fields too. Renaming (not just changing the behavior) so it no longer ends
     * in _ENABLED/_ACTIVE - renders as the plain Da/Nu dropdown instead, with none of the
     * shared enabler-switch wiring, rather than touching that shared script (used by other,
     * already-working switches elsewhere) to special-case this one field.
     */
    public function up(): void
    {
        $old = DB::table('settings')->where('name', 'CCBILL_RECURRING_ENABLED')->first();
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_RECURRING_MODE'],
            ['value' => $old->value ?? 'no', 'type' => 'toggle', 'category' => 'CCBill', 'updated_at' => now()]
        );
        DB::table('settings')->where('name', 'CCBILL_RECURRING_ENABLED')->delete();
    }

    public function down(): void
    {
        $old = DB::table('settings')->where('name', 'CCBILL_RECURRING_MODE')->first();
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_RECURRING_ENABLED'],
            ['value' => $old->value ?? 'no', 'type' => 'toggle', 'category' => 'CCBill', 'updated_at' => now()]
        );
        DB::table('settings')->where('name', 'CCBILL_RECURRING_MODE')->delete();
    }
};
