<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_profiles', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('created_by_admin_id');
            $table->string('name', 120);
            $table->string('static_image_path');
            $table->longText('system_prompt');
            $table->string('voice_id', 120);
            $table->json('learning_snapshot')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_admin_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->index(['created_by_admin_id', 'is_active'], 'ai_profiles_admin_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_profiles');
    }
};
