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
        // Only updates the `type` column (the dropdown's allowed-options list, shown on the
        // generic /admin/settings page) - deliberately does NOT touch `value`, so whichever
        // theme is currently active stays active after this runs.
        DB::table('settings')
            ->where('name', 'SITE_ACTIVE_THEME')
            ->update(['type' => 'select|classic~aurora~nordic~volt~velvet~bloom~binder', 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('name', 'SITE_ACTIVE_THEME')
            ->update(['type' => 'select|classic~aurora~nordic~volt~velvet~bloom']);
    }
};
