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
        Schema::table('ai_settings', function (Blueprint $table) {
            // Master switch for the real-chat AI auto-reply (ChatBotController), independent
            // of the legacy "Activare Chat Bot" Settings row (id=22), which only gates the
            // old keyword-match fallback bot.
            $table->boolean('text_ai_enabled')->default(true)->after('openai_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn('text_ai_enabled');
        });
    }
};
