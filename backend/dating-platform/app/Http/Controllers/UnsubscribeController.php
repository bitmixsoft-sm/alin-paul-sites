<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Unsubscribe;

class UnsubscribeController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $email
     * @return \Illuminate\Http\Response
     */
    public function index($email)
    {
        if(isset($email)){
            $check = Unsubscribe::where('email', $email);
            if(!$check->exists()){
                $user = new Unsubscribe;
                $user->email = $email;
                $user->save();
            }
        }
        return redirect()->back();
    }
}
