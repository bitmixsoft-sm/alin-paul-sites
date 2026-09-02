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
        Schema::table('messages', function (Blueprint $table) {
            // Distinguishes a real LLM-generated auto-reply (ChatBotController::sendAiReply())
            // from the legacy keyword-match bot reply — both are marked chatbot='true', but
            // only this flag lets the admin usage report count actual OpenAI API calls.
            $table->boolean('ai_generated')->default(false)->after('chatbot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('ai_generated');
        });
    }
};
