<?php

namespace App\EmailOutreach\Console;

use App\EmailOutreach\Models\EmailOutreachAccount;
use App\EmailOutreach\Services\EmailSequenceEngine;
use App\EmailOutreach\Services\ImapReplyCheckerService;
use Illuminate\Console\Command;

/**
 * Scheduled in app/Console/Kernel.php (see that file's doc comment for the exact cadence -
 * e.g. ->everyFifteenMinutes() - this command itself has no opinion on frequency, it just
 * does one pass over every account each time it runs. Account.daily_send_limit is what
 * actually paces sending across however often this fires, not the schedule itself.
 */
class RunEmailOutreachCommand extends Command
{
    protected $signature = 'email-outreach:run';
    protected $description = 'Checks for replies, then sends the next due phrase for every active campaign.';

    public function handle(ImapReplyCheckerService $replyChecker, EmailSequenceEngine $engine)
    {
        foreach (EmailOutreachAccount::all() as $account) {
            $replies = $replyChecker->checkForReplies($account);
            if ($replies > 0) {
                $this->info('Account '.$account->label.': '.$replies.' repl(y/ies) matched.');
            }

            foreach ($account->campaigns()->where('active', true)->get() as $campaign) {
                $engine->processCampaign($campaign);
            }
        }

        return 0;
    }
}
