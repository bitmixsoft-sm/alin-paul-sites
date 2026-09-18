<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Makes ProfileTranscriptBuilder's transcript-size caps and "include both sides of the
     * conversation" behavior admin-editable (client's follow-up, 2026-09-18) instead of
     * hardcoded - see that class's docblock for the reasoning behind each default, which match
     * the values that were hardcoded before this migration so nothing changes for anyone until
     * an admin actually edits them.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'AI_STYLE_LEARNING_MAX_MESSAGES'],
            ['value' => '4000', 'type' => '', 'category' => 'AI Style Learning', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'AI_STYLE_LEARNING_MAX_CHARS'],
            ['value' => '40000', 'type' => '', 'category' => 'AI Style Learning', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'AI_STYLE_LEARNING_INCLUDE_BOTH_PARTIES'],
            ['value' => 'yes', 'type' => 'toggle', 'category' => 'AI Style Learning', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', [
            'AI_STYLE_LEARNING_MAX_MESSAGES',
            'AI_STYLE_LEARNING_MAX_CHARS',
            'AI_STYLE_LEARNING_INCLUDE_BOTH_PARTIES',
        ])->delete();
    }
};
