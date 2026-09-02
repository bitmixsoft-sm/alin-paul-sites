<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('audio_muted')->nullable()->after('video_blur_percent');
        });

        // Only the free Trial package is muted by default — all paid tiers hear the AI.
        DB::table('packages')->where('name', 'Trial')->update(['audio_muted' => true]);
        DB::table('packages')->where('name', 'Silver')->update(['audio_muted' => false]);
        DB::table('packages')->where('name', 'Gold')->update(['audio_muted' => false]);
        DB::table('packages')->where('name', 'VIP')->update(['audio_muted' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('audio_muted');
        });
    }
};
