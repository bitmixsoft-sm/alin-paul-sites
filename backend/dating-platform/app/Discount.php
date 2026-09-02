<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Pack;

class Discount extends Model
{
	protected $table = 'discounts';
    protected $guarded = [];

    public function user(){

    	$user = User::where('id', $this->user_id);

    	if($user->exists()){
    		$user = $user->firstOrFail();

    		return $user;
    	}

    	return false;

    }

    public function package(){

    	$pack = Pack::where('id', $this->pack_id);

    	if($pack->exists()){
    		$pack = $pack->firstOrFail();

    		return $pack;
    	}

    	return false;

    }

}
