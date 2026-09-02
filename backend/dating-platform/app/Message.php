<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Message extends Model
{
	protected $table = 'messages';
    protected $guarded = [];

    public function userFrom()
    {
    	$user = User::where('id', $this->from_user)->firstOrFail();
    	return $user;
    }
    public function userTo()
    {
    	$user = User::where('id', $this->to_user)->firstOrFail();
    	return $user;
    }

}
