<?php

namespace App\Http\Controllers;

use App\Pack;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

/**
 * Admin-generated payment links (client's need, 2026-11-06): the client sells packages by
 * sending a link to a buyer instead of the buyer clicking "Buy" on the site. Copying the
 * CCBill URL out of the admin's own browser attached the order to the ADMIN account, so a
 * real EUR 199 payment ended up with no customer to receive the package. A link made here is
 * tied to the buyer's own account instead.
 *
 * The link is a signed, expiring URL to this site (not to CCBill): opening it creates a fresh
 * order for that user and sends them on to CCBill through the normal PaymentsController::
 * newpayment() flow, so pricing, discounts, recurring mode and the digest stay in one place.
 */
class AdminPaymentLinkController extends Controller
{
    private const LINK_VALID_DAYS = 14;

    public function index()
    {
        $this->authorizeAdmin();

        return view('admin.payment_links', [
            'on_page' => 'Link de plata',
            'packs' => $this->sellablePacks(),
            'validDays' => self::LINK_VALID_DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'email' => 'required|email',
            'pack_id' => 'required|integer',
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($data['email']))])->first();
        if (!$user) {
            return back()->withInput()->with('link_error', 'Nu exista niciun cont cu acest email. Clientul trebuie sa se inregistreze mai intai pe site, apoi generati linkul.');
        }

        $fakeEmailPattern = '/@'.preg_quote($request->getHost(), '/').'$/i';
        if ($user->email && preg_match($fakeEmailPattern, $user->email)) {
            return back()->withInput()->with('link_error', 'Acest cont este un cont temporar (inregistrare neterminata). Clientul trebuie sa isi finalizeze inregistrarea.');
        }

        $pack = $this->sellablePacks()->firstWhere('id', (int) $data['pack_id']);
        if (!$pack) {
            return back()->withInput()->with('link_error', 'Pachet invalid.');
        }

        $link = URL::temporarySignedRoute(
            'payment_link_pay',
            now()->addDays(self::LINK_VALID_DAYS),
            ['uid' => $user->id, 'pid' => $pack->id]
        );

        return back()->withInput()->with('link', $link)->with('link_target', [
            'name' => trim($user->firstname.' '.$user->lastname),
            'email' => $user->email,
            'pack' => $pack->name,
            'price' => $pack->price,
            'currency' => $pack->currency,
        ]);
    }

    /**
     * Public (no login - the buyer isn't necessarily logged in), protected by the URL
     * signature. Authenticates as the buyer for this single request only (no session is
     * created) so newpayment() builds the order for the right user.
     */
    public function pay(Request $request)
    {
        $user = User::findOrFail((int) $request->query('uid'));
        $pack = $this->sellablePacks()->firstWhere('id', (int) $request->query('pid'));
        abort_unless($pack, 404);

        Auth::onceUsingId($user->id);
        $request->merge(['pack_id' => $pack->id, 'payment_method' => 'CCBILL']);

        return app(PaymentsController::class)->newpayment($request);
    }

    private function authorizeAdmin()
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    private function sellablePacks()
    {
        return Pack::where(function ($q) {
                $q->whereNull('custom')->orWhere('custom', 0);
            })
            ->whereNull('feature_key')
            ->orderBy('price')
            ->get();
    }
}
