<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client's feedback (2026-10-21): "la preluare manuala ar ajuta daca ar fi afisata si
 * conversatia" (it would help if the conversation itself was shown) - ImapReplyCheckerService
 * only ever detected THAT a reply happened, never stored what it said. Also used to log the
 * admin's own manual replies (AdminEmailOutreachController::manualReply), so the full back-
 * and-forth shows up in one place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_outreach_logs', function (Blueprint $table) {
            $table->text('body')->nullable()->after('step');
        });
    }

    public function down(): void
    {
        Schema::table('email_outreach_logs', function (Blueprint $table) {
            $table->dropColumn('body');
        });
    }
};
