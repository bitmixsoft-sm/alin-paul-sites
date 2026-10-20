<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client's request (2026-10-20): send marketing/ad emails to the site's own registered users,
 * manually or on an automated open/reply-driven sequence (max ~7 phrases, abandon + retry after
 * 3-4 months of silence), from a paid Google Workspace mailbox, with admin-side manual takeover
 * and send/open stats. Deliberately plain Schema::create()/Blueprint calls only (no newer
 * Laravel 8+ migration sugar) - this whole feature (see app/EmailOutreach/) is meant to be
 * copy-pasted as-is onto the client's other sites, some of which are still on
 * Laravel 5.7/PHP 7.1.3.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_outreach_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label'); // e.g. "trovamequi.me" - just for the admin UI, not sent anywhere
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('smtp_host')->default('smtp.gmail.com');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->default('tls'); // tls or ssl
            $table->string('smtp_username');
            $table->text('smtp_password'); // encrypted (Crypt::encryptString) - the Google App Password
            $table->string('imap_host')->default('imap.gmail.com');
            $table->integer('imap_port')->default(993);
            $table->string('imap_username');
            $table->text('imap_password'); // encrypted - usually the same App Password as SMTP
            $table->integer('daily_send_limit')->default(200); // stays well under Workspace's own ~2000/day cap
            // Optional - used only for message translation into a recipient's language
            // (EmailTranslationService). Deliberately its OWN key, not a dependency on this
            // app's App\AISetting (keeps this feature fully self-contained/portable to the
            // other, older sites, which may not have that model at all). Left blank = outgoing
            // messages are sent exactly as written, no auto-translation.
            $table->text('openai_api_key')->nullable(); // encrypted
            $table->timestamps();
        });

        Schema::create('email_outreach_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->string('name');
            // The language the messages below are actually written in - EmailTranslationService
            // only translates when a recipient's own language differs from this.
            $table->string('base_language', 10)->default('ro');
            // "abandonam mailul vreo 3 4 luni dupa care mai incercam din nou" - client's own words,
            // made configurable per campaign instead of hardcoding one number.
            $table->integer('abandon_after_days')->default(100);
            $table->boolean('restart_after_abandon')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('email_outreach_accounts')->onDelete('cascade');
        });

        Schema::create('email_outreach_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            $table->unsignedTinyInteger('step_order'); // 1 through 7 (client's stated max)
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_outreach_campaigns')->onDelete('cascade');
            $table->unique(['campaign_id', 'step_order']);
        });

        Schema::create('email_outreach_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            // Nullable - the recipient may not match a users row (e.g. list re-imported after a
            // user account was later deleted), the sequence still runs off the raw email address.
            $table->unsignedInteger('user_id')->nullable();
            $table->string('email');
            // Seeded from users.lang when the email matches a registered user (client's own
            // registered users - see ProfileTranscriptBuilder/AI style learning elsewhere in this
            // app for the same users.lang convention) - re-detected via AI if a reply comes back
            // in a different language.
            $table->string('language', 10)->nullable();
            // pending: never contacted. waiting: last phrase sent, watching for open/reply.
            // replied: stopped automatically, needs admin attention. manual: admin took over.
            // abandoned: no engagement, in the cooldown period. bounced: hard SMTP failure.
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('current_step')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('last_engaged_at')->nullable(); // last open OR reply
            $table->timestamp('abandoned_at')->nullable();
            // Unique token embedded in the tracking-pixel URL - not the recipient's own id, so a
            // guessed/incremented id in the image URL can't be used to probe other recipients'
            // open status.
            $table->string('open_token', 40)->unique();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_outreach_campaigns')->onDelete('cascade');
            $table->index(['campaign_id', 'status']);
        });

        Schema::create('email_outreach_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('recipient_id');
            $table->string('event'); // sent, opened, replied, manual_reply, abandoned, restarted, bounced
            $table->unsignedTinyInteger('step')->nullable();
            $table->timestamps();

            $table->foreign('recipient_id')->references('id')->on('email_outreach_recipients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_outreach_logs');
        Schema::dropIfExists('email_outreach_recipients');
        Schema::dropIfExists('email_outreach_messages');
        Schema::dropIfExists('email_outreach_campaigns');
        Schema::dropIfExists('email_outreach_accounts');
    }
};
