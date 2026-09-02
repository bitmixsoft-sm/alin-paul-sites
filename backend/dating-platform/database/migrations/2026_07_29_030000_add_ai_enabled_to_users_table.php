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
            // NOT NULL DEFAULT true — MySQL backfills this onto every existing row on
            // ADD COLUMN, so existing female profiles get AI auto-replies immediately too,
            // with no separate backfill step needed.
            $table->boolean('ai_enabled')->default(true)->after('gender');
            $table->text('ai_system_prompt')->nullable()->after('ai_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_enabled', 'ai_system_prompt']);
        });
    }
};
