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

        $tpl = '<div data-user-id="' . $user->id . '" class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 find_friends_item">
                        <div class="ui-block" data-mh="friend-groups-item"
                            style=\'';
        if ($user->images->count() > 0) {
            $tpl .= 'background: url("/storage/images/' . $user->images->take(1)[0]->name . '");';
        }
        $tpl .= 'height:415px;\' >
                                <span class="find_friends_status span-online">Online</span>
                                <!-- Friend Item -->
                                <div class="friend-item friend-groups">

                                    <div class="friend-item-content">

                                        <div class="friend-avatar">

                                            <div class="author-thumb find-friends-item">';

        if ($user->images->count() == 0) {
            $tpl .= '<a href="/profile/' . $user->username . '"><img
                                                            src="/storage/images/' . $user->profile_image() . '"
                                                            alt="' . $user->name() . '"></a>';
        }
        $tpl .= '</div>
                                        </div>
                                        <div class="friend-actions">
                                            <div class="author-content">
                                                <a href="/profile/' . $user->username . '"
                                                    class="h5 author-name">' . $user->name();
        if ($user->age() != 0) {
            $tpl .= ', ' . $user->age();
        }
        $tpl .= '</a>
                                            </div>
                                            <div class="control-block-button">
                                                <a href="/profile/' . $user->username . '" class="  btn btn-control bg-blue"'
                                                    . ($activeTheme === 'rosewood' ? '' : '
                                                    data-toggle="tooltip" data-placement="top"
                                                    data-original-title="' . l("See Profile") . '"') . '>
                                                    ' . l("See Profile") . '
                                                </a>';
        if (Auth::check()) {
            $tpl .= '<a href="#" data-id="' . $user->id . '"
                                                        onclick="chat_open(this,event);" class="btn btn-control bg-purple"
                                                        data-toggle="tooltip" data-placement="top"
                                                        data-original-title="' . l("Start chatting") . '">
                                                        ' . l("Chat") . '
                                                    </a>';
        }
        $tpl .= '</div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>';
                    
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
        // Only read/branched on for the 'binder' theme's real 3D card flip (see themes/
        // binder.css's "collectible-card" section) - every other theme (classic included)
        // takes the exact same code path as before this was added, byte-for-byte.
        $activeTheme = ActiveTheme::current();
        foreach ($users as $user) {
            $com = '';
            if (Auth::check()) {
                foreach ($user->commonFriends()->take(5) as $common) {
                    $com .= '<li data-toggle="tooltip" data-placement="top" title="" data-original-title="' . $common->name() . '">
                                <a href="/profile/' . $common->username . '">
                                    <img src="/storage/images/' . $common->profile_image() . '" alt="' . $common->name() . '">
                                </a>
                            </li>';
                }
            }
            if ($activeTheme === 'binder') {
                // Mirrors find_friends.blade.php's own @if($activeTheme === 'binder') branch
                // for the real (initial page load) cards - a real front/back flip needs a
                // second "back face" element (name + actual See Profile/Chat links again),
                // not just CSS, so this AJAX "load more"/search path needs the same structural
                // branch to keep infinite-scroll-loaded cards visually identical to the ones
                // rendered on first load (the same principle every theme in this project
                // follows for the plain, non-flip card look).
                $tpl .= '<div class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 find_friends_item">
            <div class="ui-block" data-mh="friend-groups-item" ';
                if ($user->images->count() > 0) {
                    $tpl .= 'style="background: url(\'/storage/images/' . $user->images->take(1)[0]->name . '\');"';
                }
                $tpl .= '>';
                if ($user->status == 'online' || $user->gender == 'female') {
                    $tpl .= '<span class="find_friends_status span-online">Online</span>';
                } else {
                    $tpl .= '<span class="find_friends_status span-offline">Offline</span>';
                }
                $tpl .= '<div class="friend-item friend-groups binder-flip">
                    <div class="friend-item-content">
                        <div class="friend-avatar">
                            <div class="author-thumb find-friends-item">';
                if ($user->images->count() == 0) {
                    $tpl .= '<a href="/profile/' . $user->username . '"><img src="/storage/images/' . $user->profile_image() . '" alt="' . $user->name() . '"></a>';
                }
                $tpl .= '</div>
                        </div>
                        <div class="friend-actions" data-initial="' . strtoupper(substr($user->name(), 0, 1)) . '">
                            <div class="author-content">
                                <a href="/profile/' . $user->username . '" class="h5 author-name">' . $user->name();
                if ($user->age() != 0) {
                    $tpl .= ', ' . $user->age();
                }
                $tpl .= '</a>
                            </div>
                        </div>
                        <div class="friend-actions-back">
                            <div class="author-content">
                                <a href="/profile/' . $user->username . '" class="h5 author-name">' . $user->name();
                if ($user->age() != 0) {
                    $tpl .= ', ' . $user->age();
                }
                $tpl .= '</a>
                            </div>
                            <div class="control-block-button">
                                <a href="/profile/' . $user->username . '" class="  btn btn-control bg-blue" data-toggle="tooltip" data-placement="top" data-original-title="' . l("See Profile") . '">' . l("See Profile") . '</a>';
                if (Auth::check()) {
                    $tpl .= '<a href="#" data-id="' . $user->id . '" onclick="chat_open(this,event);" class="btn btn-control bg-purple" data-toggle="tooltip" data-placement="top" data-original-title="' . l("Start chatting") . '">' . l("Chat") . '</a>';
                }
                $tpl .= '</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
                continue;
            }
            $tpl .= '<div class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="ui-block" data-mh="friend-groups-item" ';
            if ($user->images->count() > 0) {
                $tpl .= 'style="background: url(\'/storage/images/' . $user->images->take(1)[0]->name . '\');"';
            }
            $tpl .= '>

                <!-- Friend Item -->

                <div class="friend-item friend-groups">

                    <div class="friend-item-content">

                        <div class="friend-avatar">
                            <div class="author-thumb find-friends-item">';
            if ($user->images->count() == 0) {
                $tpl .= '<a href="/profile/' . $user->username . '"><img src="/storage/images/' . $user->profile_image() . '" alt="' . $user->name() . '"></a>';
            }
            $tpl .= '</div>
                            <div class="author-content">
                                <a href="/profile/' . $user->username . '" class="h5 author-name">' . $user->name();
            if ($user->age() != 0) {
                $tpl .= ', ' . $user->age();
            }
            $tpl .= '</a>';
            if ($user->status == 'online' || $user->gender == 'female') {
                $tpl .= '<span class="span-online">Online</span>';
            } else {
                $tpl .= '<span class="span-offline">Offline</span>';
            }
            $tpl .= '</div>
                        </div>

                        <ul class="friends-harmonic">
                            ' . $com . '
                        </ul>


                        <div class="control-block-button">
                            <a href="/profile/' . $user->username . '" class="  btn btn-control bg-blue" data-toggle="tooltip" data-placement="top" data-original-title="' . l("See Profile") . '">
                                <svg class="olymp-magnifying-glass-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                            </a>';
            if (Auth::check()) {
                $tpl .= '<a href="#" data-id="' . $user->id . '" onclick="chat_open(this,event);" class="btn btn-control bg-purple" data-toggle="tooltip" data-placement="top" data-original-title="' . l("Start chatting") . '">
                                <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                            </a>';
            }
            $tpl .= '</div>
                    </div>
                </div>

            </div>
        </div>';
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
}
