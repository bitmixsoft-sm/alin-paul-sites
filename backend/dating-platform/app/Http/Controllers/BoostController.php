<?php

namespace App\Http\Controllers;

use App\Pack;
use App\Settings;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Boost your profile" - rank higher in Find Friends for a limited time (see
 * FindFriendsController's boosted_until ordering). Priced either in credits (the original,
 * default mode - bare users.credits decrement, same pattern as VideoController@credits/
 * ChatController@send's per-message spend) or real money (BOOST_PRICE_MODE admin setting,
 * added per the client's request, 2026-09-16).
 *
 * The money path (checkout()) deliberately reuses PaymentsController's existing, already
 * multi-provider payment flow instead of talking to a payment provider directly - see
 * checkout()'s own comment for why. Boost only actually activates once that payment is
 * confirmed (each provider's own success handling in PaymentsController, which checks
 * $pack->feature_key - see FeatureActivationRegistry), not immediately when checkout() runs.
 */
class BoostController extends Controller
{
    /**
     * Settings rows toggling each provider on (PackagesController::index() reads the exact
     * same list for the real /packages checkout) - STRIPE_ACTIVE and
     * GIFTCARD_DELICI.ONLINE_ACTIVE deliberately excluded here. Stripe needs its own card-entry
     * UI (Stripe Elements, currently only present on the /packages page itself, nowhere Boost's
     * button appears - header/profile dropdown/Find Friends), and the gift-card option is a
     * one-off integration with an unrelated WordPress site, not a general payment method.
     * Extending this to Stripe later means giving Boost's checkout its own Stripe Elements UI,
     * not just adding a name to this list.
     */
    private const SUPPORTED_PROVIDERS = ['CENTRALPAY_ACTIVE', 'PAYPAL_ACTIVE', 'CCBILL_ACTIVE', 'WIRE-TRANSFER_ACTIVE'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function activate(Request $request)
    {
        // On/off switch lives in the settings table (BOOST_FEATURE_ENABLED, a 'toggle' row -
        // same admin-editable /admin/settings page as BOOST_COST_CREDITS/BOOST_DURATION_MINUTES
        // below), not a config/.env value, so the admin can flip it from the web UI. Checked
        // per-method rather than in the constructor, since aborting in the constructor would
        // throw any time this controller is merely instantiated (e.g. `php artisan route:list`
        // resolves controllers to read their middleware), not just on an actual request here.
        abort_if(Settings::where('name', 'BOOST_FEATURE_ENABLED')->value('value') === 'no', 404);

        // This endpoint is credits-only - the money mode has its own checkout() below, since
        // the two flows behave completely differently (instant success vs. a page redirect).
        // The button's own onclick (boost-widget.blade.php) already picks the right one based
        // on BOOST_PRICE_MODE, so landing here in money mode would mean the button/JS is out
        // of sync with the setting, not a normal user path.
        if ($this->priceMode() === 'money') {
            return response()->json(['success' => false, 'error' => 'wrong_mode'], 409);
        }

        // The button is hidden for admins (profile-info.blade.php) since this page always
        // edits Auth::user()'s own account - an admin's own login, not a managed female
        // profile - and Find Friends only ever shows female profiles to regular users, so
        // boosting it would never be visible to anyone. Enforced here too in case the route
        // is hit directly.
        if (Auth::user()->isAdmin()) {
            abort(403);
        }

        $user = User::where('id', Auth::id())->firstOrFail();

        $cost = (int) (Settings::where('name', 'BOOST_COST_CREDITS')->value('value') ?? 50);
        $duration = (int) (Settings::where('name', 'BOOST_DURATION_MINUTES')->value('value') ?? 45);

        if ($user->credits < $cost) {
            return response()->json([
                'success' => false,
                'error' => 'not_enough_credits',
                'credits' => $user->credits,
                'cost' => $cost,
            ], 402);
        }

        $user->credits = $user->credits - $cost;
        $user->boosted_until = now()->addMinutes($duration);
        $user->save();

        return response()->json([
            'success' => true,
            'credits' => $user->credits,
            'boosted_until' => $user->boosted_until->toIso8601String(),
            'boosted_until_human' => $user->boosted_until->format('H:i'),
        ]);
    }

    /**
     * Money-mode counterpart to activate(). Rather than talking to a payment provider directly
     * (the first version of this did, CentralPay-only), this creates a hidden, one-off Pack
     * (custom: 1, so it never shows on the public /packages listing - see Pack::where('custom',
     * '!=', 1) there) tagged with feature_key: 'boost', then sends the browser through the
     * exact same PaymentsController::newpayment() entry point every real package purchase
     * already uses - so CentralPay, PayPal, CCBill, and Wire-transfer (whichever the admin has
     * actually switched on - see SUPPORTED_PROVIDERS) all work here for free, with no
     * provider-specific code of Boost's own to get wrong. credits: 0 and type: 'credits' on the
     * hidden Pack mean that flow's normal "grant the pack" step is a harmless no-op; the real
     * effect (setting boosted_until) happens separately, in whichever payment success path
     * notices $pack->feature_key is set (see FeatureActivationRegistry).
     */
    public function checkout(Request $request)
    {
        abort_if(Settings::where('name', 'BOOST_FEATURE_ENABLED')->value('value') === 'no', 404);

        if ($this->priceMode() !== 'money') {
            abort(409);
        }

        if (Auth::user()->isAdmin()) {
            abort(403);
        }

        $activeProviders = Settings::whereIn('name', self::SUPPORTED_PROVIDERS)
            ->where('value', 'yes')
            ->pluck('name');

        if ($activeProviders->isEmpty()) {
            // Nothing Boost can use is switched on (e.g. only Stripe is active) - sent back
            // with a message rather than into a checkout flow that has no way to actually take
            // payment. boost-widget.blade.php's error box/alert (activateBoost() in dating.js)
            // is only wired for the AJAX credits path, not this page-navigation one, so this
            // uses the plain session flash + redirect pattern instead.
            return redirect()->back()->with('status', 'Plata cu bani reali nu este disponibila momentan. Incearca din nou mai tarziu.');
        }

        $price = (float) (Settings::where('name', 'BOOST_PRICE_AMOUNT')->value('value') ?? 4.00);
        $duration = (int) (Settings::where('name', 'BOOST_DURATION_MINUTES')->value('value') ?? 45);

        $pack = $this->createHiddenPack($price, $duration);

        if ($activeProviders->count() === 1) {
            return redirect()->route('new_payment', ['pack_id' => $pack->id, 'payment_method' => $activeProviders->first()]);
        }

        // More than one option - a small dedicated picker instead of forcing a choice, mirrors
        // (in miniature) the "Select Payment method" modal /packages already has, since that
        // modal's markup/JS lives on the packages page itself, not wherever Boost's button is.
        return view('boost_checkout_select', [
            'title' => l('Select Payment method'),
            'pack' => $pack,
            'price' => $price,
            'activeProviders' => $activeProviders,
        ]);
    }

    private function createHiddenPack(float $price, int $durationMinutes): Pack
    {
        $pack = new Pack();
        $pack->custom = 1;
        $pack->name = 'Boost';
        $pack->price = $price;
        $pack->credits = 0;
        $pack->currency = 'EUR';
        $pack->featured = 0;
        $pack->type = 'credits';
        $pack->duration = 0;
        $pack->feature_key = 'boost';
        $pack->feature_duration_minutes = $durationMinutes;
        $pack->save();

        return $pack;
    }

    private function priceMode(): string
    {
        $mode = Settings::where('name', 'BOOST_PRICE_MODE')->value('value');

        return $mode === 'money' ? 'money' : 'credits';
    }
}
