<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class WebAccount extends Model
{
	protected $table = 'web_accounts';
    protected $guarded = [];

    public function getUserRelationship(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUser()
    {
    	// $user = User::where('id', $this->user_id)->firstOrFail();
    	$user = User::where('id', $this->user_id)->first();
    	return $user;
    }
}
