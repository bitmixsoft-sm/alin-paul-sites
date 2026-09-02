<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', static function (Blueprint $table): void {
            $table->id();
            $table->string('live_avatar_provider', 20)->default('tavus_cvi');
            $table->string('avatar_video_provider', 20)->default('did');
            $table->text('tavus_api_key')->nullable();
            $table->text('simli_api_key')->nullable();
            $table->text('heygen_api_key')->nullable();
            $table->text('did_api_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
