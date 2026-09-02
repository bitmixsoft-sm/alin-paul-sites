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
            // Opt-in (default false), unlike text_ai_enabled — live video is materially
            // more expensive and needs per-profile Simli Face setup before it can work at
            // all, so it shouldn't silently turn on for every profile like the text feature did.
            $table->boolean('live_ai_video_enabled')->default(false)->after('text_ai_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn('live_ai_video_enabled');
        });
    }
};
