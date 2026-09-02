<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', static function (Blueprint $table): void {
            $table->boolean('avatar_video_enabled')->default(true)->after('avatar_video_provider');
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', static function (Blueprint $table): void {
            $table->dropColumn('avatar_video_enabled');
        });
    }
};
