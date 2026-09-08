<?php

namespace App\Http\Controllers;

use App\Settings;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Boost your profile" - spend credits to rank higher in Find Friends for a limited time (see
 * FindFriendsController's boosted_until ordering). Priced in credits, not a separate payment
 * flow, so it reuses the same bare users.credits decrement pattern as VideoController@credits
 * and ChatController@send's per-message credit spend - there's no credits-transaction/history
 * table anywhere in this app, so this doesn't add one either, for consistency.
 */
class BoostController extends Controller
{
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
}
