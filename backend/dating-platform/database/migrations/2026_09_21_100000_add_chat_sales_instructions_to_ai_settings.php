<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client's request (2026-09-21): the real-chat AI told stories and never steered the
     * conversation toward a subscription (it only had a deliberately mild "without being pushy"
     * sentence, no knowledge of what the subscription is, and no rules on length/language).
     * These columns hold the admin-editable replacement - see App\Services\AI\ChatSalesPrompts
     * for the default texts, which are used whenever a text column is left empty (null).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('ai_settings', 'chat_instructions_enabled')) {
            Schema::table('ai_settings', function (Blueprint $table) {
                $table->boolean('chat_instructions_enabled')->default(true);
                $table->text('chat_general_instructions')->nullable();
                $table->text('chat_product_knowledge')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ai_settings', 'chat_instructions_enabled')) {
            Schema::table('ai_settings', function (Blueprint $table) {
                $table->dropColumn(['chat_instructions_enabled', 'chat_general_instructions', 'chat_product_knowledge']);
            });
        }
    }
};
