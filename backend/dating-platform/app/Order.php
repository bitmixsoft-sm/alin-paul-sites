<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Pack;

class Order extends Model
{
    protected $guarded = [];

    public function user()
    {
    	if($user = User::where('id', $this->user_id)->exists()){
	    	$user = User::where('id', $this->user_id)->firstOrFail();
	    	return $user;
    	}else{
	    	return false;
	    }
    }

    public function package()
    {
    	if($pack = Pack::where('id', $this->package_id)->exists()){
	    	$pack = Pack::where('id', $this->package_id)->firstOrFail();
	    	return $pack;
	    }else{
	    	return false;
	    }
    }

}
