<?php

namespace App\Http\Controllers;

use App\AIProfile;
use App\AISetting;
use App\Conversation;
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

        if (Auth::check() && Auth::user()->isAdmin()) {
            $users = User::where('gender', 'male')->with('images')->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc')->take(20)->get();
        } else {
            $users = User::where('gender', 'female')->with('images')->take(20)->get();
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
        if (Auth::check() && Auth::user()->isAdmin()) {
            $user = User::where('gender', 'male')->with('images')->where('id', $id)->firstOrFail();
        } else {
            $user = User::where('gender', 'female')->with('images')->where('id', $id)->firstOrFail();
        }

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
                                                <a href="/profile/' . $user->username . '" class="  btn btn-control bg-blue"
                                                    data-toggle="tooltip" data-placement="top"
                                                    data-original-title="' . l("See Profile") . '">
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

        if (Auth::check() && Auth::user()->isAdmin()) {
            $users = User::where('gender', 'male')->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc')->skip($items)->take(20)->get();
        } else {
            $users = User::where('gender', 'female')->skip($items)->take(20)->get();
        }

        $tpl = '';
        if ($users->count() == 0) {
            $tpl = '<div class="find-friends-no-result"><span>No results</span></div>';
            return response()->json(['tpl' => $tpl, 'results' => 0]);
        }
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

        if (Auth::check() && Auth::user()->isAdmin()) {
            $users = User::where('gender', 'male')->where('status', 'online')->orderby('status', 'desc')->orderby('created_at', 'desc')->orderBy('gender', 'desc')->take(20)->get();
        } else {
            $users = User::where('gender', 'female')->where('status', 'online')->take(20)->get();
        }

        return view('find_friends', compact('title', 'users'));
    }
}
