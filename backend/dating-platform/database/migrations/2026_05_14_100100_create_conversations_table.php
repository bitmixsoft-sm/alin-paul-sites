<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreignId('ai_profile_id')->constrained('ai_profiles')->cascadeOnDelete();
            $table->string('channel', 20)->default('text');
            $table->string('last_user_emotion', 30)->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->json('conversion_context')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at'], 'conversations_user_created_idx');
            $table->index(['ai_profile_id', 'created_at'], 'conversations_profile_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
