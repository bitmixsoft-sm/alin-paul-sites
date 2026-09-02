<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RouletteValue extends Model
{
	protected $table = 'roulette_values';
    protected $guarded = [];

    public function package(){

    	$pack = Pack::where('id', $this->pack_id);

    	if($pack->exists()){

    		$pack = $pack->firstOrFail();

    		return $pack;
    	}

    	return false;

    }
}
