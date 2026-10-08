<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Safety switch (found live, 2026-10-08): real 30+ day packages ("play", "exclusive
     * king") currently get "Invalid Digest" from CCBill when the recurring
     * (recurringPrice/recurringPeriod/numRebills) params are added - root cause not yet
     * confirmed with CCBill support. Defaults to 'no' so those packages stay sellable (as a
     * plain one-time charge, same as before today's recurring work) while that's being
     * sorted out - flip to 'yes' once CCBill confirms/fixes whatever's wrong. Named
     * *_ENABLED so admin/settings.blade.php renders it as the on/off switch style, not the
     * plain Da/Nu dropdown.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_RECURRING_ENABLED'],
            ['value' => 'no', 'type' => 'toggle', 'category' => 'CCBill', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('name', 'CCBILL_RECURRING_ENABLED')->delete();
    }
};
