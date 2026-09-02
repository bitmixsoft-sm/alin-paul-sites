<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use function foo\func;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'gender', 'email', 'password', 'username', 'profile_image', 'cover_image', 'birthday', 'background_image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getNameAttribute()
    {
        return trim(($this->firstname ?? '') . " " . ($this->lastname ?? ''));
    }

    public function name()
    {
        return $this->getNameAttribute();
    }

    public function blocked()
    {
        $blocked = Block::where('user', $this->id)->select('block')->get();

        return $blocked->toArray();
    }

    public function allBlocked()
    {
        return Cache::rememberForever('allBlocked.' . $this->id, function(){
            $blocked1 = Block::where('user', $this->id)->select('block')->get();
            $blocked2 = Block::where('block', $this->id)->select('user')->get();

            $blocked1 = $blocked1->pluck('block');
            $blocked2 = $blocked2->pluck('user');

            return $blocked1->merge($blocked2)->toArray();
        });
    }

    public function images()
    {
        return $this->hasMany('App\ImageGet')->where('role', '!=', 'profile')->where('role', '!=', 'cover')->orderBy('created_at', 'desc');
    }

    public function albums()
    {
        return $this->hasMany('App\Album')->orderBy('created_at', 'desc');
    }

    public function profile_image()
    {
        if ($this->profile_image != '') {
            return $this->profile_image;
        } else {
            return 'default.png';
        }
    }

    public function cover_image()
    {
        if ($this->cover_image != '') {
            return $this->cover_image;
        } else {
            return 'default-cover.png';
        }
    }

    public function hasFriendRequestFromAuth()
    {
        $req = FriendRequest::where('user_from', Auth::id())->where('user_to', $this->id)->exists();
        if ($req) {
            return true;
        } else {
            return false;
        }
    }

    public function hasFriendRequest()
    {
        $req = FriendRequest::where('user_to', Auth::id())->where('user_from', $this->id)->exists();
        if ($req) {
            return true;
        } else {
            return false;
        }
    }

    public function allFriendRequests()
    {
        return FriendRequest::where('user_to', $this->id)->whereNotIn('user_from', $this->allBlocked())->get();
    }

    public function allFriendRequestsUserIds()
    {
        $friend_requests1 = $this->allFriendRequests();
        $friend_requests2 = FriendRequest::where('user_from', $this->id)->get();

        $ids = [];
        foreach ($friend_requests1 as $account) {
            $ids[] = $account->user_from;
        }
        foreach ($friend_requests2 as $account) {
            $ids[] = $account->user_to;
        }
        return $ids;
    }

    public function isFriendWithAuth()
    {
        $case1 = ['user' => Auth::id(), 'user_friend' => $this->id];
        $case2 = ['user' => $this->id, 'user_friend' => Auth::id()];
        $req1 = Friend::where('user', Auth::id())->where('user_friend', $this->id)->exists();
        $req2 = Friend::where('user_friend', Auth::id())->where('user', $this->id)->exists();
        if ($req1 || $req2) {
            return true;
        } else {
            return false;
        }
    }

    public function friends(){
        return $this->belongsToMany(User::class, 'friends', 'user', 'user_friend');
    }

    public function friendsInverse(){
        return $this->belongsToMany(User::class, 'friends', 'user_friend', 'user');
    }

    public function allFriendsRefactored($gender = 'all'){
        $blockedFriends = $this->allBlocked();
        if ($gender === 'all') {
            $friends1 = $this->friends()->where('users.id', '!=', $this->id)->whereNotIn('friends.user_friend', $blockedFriends)->get();
            $friends2 = $this->friendsInverse()->where('users.id', '!=', $this->id)->whereNotIn('friends.user', $blockedFriends)->get();
        } else {
            $friends1 = $this->friends()->where('gender', $gender)->where('users.id', '!=', $this->id)->whereNotIn('friends.user_friend', $blockedFriends)->get();
            $friends2 = $this->friendsInverse()->where('gender', $gender)->where('users.id', '!=', $this->id)->whereNotIn('friends.user', $blockedFriends)->get();
        }
        return $friends1->merge($friends2);
    }

    public function allFriends($gender = 'all')
    {
        $blockedFriends = $this->allBlocked();
        if ($gender == 'all') {
            $friends = User::whereIn('id', function ($q) use($blockedFriends){
                $q->select('user')->from('friends')->where('user_friend', $this->id)->whereNotIn('user', $blockedFriends);
            })->orWhereIn('id', function ($q1) use($blockedFriends){
                $q1->select('user_friend')->from('friends')->where('user', $this->id)->whereNotIn('user_friend', $blockedFriends);
            })->where('id', '!=', $this->id)->get();
        } else {
            $friends = User::where('gender', $gender)->whereIn('id', function ($q) use($blockedFriends){
                $q->select('user')->from('friends')->where('user_friend', $this->id)->whereNotIn('user', $blockedFriends);
            })->orWhereIn('id', function ($q1) use($blockedFriends){
                $q1->select('user_friend')->from('friends')->where('user', $this->id)->whereNotIn('user_friend', $blockedFriends);
            })->where('gender', $gender)->where('id', '!=', $this->id)->get();
        }
        return $friends;
    }

    public function allFriendsNotIn($arr)
    {
        $friends = User::whereIn('id', function ($q) use ($arr) {
            $q->select('user')->from('friends')->whereNotIn('user', $arr)->where('user_friend', $this->id)->whereNotIn('user', $this->allBlocked());
        })->orWhereIn('id', function ($q1) use ($arr) {
            $q1->select('user_friend')->from('friends')->whereNotIn('user_friend', $arr)->where('user', $this->id)->whereNotIn('user_friend', $this->allBlocked());
        })->where('id', '!=', $this->id)->get();
        return $friends;
    }

    public function allFriendsIds()
    {
        $friends = User::whereIn('id', function ($q) {
            $q->select('user')->from('friends')->where('user_friend', $this->id)->whereNotIn('user', $this->allBlocked());
        })->orWhereIn('id', function ($q1) {
            $q1->select('user_friend')->from('friends')->where('user', $this->id)->whereNotIn('user_friend', $this->allBlocked());
        })->where('id', '!=', $this->id)->get();
        $ids = [];
        foreach ($friends as $account) {
            $ids[] = $account->id;
        }
        return $ids;
    }

    public function unreadMessages()
    {
        if ($this->isAdmin()) {
            $ids = $this->getAccountIds();
            $count = Message::whereIn('to_user', $ids)->where('status', 0)->count();
        } else {
            $count = Message::where('to_user', $this->id)->where('status', 0)->count();
        }
        return $count;
    }

    public function allMessagesCount()
    {
        $messages = Message::where('from_user', $this->id)->count();
        return $messages;
    }

    public function unreadFrom($id, $this_id = false)
    {
        if ($this->isAdmin() && !$this_id) {
            $ids = $this->getAccountIds();
            $count = Message::whereIn('to_user', $ids)->where('from_user', $id)->where('status', 0)->count();
        } else {
            $count = Message::where('to_user', $this->id)->where('from_user', $id)->where('status', 0)->count();
        }
        return $count;
    }

    public function hasUnreadFrom($id, $from)
    {
        if ($this->isAdmin()) {
            if (Message::where('to_user', $id)->where('from_user', $from)->where('status', 0)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function chat($id)
    {
        $case1 = $this->id . "-" . $id;
        $case2 = $id . "-" . $this->id;
        $chat = Chat::where('from_to', $case1)->orWhere('from_to', $case2);

        if (!$chat->exists()) {
            $chat = new Chat;
            $chat->from_to = $case1;
            $chat->from_user = $this->id;
            $chat->to_user = $id;
            $chat->save();
            return $chat;
        }

        return $chat->firstOrFail();
    }

    public function getAccountIdsRefactored(){
        $accounts = $this->getAccountsRelationship()->with('getUserRelationship')->get();
        $ids = [$this->id];
        foreach ($accounts as $account) {
            $ids[] = $account->getUserRelationship->id;
        }
        return $ids;
    }

    public function getAccountIds()
    {
       return Cache::rememberForever('accountIds.' . $this->id, function(){
            $accounts = $this->getAccounts();
            $ids = [$this->id];
            foreach ($accounts as $account) {
                // A WebAccount can point at a user_id that no longer exists (deleted
                // account) — skip those instead of fatally erroring on every page load.
                if ($account->getUserRelationship) {
                    $ids[] = $account->getUserRelationship->id;
                }
            }

           return $ids;
        });
    }

    public function myChats(){
        return $this->hasMany(Chat::class, 'from_user');
    }

    public function myChatsInverse(){
        return $this->hasMany(Chat::class, 'to_user');
    }

    public function lastChats($n = 20, $skip = 0)
    {
        $usr = $this;
        $allBlocked = $usr->allBlocked();

        if ($this->isAdmin()) {
            $ids = $this->getAccountIds();
            $chats = Chat::orderBy('updated_at', 'desc')->where(function ($query) use ($allBlocked, $ids) {
                $query->whereIn('from_user', $ids);
                $query->whereNotIn('to_user', $allBlocked);
            })->orWhere(function ($query) use ($allBlocked, $ids) {
                $query->whereIn('to_user', $ids);
                $query->whereNotIn('from_user', $allBlocked);
            })->skip($skip)->take($n)->get();
        } else {
            $chats = Chat::orderBy('updated_at', 'desc')->where(function ($query) use ($usr, $allBlocked) {
                $query->where('from_user', $usr->id);
                $query->whereNotIn('to_user', $allBlocked);
            })->orWhere(function ($query) use ($usr, $allBlocked) {
                $query->where('to_user', $usr->id);
                $query->whereNotIn('from_user', $allBlocked);
            })->skip($skip)->take($n)->get();
        }

        // A Chat row's last_msg (or that Message's from_user/to_user) can point at a
        // since-deleted Message/User — every consumer of this list (chat-header, sidebar,
        // load_top) calls ->lastMessage()->userFrom()/userTo(), which use firstOrFail() and
        // would fatally 404 the whole page otherwise. Drop those rows here instead.
        return $chats->filter(function ($chat) {
            $message = Message::find($chat->last_msg);

            return $message !== null
                && User::where('id', $message->from_user)->exists()
                && User::where('id', $message->to_user)->exists();
        })->values();
    }

    public function lastUserChatsIds($n = 20)
    {
        return $this->allUserChatsOnly()->take($n);
    }

    public function welcomeMessage()
    {
        $welcomeMessage = WelcomeMessage::where('user_id', $this->id)->where('website', $_SERVER['SERVER_NAME']);

        if ($welcomeMessage->exists()) {
            $welcomeMessage = $welcomeMessage->first();
            return $welcomeMessage->message;
        }

        return false;
    }

    public function allUserChats($n = 20, $page = 1)
    {
        $allBlocked = $this->allBlocked();
        $friends = $this->allFriendsRefactored();

        $chat = User::whereIn('id', $this->allUserChatsOnly())->get();

        $users = $chat->merge($friends);

        $users = $users->sortBy('firstname');
        if ($n == 'all') {
            return $users;
        } else {
            $users = $users->forPage($page, $n);
        }

        return $users;
    }

    public function allUserChatsOnly()
    {
        $allBlocked = $this->allBlocked();
        if($this->isAdmin()){
            $ids = $this->getAccountIds();
            $chats1 = Chat::whereIn('from_user', $ids)->whereNotIn('to_user', $allBlocked)->orderBy('updated_at', 'desc')->select('to_user')->distinct()->get();
            $chats2 = Chat::whereIn('to_user', $ids)->whereNotIn('from_user', $allBlocked)->orderBy('updated_at', 'desc')->select('from_user')->distinct()->get();
            $chats1 = $chats1->pluck('to_user');
            $chats2 = $chats2->pluck('from_user');
            $chats = $chats1->merge($chats2);
        }else{
            $chats1 = Chat::where('from_user', $this->id)->whereNotIn('to_user', $allBlocked)->orderBy('updated_at', 'desc')->select('to_user')->distinct()->get();
            $chats2 = Chat::where('to_user', $this->id)->whereNotIn('from_user', $allBlocked)->orderBy('updated_at', 'desc')->select('from_user')->distinct()->get();
            $chats1 = $chats1->pluck('to_user');
            $chats2 = $chats2->pluck('from_user');
            $chats = $chats1->merge($chats2);
        }

        return $chats;
    }

    public function isAdmin()
    {
        $adminIp = $this->admin_ip;

        // return $this->role === 'admin' || $this->role === 'editor' || $adminIp === $_SERVER['REMOTE_ADDR'];
        return $this->role === 'admin' || $this->role === 'editor';
    }

    public function getAccounts()
    {
        return Cache::rememberForever('accounts.' . $this->id, function() {
            return WebAccount::where('admin_id', $this->id)->get();
        });
    }

    public function accountAdmins()
    {
        $acc = WebAccount::where('user_id', $this->id)->get();
        $admin_ids = array();
        foreach ($acc as $ac) {
            $admin_ids[] = $ac->admin_id;
        }
        $admin = User::whereIn('id', $admin_ids)->get();
        return $admin;
    }

    public function commonFriendsRefactored()
    {
        $auth_friends = Auth::user()->allFriendsRefactored();
        $selected_friends = $this->allFriendsRefactored();
        return $auth_friends->intersect($selected_friends);
    }

    public function commonFriends()
    {
        $auth_friends = Auth::user()->allFriends();
        $selected_friends = $this->allFriends();
        return $auth_friends->intersect($selected_friends);
    }

    public function age()
    {
        return date_diff(date_create($this->birthday), date_create('today'))->y;
    }

    public function notifications()
    {
        $notifications = Notification::where('for_user', $this->id)->whereNotIn('user_id', $this->allBlocked())->orderBy('created_at', 'desc')->get();
        return $notifications;
    }

    public function unreadNotifications()
    {
        $notifications = Notification::where('for_user', $this->id)->where('seen', 0)->whereNotIn('user_id', $this->allBlocked())->count();
        return $notifications;
    }

    public function package()
    {
        /*$user_pack = User_Pack::where('user_id', $this->id)->where('expiration_date', '>=', date('Y-m-d H:i:s'));
        if($user_pack->exists()){
            $user_pack = $user_pack->firstOrFail();
            $pack = Pack::where('id', $user_pack->pack_id)->firstOrFail();
            return $pack;
        }
        return false;*/

        // $user_pack = User_Pack::where('user_id', $this->id)->where('expiration_date', '>=', date('Y-m-d H:i:s'));
        $user_pack = User_Pack::where('user_id', $this->id);
        if ($user_pack->exists()) {
            $user_pack = $user_pack->firstOrFail();
            // #bx start
            if ($user_pack && strtotime($user_pack->expiration_date) < strtotime(date('Y-m-d'))) { //extend subscription date automatically
                $subscription = Order::where(['user_id' => Auth::user()->id, 'status' => 'Accepted'])->whereNotNull('subscription_id')->first();
                if ($subscription) {
                    $package = Pack::find($subscription->package_id);
                    if ($package) {
                        if ($package->type == "subscription-credits") { //if subscription-credits then add credits based on the expired days
                            $exp = ceil((strtotime(date('Y-m-d')) - strtotime($user_pack->expiration_date)) / (86400));
                            $user = User::find(auth()->user()->id);
                            $user->credits = $user->credits + ceil($exp / 4) * $package->credits;
                            $user->save();
                        }
                        $user_pack->expiration_date = date('Y-m-d H:i:s', strtotime("+" . $package->duration . " day"));
                        $user_pack->save();
                        return $package;
                    }
                }
            }
            // #bx end

            $pack = Pack::where('id', $user_pack->pack_id)->firstOrFail();
            return $pack;
        }
        return false;
    }

    public function paidPackage()
    {
        $user_pack = User_Pack::where('user_id', $this->id)->where('expiration_date', '>=', date('Y-m-d H:i:s'));
        if ($user_pack->exists()) {
            $user_pack = $user_pack->firstOrFail();
            $pack = Pack::where('id', $user_pack->pack_id)->where('name', '!=', 'Trial');
            if ($pack->exists()) {
                $pack = $pack->firstOrFail();
                return $pack;
            } else {
                return false;
            }
        }
        return false;
    }

    public function package_expire()
    {
        $user_pack = User_Pack::where('user_id', $this->id)->where('expiration_date', '>=', date('Y-m-d H:i:s'));
        if ($user_pack->exists()) {
            $user_pack = $user_pack->firstOrFail();
            $expiration = Carbon::createFromFormat('Y-m-d H:i:s', $user_pack->expiration_date);

            return $expiration ?: null;
        }

        return null;
    }

    public function package_expire_indays()
    {
        $expiration = $this->package_expire();
        if (!$expiration) {
            // No valid expiration date means no near-expiry warning should be triggered.
            return 9999;
        }

        return Carbon::now()->diffInDays($expiration);
    }

    public function hasPermission($field)
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->package()) {
            $pack = $this->package();
            if ($pack->{$field} == 'true') {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function hasDiscount()
    {
        $discount = Discount::where('user_id', $this->id)->where('ending_at', '>', Carbon::now());

        if ($discount->exists()) {
            return true;
        }

        return false;
    }

    public function getDiscount()
    {
        $discount = Discount::where('user_id', $this->id)->where('ending_at', '>', Carbon::now());

        if ($discount->exists()) {
            return $discount->firstOrFail();
        }

        return false;
    }

    public function getDiscounts()
    {
        $discount = Discount::where('user_id', $this->id)->where('ending_at', '>', Carbon::now());

        if ($discount->exists()) {
            return $discount->get();
        }

        return false;
    }

    public function getPackDiscounts()
    {
        $discount = Discount::where('user_id', $this->id)->where('ending_at', '>', Carbon::now());

        if ($discount->exists()) {
            $discounts = $discount->select('pack_id')->get();
            $packs = Pack::whereIn('id', $discounts)->get();
            return $packs;
        }

        return false;
    }

    public function getDiscountByPack($id)
    {
        $discount = Discount::where('user_id', $this->id)->where('pack_id', $id)->where('ending_at', '>', Carbon::now());

        if ($discount->exists()) {
            return $discount->firstOrFail();
        }

        return false;
    }

    public function adminReplyEmail()
    {
        if ($this->role == "admin" || $this->role == "editor") {
            return $this->hasOne('App\AdminReplyEmail', 'admin_id', 'id');
        }
    }

    public function aiProfilesCreated()
    {
        return $this->hasMany('App\AIProfile', 'created_by_admin_id');
    }

    public function conversations()
    {
        return $this->hasMany('App\Conversation', 'user_id');
    }
}
