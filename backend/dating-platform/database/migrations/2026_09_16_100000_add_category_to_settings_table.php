<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The generic /admin/settings page (SettingsController) has grown into one long flat list
     * of ~65+ rows with no grouping at all - reported live as genuinely hard to navigate, and
     * about to get harder as more paid-feature settings (Boost's real-money option, and
     * whatever similar features come after it) are added alongside it. A nullable category
     * column, defaulting to null for every existing row (so nothing already there visually
     * moves unless explicitly assigned below), lets the settings view group rows under
     * headings without changing how the form itself is submitted/saved (SettingsController::
     * store() still just walks every posted field keyed by settings.id, unaffected by this).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('settings', 'category')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('category')->nullable()->after('type');
            });
        }

        // Existing Boost settings, the first thing this grouping was actually needed for -
        // tagged here so they show up in their own section immediately, not still buried in
        // "General" until someone remembers to categorize them by hand later.
        DB::table('settings')
            ->whereIn('name', ['BOOST_FEATURE_ENABLED', 'BOOST_COST_CREDITS', 'BOOST_DURATION_MINUTES'])
            ->update(['category' => 'Functii platite (Boost si similare)']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
