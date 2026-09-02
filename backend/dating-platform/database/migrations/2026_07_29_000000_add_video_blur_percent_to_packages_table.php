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
            $table->unsignedTinyInteger('video_blur_percent')->nullable()->after('featured');
        });

        // Seed the requested defaults for the current non-custom packages — higher tiers see
        // a clearer (less blurred) AI live video.
        DB::table('packages')->where('name', 'Trial')->update(['video_blur_percent' => 90]);
        DB::table('packages')->where('name', 'Silver')->update(['video_blur_percent' => 70]);
        DB::table('packages')->where('name', 'Gold')->update(['video_blur_percent' => 40]);
        DB::table('packages')->where('name', 'VIP')->update(['video_blur_percent' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('video_blur_percent');
        });
    }
};
