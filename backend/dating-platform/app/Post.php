<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Album;
use App\User;
use App\Video;
use App\ImageGet;
use Illuminate\Support\Facades\Auth;
use App\Like;

class Post extends Model
{
    protected $guarded = [];

    public function getContent()
    {
    	if($this->type == 'album'){
    		$album = Album::where('user_id', $this->user_id)->where('id', $this->item_id)->firstOrFail();
    		return $album;
    	}
    	if($this->type == 'image'){
    		$image = ImageGet::where('user_id', $this->user_id)->where('id', $this->item_id)->firstOrFail();
    		return $image;
    	}
        if($this->type == 'video'){
            $video = Video::where('user_id', $this->user_id)->where('id', $this->item_id)->firstOrFail();
            return $video;
        }
    }
    public function getUser()
    {
    	$user = User::where('id', $this->user_id)->firstOrFail();
    	return $user;
    }
    public function liked()
    {
        $like = Like::where('user_id', Auth::id())->where('post_id', $this->id)->exists();
        if($like){
            return true;
        }

        return false;
    }
}
