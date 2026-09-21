<?php

namespace App\Http\Controllers;

use App\AIProfile;
use App\AISetting;
use App\Conversation;
use App\Support\ActiveTheme;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FindFriendsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = l('Find Friends');

        // Optional theme-hero search/sort (only sent by the aurora/nordic themed search bar
        // - absent for the classic theme, so its query/order stays byte-identical to before).
        $searchTerm = trim((string) $request->input('q', ''));
        $sort = $request->input('sort');

        if (Auth::check() && Auth::user()->isAdmin()) {
            $query = User::with('images');
            $this->applyFindFriendsGenderScope($query);
            $this->applyFindFriendsSearch($query, $searchTerm);
            $this->applyBoostOrdering($query);
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } else {
                $query->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc');
            }
            $users = $query->take(20)->get();
        } else {
            $query = User::with('images');
            $this->applyFindFriendsGenderScope($query);
            $this->applyFindFriendsSearch($query, $searchTerm);
            $this->applyBoostOrdering($query);
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            }
            $users = $query->take(20)->get();
        }

        $aiProfiles = AIProfile::where('is_active', true)->orderBy('name')->take(8)->get();
        $aiSetting = AISetting::current();

        // Shared with ChatController::startAiVideoSession() so both the AI Companions live
        // video and the real-profile live AI video call honor the same package-tier privacy
        // settings identically (also where the defensive try/catch around package() lives,
        // since that data can be inconsistent for a given account — see AISetting::resolveVideoPrivacyForUser()).
        $videoPrivacy = $aiSetting->resolveVideoPrivacyForUser(Auth::check() ? Auth::user() : null);
        $videoBlurAmount = $videoPrivacy['amountPx'];
        $audioMuted = $videoPrivacy['audioMuted'];

        $aiInbox = [];

        if (Auth::check() && Auth::user()->isAdmin()) {
            try {
                $aiInbox = Conversation::query()
                    ->whereIn('user_id', Auth::user()->getAccountIds())
                    ->where('message_count', '>', 0)
                    ->with(['user:id,firstname,lastname,username', 'aiProfile:id,name,static_image_path'])
                    ->orderByDesc('updated_at')
                    ->limit(30)
                    ->get()
                    ->filter(fn ($conversation) => $conversation->user !== null && $conversation->aiProfile !== null)
                    ->map(fn ($conversation) => [
                        'ai_profile_id' => (int) $conversation->ai_profile_id,
                        'ai_profile_name' => (string) $conversation->aiProfile->name,
                        'ai_profile_image' => $conversation->aiProfile->imageUrl(),
                        'user_id' => (int) $conversation->user_id,
                        'user_name' => (string) $conversation->user->name(),
                        'preview' => mb_substr((string) data_get($conversation->conversion_context, 'last_assistant_preview', ''), 0, 120),
                        'updated_at' => optional($conversation->updated_at)->toIso8601String(),
                    ])
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                $aiInbox = [];
            }
        }

        return view('find_friends', compact('title', 'users', 'aiProfiles', 'aiSetting', 'videoBlurAmount', 'audioMuted', 'aiInbox'));
    }

    public function getFindFriendsUser($id)
    {
        if($id == Auth::id()){
            return response()->json(false);
        }
        $query = User::with('images')->where('id', $id);
        $this->applyFindFriendsGenderScope($query);
        $user = $query->firstOrFail();
        $activeTheme = ActiveTheme::current();

        // Same shared card partial as the first page load and search() - see
        // components/find_friends_card.blade.php. forceOnline: this is only ever called the
        // moment the user comes online (online.js setFindFriendsUserState), before users.status
        // is necessarily updated, so the badge must not depend on it.
        $tpl = view('components.find_friends_card', [
            'user' => $user,
            'activeTheme' => $activeTheme,
            'isRouletteSlot' => false,
            'forceOnline' => true,
        ])->render();

        return response()->json(['tpl' => $tpl]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $items = 0;
        if ($request->items) {
            $items = $request->items;
        }

        // Same optional theme-hero search/sort as index() - absent for classic, so infinite
        // scroll there keeps loading pages in the exact same order/filter as before.
        $searchTerm = trim((string) $request->input('q', ''));
        $sort = $request->input('sort');

        if (Auth::check() && Auth::user()->isAdmin()) {
            $query = User::query();
            $this->applyFindFriendsGenderScope($query);
            $this->applyFindFriendsSearch($query, $searchTerm);
            $this->applyBoostOrdering($query);
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } else {
                $query->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc');
            }
            $users = $query->skip($items)->take(20)->get();
        } else {
            $query = User::query();
            $this->applyFindFriendsGenderScope($query);
            $this->applyFindFriendsSearch($query, $searchTerm);
            $this->applyBoostOrdering($query);
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            }
            $users = $query->skip($items)->take(20)->get();
        }

        $tpl = '';
        if ($users->count() == 0) {
            $tpl = '<div class="find-friends-no-result"><span>No results</span></div>';
            return response()->json(['tpl' => $tpl, 'results' => 0]);
        }
        // Every theme - classic and binder included - renders the exact same partial as the first
        // page load (find_friends.blade.php), so "load more"/search cards can never drift from the
        // real ones again. This used to hand-build its own HTML per theme, which had no
        // .find_friends_item wrapper (unstyled/narrow cards under bloom, nordic, rosewood, ...) and
        // never included the hover-preview video for any theme.
        $activeTheme = ActiveTheme::current();
        foreach ($users as $user) {
            $tpl .= view('components.find_friends_card', [
                'user' => $user,
                'activeTheme' => $activeTheme,
                'isRouletteSlot' => false,
            ])->render();
        }

        return response()->json(['tpl' => $tpl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function onlineusers(Request $request)
    {
        $title = l('Find Friends');

        $query = User::where('status', 'online');
        $this->applyFindFriendsGenderScope($query);
        $this->applyBoostOrdering($query);
        if (Auth::check() && Auth::user()->isAdmin()) {
            $query->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc');
        }
        $users = $query->take(20)->get();

        return view('find_friends', compact('title', 'users'));
    }

    /**
     * Restricts a users query by gender, matching the same visibility rule the header
     * quick-search already uses (ProfileController::search(), which does NOT gate purely on
     * isAdmin()): `(gender=male && isAdmin()) || (gender=female && role!=editor)`. Applied per
     * role:
     * - 'admin': no restriction at all - both genders visible (this is the actual behavior
     *   change requested; previously this controller treated 'admin' the same as 'editor',
     *   i.e. male-only, which made Find Friends inconsistent with the header search).
     * - 'editor': male only (unchanged from before - isAdmin() is true for editors too, but
     *   the header search's own female clause explicitly excludes them).
     * - anyone else (regular user or guest): female only (unchanged).
     */
    private function applyFindFriendsGenderScope($query): void
    {
        $role = Auth::check() ? Auth::user()->role : null;

        if ($role === 'admin') {
            return;
        }

        if ($role === 'editor') {
            $query->where('gender', 'male');
            return;
        }

        $query->where('gender', 'female');
    }

    /**
     * Applies the optional theme-hero search box's free-text filter (name/city) to a users
     * query, in place. No-op when the search term is empty, so callers that never receive a
     * `q` param (i.e. every request from the classic theme, which has no search box) behave
     * exactly as before this method existed.
     */
    private function applyFindFriendsSearch($query, string $searchTerm): void
    {
        if ($searchTerm === '') {
            return;
        }

        $query->where(function ($q) use ($searchTerm) {
            $q->where('firstname', 'like', '%' . $searchTerm . '%')
                ->orWhere('lastname', 'like', '%' . $searchTerm . '%')
                ->orWhere('city', 'like', '%' . $searchTerm . '%');
        });
    }

    /**
     * "Boost your profile" (BoostController@activate) - a user who paid to boost sets
     * users.boosted_until to a few minutes in the future; while that's still ahead of now(),
     * they sort before everyone else, ahead of whatever ordering the caller applies next
     * (status/created_at/gender, or nothing at all for the non-admin "no explicit sort" case)
     * - call this BEFORE those, not instead of them, so it only ever reorders within/across
     * the boosted-vs-not split rather than replacing the existing tiebreakers.
     *
     * Binds PHP's now() as a parameter instead of using SQL NOW() - this DB server's clock
     * runs 3 hours ahead of the app's (APP_TIMEZONE=UTC vs. the DB server's local time), and
     * boosted_until is written using PHP's now() (BoostController@activate), so comparing it
     * against SQL NOW() instead made every boost register as already-expired immediately.
     */
    private function applyBoostOrdering($query): void
    {
        $query->orderByRaw('CASE WHEN boosted_until IS NOT NULL AND boosted_until > ? THEN 0 ELSE 1 END ASC', [now()]);
    }
}
