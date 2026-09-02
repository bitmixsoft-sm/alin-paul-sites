<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('role', 20);
            $table->string('channel', 20)->default('text');
            $table->text('content');
            $table->string('emotion', 30)->nullable();
            $table->string('conversion_signal', 40)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['conversation_id', 'id'], 'conv_messages_conversation_id_idx');
            $table->index(['role', 'created_at'], 'conv_messages_role_created_idx');
            $table->index(['conversion_signal', 'created_at'], 'conv_messages_signal_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
