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
            // Lets a real female profile be called via a live Simli AI video avatar
            // (SimliService::createSessionForUser()), mirroring what AIProfile already
            // stores in learning_snapshot for the separate AI Companions catalog.
            $table->string('simli_face_id')->nullable()->after('ai_system_prompt');
            $table->string('simli_voice_id')->nullable()->after('simli_face_id');
            $table->string('simli_agent_id')->nullable()->after('simli_voice_id');
            $table->unsignedTinyInteger('simli_agent_schema_version')->nullable()->after('simli_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['simli_face_id', 'simli_voice_id', 'simli_agent_id', 'simli_agent_schema_version']);
        });
    }
};
