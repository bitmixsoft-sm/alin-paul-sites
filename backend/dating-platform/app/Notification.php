<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Post;

class Notification extends Model
{
    protected $guarded = [];

    public function getUser()
    {
    	$user = User::where('id', $this->user_id)->firstOrFail();
    	return $user;
    }
    public function getPost()
    {
    	$post = Post::where('id', $this->post_id)->firstOrFail();
    	return $post;
    }
}
