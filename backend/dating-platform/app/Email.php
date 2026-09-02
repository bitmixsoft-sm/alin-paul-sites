<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\EmailTracking;

class Email extends Model
{
    protected $guarded = [];
    protected $connection= 'chats';


    public function receivers(){

    	$receivers = EmailTracking::where('email_id', $this->id)->get();

    	return $receivers;

    }

    public function seen(){

        $check = EmailTracking::where('email_id', $this->id)->where('seen', 0)->where('status', '!=', 'sent')->get();
        foreach ($check as $email) {
            $email->seen = 1;
            $email->save();
        }
    	$receivers = EmailTracking::where('email_id', $this->id)->where('seen', 1)->get();

    	return $receivers;

    }

}
