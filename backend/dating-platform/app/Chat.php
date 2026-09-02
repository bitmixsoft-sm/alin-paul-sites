<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Chat extends Model
{
	protected $table = 'chats';
    protected $guarded = [];
    protected $casts = [
        'ai_paused_at' => 'datetime',
    ];

    public function lastMessage()
    {
    	$msg = Message::where('id', $this->last_msg)->firstOrFail();

    	return $msg;
    }

    public function otherUserRelationship(){
        return $this->belongsTo(User::class, 'to_user');
    }

    public function thisUserRelationship(){
        return $this->belongsTo(User::class, 'from_user');
    }

    public function otherUser()
    {
    	$user = Cache::remember('chatThisUserRelationship.' . $this->id, 5, function(){
            return $this->thisUserRelationship;
        });
    	if(Auth::user()->isAdmin()){
    		if(in_array($user->id, Auth::user()->getAccountIds())){
                $user = Cache::remember('chatOtherUserRelationshipAdmin.' . $this->id, 5, function(){
                    return $this->otherUserRelationship;
                });
    		}
    	}else{
    		if($user->id == Auth::id()){
    			$user = Cache::remember('chatOtherUserRelationship.' . $this->id, 5, function(){
                    return $this->otherUserRelationship;
                });
    		}
    	}

    	return $user;
    }

    public function thisUser()
    {
        $user = Cache::remember('chatThisUserRelationship.' . $this->id, 5, function(){
            return $this->thisUserRelationship;
        });

    	if(Auth::user()->isAdmin()){
    		if(!in_array($user->id, Auth::user()->getAccountIds())){
                $user = Cache::remember('inverseChatOtherUserRelationshipAdmin.' . $this->id, 5, function(){
                    return $this->otherUserRelationship;
                });
    		}
    	}else{
    		if($user->id != Auth::id()){
                $user = Cache::remember('inverseChatOtherUserRelationshipAdmin.' . $this->id, 5, function(){
                    return $this->otherUserRelationship;
                });
    		}
    	}

    	return $user;
    }

}
