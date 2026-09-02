<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Client extends Model
{
	protected $table = 'clients';
    protected $guarded = [];

    public function admin_name()
    {     
    	$admin = User::where('id', $this->admin_id)->where('role', '!=', 'user');
    	if($admin->exists()){
    		$admin = $admin->firstOrFail();
    		return $admin->name();
    	} 
        return false;
    }

    public function isRegistered()
    {     
    	$client = User::where('email', $this->email);
    	if($client->exists()){
    		return true;
    	} 
        return false;
    }

}
