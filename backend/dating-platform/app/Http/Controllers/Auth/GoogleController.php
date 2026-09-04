<?php

namespace App\Http\Controllers\Auth;

use App\Client;
use App\Http\Controllers\Controller;
use App\Pack;
use App\Referral;
use App\User;
use App\User_Pack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Login/Register with Google" - one button on the auth modals/landing page covers both:
 * an existing account (matched by email) logs straight in, a new email creates one, mirroring
 * RegisterController::create()'s user setup (gender is hardcoded 'male' there too - this site's
 * real registrants are exclusively male, matching LoginController::credentials()'s own
 * male-only constraint on the normal email/password login).
 */
class GoogleController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect()
    {
        $this->abortIfDisabled();

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $this->abortIfDisabled();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect('/')->with('error', ['google', 'Could not sign in with Google. Please try again.']);
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return redirect('/')->with('error', ['google', 'Your Google account has no email address we can use.']);
        }

        $existing = User::where('email', $email)->where('gender', 'male')->first();

        if ($existing) {
            Auth::loginUsingId($existing->id, true);

            $sessionLang = session('lang');
            if ($sessionLang && $existing->lang != $sessionLang) {
                $existing->lang = $sessionLang;
                $existing->save();
            }

            return redirect('/find-friends');
        }

        $name = trim((string) $googleUser->getName());
        $nameParts = $name !== '' ? preg_split('/\s+/', $name, 2) : [];
        $firstname = $nameParts[0] ?? 'Google';
        $lastname = $nameParts[1] ?? 'User';

        $clients = Client::select('email')->get()->pluck('email')->toArray();

        $user = new User;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        if (in_array($user->email, $clients)) {
            $user->banned = 'no';
        }
        if (Session::has('referral')) {
            $user->banned = 'no';
        }
        if (Cookie::get('referral') != 'yes') {
            $user->banned = 'no';
        }
        // Never used to log in (Google is the only way in for this account) - a random hash
        // just satisfies the column, and still works as a fallback if they ever use "forgot
        // password" to set a real one.
        $user->password = Hash::make(Str::random(32));
        $user->gender = 'male';
        $user->country = get_user_country();
        $user->county = get_user_region();
        $user->city = get_user_city();
        $user->lang = get_user_browser_lang();
        $user->save();
        $user->username = strtolower($firstname.$lastname.$user->id);
        $user->save();

        $pack = Pack::where('name', 'Trial')->exists();
        if ($pack) {
            $pack = Pack::where('name', 'Trial')->firstOrFail();
            $add_pack = new User_Pack;
            $add_pack->user_id = $user->id;
            $add_pack->pack_id = $pack->id;
            $add_pack->expiration_date = date('Y-m-d H:i:s', strtotime('+'.$pack->duration.' day'));
            $add_pack->save();
            $user->credits = $user->credits + $pack->credits;
            $user->save();
        }

        if (Session::has('referral') && Cookie::get('referral') != 'yes') {
            $referral = Session::get('referral');
            $set_status = Referral::where('user', $referral)->where('referral', $user->email);
            if ($set_status->exists()) {
                $set_status = $set_status->firstOrFail();
                $set_status->status = 1;
                $set_status->save();
            } else {
                $new_ref = new Referral;
                $new_ref->user = $referral;
                $new_ref->referral = $user->email;
                $new_ref->status = 1;
                $new_ref->save();
            }
            Session::forget('referral');
            Cookie::queue(Cookie::forever('referral', 'yes'));
        }

        // Read once by the TikTok Pixel snippet in layouts/layout.blade.php on the very next
        // page load, same as RegisterController::create().
        session()->flash('tiktok_track_registration', true);

        Auth::loginUsingId($user->id, true);

        return redirect('/profile');
    }

    // The GOOGLE_LOGIN_ENABLED switch (see config/services.php) isn't just a cosmetic hide on
    // the button - these routes 404 when it's off, so the feature is fully gone, not just
    // unreachable-but-still-there for anyone who happens to have the URL. Checked per-method
    // rather than in the constructor - aborting in the constructor would throw any time this
    // controller is merely instantiated (e.g. `php artisan route:list` resolves controllers
    // to read their middleware), not just on an actual request to one of its routes.
    private function abortIfDisabled(): void
    {
        abort_unless((bool) config('services.google.enabled'), 404);
    }
}
