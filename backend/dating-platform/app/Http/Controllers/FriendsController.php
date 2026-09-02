<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Friend;
use App\User;
use App\FriendRequest;
use Illuminate\Support\Facades\Auth;
use App\Events\NewFriendRequest;
use App\Events\AccFriendRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FriendsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Auth::user()->allFriends());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if(Auth::user()->hasPermission('friends')){
        $req1 = FriendRequest::where('user_from', Auth::id())->where('user_to', $id)->with('userFromRelationship');
        $req2 = FriendRequest::where('user_to', Auth::id())->where('user_from', $id)->with('userFromRelationship');
        if(!$req2->exists() && !$req1->exists()){
            $new = new FriendRequest;
            $new->user_from = Auth::id();
            $new->user_to = $id;
            $new->save();
            $date = date_format(date_create($new->created_at),"d/m/Y");
            $user_info = ['id' => $new->userFromRelationship->id, 'profile_image' => $new->userFromRelationship->profile_image(), 'name' => $new->userFromRelationship->name(), 'username' => $new->userFromRelationship->username];
            try {
                event(new NewFriendRequest(['type' => 'add', 'request_data' => $new, 'user_data' => $user_info, 'date' => $date]));
            } catch (\Throwable $e) {
                Log::error('Broadcast failed in FriendsController@create.', [
                    'from_user' => Auth::id(),
                    'to_user' => $id,
                    'exception' => $e->getMessage(),
                ]);
            }
            return response()->json(['res' => 'Pending...', 'id' => $id, 'url' => '/delete-request/'.$id]);
        }
        }else{
            return response()->json(['res' => '/packages']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function live_request()
    {
        $user = User::where('gender', 'female')->whereNotIn('id', Auth::user()->allFriendRequestsUserIds())->whereNotIn('id', Auth::user()->allFriendsIds())->inRandomOrder();
        $last_req = FriendRequest::where('user_to', Auth::id())->orderBy('created_at', 'desc');
        $last_req2 = Friend::where('user', Auth::id())->orderBy('created_at', 'desc');
        if($last_req->exists() || $last_req2->exists()){
            $now = Carbon::now();
            $diff = false;
            $diff2 = false;

            if($last_req2->exists()){
                $last_req2 = $last_req2->firstOrFail();
                $last_date2 = $last_req2->created_at;
                $diff2 = $now->diffInSeconds($last_date2);
            }

            $minutes = 5;
            if($diff != false && $diff < 60*$minutes || $diff2 != false && $diff2 < 60*$minutes){
                $check = false;
            }else{
                $check = true;
            }
        }else{
            $check = true;
        }
        if($user->exists() && $check){
            $user = $user->firstOrFail();
            $id = $user->id;
        }else{
            $id = false;
        }
        if($id){
            $req = FriendRequest::where('user_from', Auth::id())->where('user_to', $id)->orWhere(function($q) use($id){
                $q->where('user_to', Auth::id())->where('user_from', $id);
            });

            if(!$req->exists()){
                $new = new FriendRequest;
                $new->user_from = $id;
                $new->user_to = Auth::id();
                $new->save();
                $date = date_format(date_create($new->created_at),"d/m/Y");
                $user_info = ['id' => $new->userFromRelationship->id, 'profile_image' => $new->userFromRelationship->profile_image(), 'name' => $new->userFromRelationship->name(), 'username' => $new->userFromRelationship->username];
                try {
                    event(new NewFriendRequest(['type' => 'add', 'request_data' => $new, 'user_data' => $user_info, 'date' => $date]));
                } catch (\Throwable $e) {
                    Log::error('Broadcast failed in FriendsController@live_request.', [
                        'from_user' => $id,
                        'to_user' => Auth::id(),
                        'exception' => $e->getMessage(),
                    ]);
                }
                return response()->json(true);
            }
        }
        return response()->json(false);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        if(Auth::user()->hasPermission('friends')){
        if(!Friend::where('user' , Auth::id())->where('user_friend', $id)->exists() && !Friend::where('user_friend' , Auth::id())->where('user', $id)->exists()){
            $req2 = FriendRequest::where('user_to', Auth::id())->where('user_from', $id);
            $friend = new Friend;
            $friend->user = Auth::id();
            $friend->user_friend = $id;
            $friend->save();
            $req2->delete();
            $user = User::where('id', $id)->firstOrFail();
            try {
                event(new AccFriendRequest(['from' => Auth::id(),'id' => $id,'url' => '/delete-friend/'.Auth::id(),'status' => Auth::user()->status,'profile_image' => Auth::user()->profile_image(), 'txt' => 'Friends']));
            } catch (\Throwable $e) {
                Log::error('Broadcast failed in FriendsController@store.', [
                    'from_user' => Auth::id(),
                    'to_user' => $id,
                    'exception' => $e->getMessage(),
                ]);
            }
            return response()->json(['res' => 'Friends', 'id' => $id, 'url' => '/delete-friend/'.$id, 'status' => $user->status, 'profile_image' => $user->profile_image()]);
        }
        }else{
            return response()->json(['res' => '/packages']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_request($id)
    {
        if(Auth::user()->hasPermission('friends')){
        $req = FriendRequest::where('user_from', Auth::id())->where('user_to', $id);
        $req2 = FriendRequest::where('user_to', Auth::id())->where('user_from', $id);
        if($req->exists()){
            try {
                event(new NewFriendRequest(['type' => 'delete', 'request_data' => $req->firstOrFail()]));
            } catch (\Throwable $e) {
                Log::error('Broadcast failed in FriendsController@delete_request.', [
                    'from_user' => Auth::id(),
                    'to_user' => $id,
                    'exception' => $e->getMessage(),
                ]);
            }
            $req->delete();
            return response()->json(['res' => 'Add Friend', 'id' => $id, 'url' => '/add-friend/'.$id, 'count' => Auth::user()->allFriendRequests()->count()]);
        }elseif($req2->exists()){
            $req2->delete();
            return response()->json(['res' => 'Add Friend', 'id' => $id, 'url' => '/add-friend/'.$id, 'count' => Auth::user()->allFriendRequests()->count()]);
        }
        }else{
            return response()->json(['res' => '/packages']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $req = Friend::where('user' , Auth::id())->where('user_friend', $id);
        $req2 = Friend::where('user_friend' , Auth::id())->where('user', $id);

        if($req->exists()){
            $req->delete();
            return response()->json(['res' => 'Add Friend', 'id' => $id, 'url' => '/add-friend/'.$id]);
        }
        if($req2->exists()){
            $req2->delete();
            return response()->json(['res' => 'Add Friend', 'id' => $id, 'url' => '/add-friend/'.$id]);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->hasPermission('friends')){
        $user = User::where('username', $id)->firstOrFail();

        $friends = User::whereIn('id', function($q) use ($user){
                        $q->select('user')->from('friends')->where('user_friend', $user->id);
                   })->orWhereIn('id', function($q1) use ($user){
                        $q1->select('user_friend')->from('friends')->where('user', $user->id);
                   })->where('gender', 'female')->orderBy('username', 'asc')->paginate(20);
        $title = 'Friends';
        return view('friends', compact('title', 'user', 'friends'));
        }else{
            return redirect('/packages');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->search;
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $blockedFriends = Auth::user()->allBlocked();

        $friends = User::whereIn('id', function($q) use ($user, $blockedFriends){
                        $q->select('user')->from('friends')->where('user_friend', $user->id)->whereNotIn('user', $blockedFriends);
                   })->orWhereIn('id', function($q1) use ($user, $blockedFriends){
                        $q1->select('user_friend')->from('friends')->where('user', $user->id)->whereNotIn('user_friend', $blockedFriends);
                   })->get();
        if($search !== ''){
            $friends = $friends->filter(function ($item) use ($search) {
                return strstr($item->firstname, $search) ||
               strstr($item->lastname, $search) ||
               strstr($item->username, $search) ||
               strstr($item->email, $search);
            })->take(10);
        }else{
            $friends->take(10);
        }
        $tpl = '';
        foreach ($friends as $friend) {
            $tpl .= '<li data-id="'.$friend->id.'"';
            if($isAdmin){
                $tpl .= ' data-from="'.Auth::id().'"';
            }
            $tpl .= ' onClick="chat_open(this,event);" class="inline-items js-chat-open">';
            $tpl .= '<div class="author-thumb">
                        <img alt="author" src="/storage/images/'.$friend->profile_image().'" class="avatar">
                        <span  data="status" class="icon-status '.$friend->status.'"></span>
                    </div>

                    <div class="author-status">
                        <a href="#" class="h6 author-name">'.$friend->name().'</a>
                        <span class="status">'.$friend->moto.'</span>
                    </div>

                    <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>

                        <ul class="more-icons">
                            <li>
                                <svg data-toggle="tooltip" data-placement="top" data-original-title="'.l('START CONVERSATION').'" class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                            </li>

                            <li>
                                <svg data-toggle="tooltip" data-placement="top" data-original-title="'.l('ADD TO CONVERSATION').'" class="olymp-add-to-conversation-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-add-to-conversation-icon"></use></svg>
                            </li>

                            <li>
                                <svg data-toggle="tooltip" data-placement="top" data-original-title="'.l('BLOCK FROM CHAT').'" class="olymp-block-from-chat-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-block-from-chat-icon"></use></svg>
                            </li>
                        </ul>

                    </div>

                </li>';
        }
        return response()->json($tpl);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
