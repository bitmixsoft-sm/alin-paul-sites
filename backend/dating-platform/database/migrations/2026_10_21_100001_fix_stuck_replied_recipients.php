<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data fix for recipients already stuck at status = 'replied' from before the 2026-10-21
 * ImapReplyCheckerService rewrite (when a reply froze the sequence instead of continuing it).
 * 'replied' is no longer a status the app ever sets - these rows would otherwise sit frozen
 * forever since no code path advances a 'replied' row anymore. Moving them to 'waiting' lets
 * EmailSequenceEngine pick them back up on its next run exactly like a fresh reply would.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_outreach_recipients')->where('status', 'replied')->update(['status' => 'waiting']);
    }

    public function down(): void
    {
        // Not reversible - no record of which 'waiting' rows were previously 'replied'.
    }
};
