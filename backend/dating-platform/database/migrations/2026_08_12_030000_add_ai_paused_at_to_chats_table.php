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
        Schema::table('chats', function (Blueprint $table) {
            // Timestamp of the most recent manual admin takeover (or manual pause via the
            // toggle) — lets ChatBotController auto-resume the AI after a period of admin
            // inactivity, instead of leaving a conversation paused forever if forgotten.
            $table->timestamp('ai_paused_at')->nullable()->after('ai_paused');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('ai_paused_at');
        });
    }
};
