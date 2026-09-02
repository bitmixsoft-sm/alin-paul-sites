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
            $table->boolean('live_video_mute_enabled')->default(true)->after('live_video_blur_amount');
            $table->boolean('live_video_mute_default')->default(false)->after('live_video_mute_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn(['live_video_mute_enabled', 'live_video_mute_default']);
        });
    }
};
