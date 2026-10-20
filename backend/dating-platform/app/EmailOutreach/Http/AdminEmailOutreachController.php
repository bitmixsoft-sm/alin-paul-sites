<?php

namespace App\EmailOutreach\Http;

use App\EmailOutreach\Models\EmailOutreachAccount;
use App\EmailOutreach\Models\EmailOutreachCampaign;
use App\EmailOutreach\Models\EmailOutreachLog;
use App\EmailOutreach\Models\EmailOutreachMessage;
use App\EmailOutreach\Models\EmailOutreachRecipient;
use App\EmailOutreach\Services\EmailSequenceEngine;
use App\EmailOutreach\Services\ImapReplyCheckerService;
use App\EmailOutreach\Services\SmtpMailerService;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Admin-only (client's request, 2026-10-20) - deliberately role === 'admin', not isAdmin()
 * (admin OR editor), same reasoning as the AI Settings/Profiles areas: this sends real emails
 * on the client's behalf and holds live SMTP credentials, not something an editor account
 * should be able to touch.
 */
class AdminEmailOutreachController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
            return $next($request);
        });
    }

    public function index()
    {
        $on_page = 'Email Outreach';
        $accounts = EmailOutreachAccount::withCount('campaigns')->get();
        $campaigns = EmailOutreachCampaign::with('account')
            ->withCount([
                'recipients as pending_count' => function ($q) { $q->where('status', 'pending'); },
                'recipients as waiting_count' => function ($q) { $q->where('status', 'waiting'); },
                'recipients as replied_count' => function ($q) { $q->where('status', 'replied'); },
                'recipients as abandoned_count' => function ($q) { $q->where('status', 'abandoned'); },
            ])
            ->orderBy('id', 'desc')->get();

        return view('email-outreach.admin.index', compact('on_page', 'accounts', 'campaigns'));
    }

    // -- Accounts (mailboxes) -------------------------------------------------------------

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'from_email' => 'required|email',
            'from_name' => 'nullable|string|max:100',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'smtp_encryption' => 'required|in:tls,ssl',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string|max:255',
            'imap_password' => 'required|string',
            'daily_send_limit' => 'required|integer|min:1',
            'openai_api_key' => 'nullable|string',
        ]);

        EmailOutreachAccount::create($data);

        return redirect('/admin/email-outreach')->with('status', 'Cont de email adaugat.');
    }

    public function editAccount($id)
    {
        $on_page = 'Email Outreach';
        $account = EmailOutreachAccount::findOrFail($id);

        return view('email-outreach.admin.edit-account', compact('on_page', 'account'));
    }

    /**
     * Password fields are left blank on the edit form by default ("leave blank to keep" -
     * same convention as admin/ai_settings.blade.php's API key fields) - only overwritten if
     * the admin actually typed a new value, so re-saving the form after a typo in some other
     * field doesn't force re-entering the App Password too.
     */
    public function updateAccount(Request $request, $id)
    {
        $account = EmailOutreachAccount::findOrFail($id);

        $data = $request->validate([
            'label' => 'required|string|max:100',
            'from_email' => 'required|email',
            'from_name' => 'nullable|string|max:100',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'smtp_encryption' => 'required|in:tls,ssl',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string|max:255',
            'imap_password' => 'nullable|string',
            'daily_send_limit' => 'required|integer|min:1',
            'openai_api_key' => 'nullable|string',
        ]);

        foreach (['smtp_password', 'imap_password', 'openai_api_key'] as $secretField) {
            if (($data[$secretField] ?? '') === '') {
                unset($data[$secretField]); // keep the existing encrypted value
            }
        }

        $account->fill($data);
        $account->save();

        return redirect('/admin/email-outreach')->with('status', 'Cont actualizat.');
    }

    // -- Campaigns --------------------------------------------------------------------------

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|exists:email_outreach_accounts,id',
            'name' => 'required|string|max:150',
            'base_language' => 'required|string|max:10',
            'abandon_after_days' => 'required|integer|min:1',
            'restart_after_abandon' => 'nullable|boolean',
        ]);
        $data['restart_after_abandon'] = $request->has('restart_after_abandon');

        $campaign = EmailOutreachCampaign::create($data);

        return redirect('/admin/email-outreach/campaigns/'.$campaign->id)->with('status', 'Campanie creata.');
    }

    /**
     * Client's question (2026-10-20): manual "send now" trigger, so testing doesn't require
     * waiting for the next cron tick (every 15 minutes - see Kernel.php). Runs the exact same
     * reply-check + send pass the scheduled command does, just for this one campaign's
     * account, on demand.
     */
    public function runCampaignNow($id, ImapReplyCheckerService $replyChecker, EmailSequenceEngine $engine)
    {
        $campaign = EmailOutreachCampaign::findOrFail($id);
        $account = $campaign->account;

        $replies = $account ? $replyChecker->checkForReplies($account) : 0;
        $sent = $engine->processCampaign($campaign);

        return redirect('/admin/email-outreach/campaigns/'.$campaign->id)
            ->with('status', "Rulare manuala: {$sent} email(uri) trimise, {$replies} raspuns(uri) detectate.");
    }

    public function showCampaign($id)
    {
        $campaign = EmailOutreachCampaign::with('messages')->findOrFail($id);
        $on_page = 'Email Outreach';
        $recipients = $campaign->recipients()->orderBy('id', 'desc')->paginate(50);
        $stats = [
            'total' => $campaign->recipients()->count(),
            'pending' => $campaign->recipients()->where('status', 'pending')->count(),
            'waiting' => $campaign->recipients()->where('status', 'waiting')->count(),
            'replied' => $campaign->recipients()->where('status', 'replied')->count(),
            'manual' => $campaign->recipients()->where('status', 'manual')->count(),
            'abandoned' => $campaign->recipients()->where('status', 'abandoned')->count(),
            'opened' => EmailOutreachLog::where('event', 'opened')
                ->whereHas('recipient', function ($q) use ($campaign) { $q->where('campaign_id', $campaign->id); })
                ->count(),
            'sent' => EmailOutreachLog::where('event', 'sent')
                ->whereHas('recipient', function ($q) use ($campaign) { $q->where('campaign_id', $campaign->id); })
                ->count(),
        ];

        return view('email-outreach.admin.campaign', compact('on_page', 'campaign', 'recipients', 'stats'));
    }

    // -- Messages (the up-to-7 phrases) -----------------------------------------------------

    public function storeMessage(Request $request, $campaignId)
    {
        $campaign = EmailOutreachCampaign::findOrFail($campaignId);
        $data = $request->validate([
            'step_order' => 'required|integer|min:1|max:7',
            'body' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('email-outreach-images');
        }

        EmailOutreachMessage::updateOrCreate(
            ['campaign_id' => $campaign->id, 'step_order' => $data['step_order']],
            ['body' => $data['body']] + ($imagePath ? ['image_path' => $imagePath] : [])
        );

        return redirect('/admin/email-outreach/campaigns/'.$campaign->id)->with('status', 'Fraza salvata.');
    }

    // -- Recipients (the list) ---------------------------------------------------------------

    /**
     * Accepts a textarea of one email per line (client's "optiune de lista") - matched against
     * this site's own registered users (User::email) to pre-fill language/user_id when
     * possible; unmatched addresses are still added, just without that extra context.
     */
    public function importRecipients(Request $request, $campaignId)
    {
        $campaign = EmailOutreachCampaign::findOrFail($campaignId);
        $data = $request->validate(['emails' => 'required|string']);

        $lines = preg_split('/[\r\n,;]+/', $data['emails']);
        $added = 0;

        foreach ($lines as $line) {
            $email = strtolower(trim($line));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if ($campaign->recipients()->where('email', $email)->exists()) {
                continue;
            }

            $user = User::where('email', $email)->first();

            EmailOutreachRecipient::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user ? $user->id : null,
                'email' => $email,
                'language' => $user ? $user->lang : null,
                'status' => 'pending',
                'open_token' => Str::random(32),
            ]);
            $added++;
        }

        return redirect('/admin/email-outreach/campaigns/'.$campaign->id)->with('status', $added.' adrese adaugate.');
    }

    // -- Manual takeover ----------------------------------------------------------------------

    public function takeOver($recipientId)
    {
        $recipient = EmailOutreachRecipient::findOrFail($recipientId);
        $recipient->status = 'manual';
        $recipient->save();
        EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'manual_takeover', 'step' => $recipient->current_step]);

        return back()->with('status', 'Preluat manual.');
    }

    public function continueAutomation($recipientId)
    {
        $recipient = EmailOutreachRecipient::findOrFail($recipientId);
        $recipient->status = 'waiting';
        $recipient->last_engaged_at = now(); // so the engine's "engaged since last send" check picks it up
        $recipient->save();

        return back()->with('status', 'Secventa automata reluata.');
    }

    public function manualReply(Request $request, $recipientId, SmtpMailerService $mailer)
    {
        $recipient = EmailOutreachRecipient::with('campaign.account')->findOrFail($recipientId);
        $data = $request->validate(['body' => 'required|string']);

        $result = $mailer->send($recipient->campaign->account, $recipient->email, 'TrovaMequi', nl2br(e($data['body'])));

        if ($result['success']) {
            EmailOutreachLog::create(['recipient_id' => $recipient->id, 'event' => 'manual_reply', 'step' => $recipient->current_step]);
            return back()->with('status', 'Raspuns trimis.');
        }

        return back()->with('status', 'Eroare la trimitere: '.$result['error']);
    }

    /**
     * Client's question (2026-10-20): removes a recipient from a campaign entirely (e.g. a
     * test address, or someone who should stop receiving anything at all). Their
     * email_outreach_logs rows go with them (ON DELETE CASCADE) - nothing left behind to
     * accidentally resurrect the sequence for that address later.
     */
    public function deleteRecipient($recipientId)
    {
        $recipient = EmailOutreachRecipient::findOrFail($recipientId);
        $campaignId = $recipient->campaign_id;
        $recipient->delete();

        return redirect('/admin/email-outreach/campaigns/'.$campaignId)->with('status', 'Destinatar sters.');
    }

    /**
     * Client's question (2026-10-20): "what if the list has 100-500+ addresses, do I have to
     * delete them one by one?" - no, two bulk options: checkbox-select-and-delete here, or
     * deleteAllRecipients() below to clear the whole campaign in one go.
     */
    public function bulkDeleteRecipients(Request $request, $campaignId)
    {
        $ids = $request->input('recipient_ids', []);
        $count = EmailOutreachRecipient::where('campaign_id', $campaignId)->whereIn('id', $ids)->delete();

        return redirect('/admin/email-outreach/campaigns/'.$campaignId)->with('status', $count.' destinatari stersi.');
    }

    public function deleteAllRecipients($campaignId)
    {
        $campaign = EmailOutreachCampaign::findOrFail($campaignId);
        $count = $campaign->recipients()->delete();

        return redirect('/admin/email-outreach/campaigns/'.$campaign->id)->with('status', 'Lista intreaga stearsa ('.$count.' destinatari).');
    }

}
