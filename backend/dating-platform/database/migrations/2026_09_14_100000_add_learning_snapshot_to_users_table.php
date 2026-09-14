<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mirrors AIProfile.learning_snapshot (the AI Companions catalog's existing
            // style-learning feature) - same {style_guide, style_guide_updated_at} shape, plus
            // style_guide_source_user_id so the admin UI can show "this style was copied from
            // <profile>" instead of just an opaque blob of text.
            $table->json('learning_snapshot')->nullable()->after('simli_agent_schema_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('learning_snapshot');
        });
    }
};
