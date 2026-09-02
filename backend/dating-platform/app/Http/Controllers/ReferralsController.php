<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Referral;
use App\User;
use App\Pack;
use App\User_Pack;
use Illuminate\Support\Facades\Mail;
use App\Mail\Referrals;


class ReferralsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {   
        $check1 = Referral::where('referral', $request->email)->count();
        $check2 = User::where('email', $request->email)->count();
        if($check1 == 0 && $check2 == 0){
            $referral = new Referral;
            $referral->user = Auth::user()->email;
            $referral->referral = $request->email;
            
            $user = Auth::user();
            $data = array();
            $data['user_id'] = $user->id;
            $data['cover'] = url('/storage/images').'/'.$user->cover_image();
            $data['profile'] = url('/storage/images').'/'.$user->profile_image();
            $lang = $user->lang;
            $data['lang'] = $lang;
            $data['name'] = $user->name();
            $data['age'] = $user->age().' '.l('years', $lang);
            $data['header'] = l('You have been invited to join '.env('APP_NAME').'!', $lang);
            $data['text'] = $user->firstname.' '.l('has sent you an invitation!', $lang);
            Mail::to($request->email)->send(new Referrals($data));
            $referral->save();
            return redirect('/packages?referrals=yes');
        }
        return redirect('/packages?referrals=no');
    }

    /**
     * Store a newly created resource in storage.
     * @param  string  $username
     * @param  string  $token
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($username, $token, Request $request)
    {
        $user = User::where('username', $username);
        if($user->exists()){
            $user = $user->firstOrFail();
            if(md5($user->id.$user->email) == $token){
                Auth::logout();
                session(['referral' => $user->email]);
            }
        }
        return redirect('/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $packages = Pack::Where('price', '!=', '0')->where('custom', '!=', 1)->whereIn('type', ['subscription', 'subscription-credits'])->where('name', '!=', 'Trial')->orderBy('price', 'asc')->get();
        $number = 5*($packages->search(function($i) use($id) {
            return $i->id == $id;
        })+1);
        $can_claim = Referral::where('user', Auth::user()->email)->where('status', 1)->where('claimed', 0)->get();
        if($can_claim->count() >= $number){
            $user = User::where('id', Auth::id())->firstOrFail();
            $pack = Pack::where('id', $id)->firstOrFail();
            $user->credits = $user->credits+$pack->credits;
            $user->save();
            if($pack->type != 'credits'){
                if($user->package() && $user->package()->id == $pack->id){
                    $user_exp = $user->package_expire();
                    $current_pack = User_Pack::where('user_id', $user->id)->firstOrFail();
                    $current_pack->expiration_date = date('Y-m-d H:i:s',strtotime($user_exp." +".$pack->duration." day"));
                    $current_pack->save();
                }else{
                    $del_pack = User_Pack::where('user_id', $user->id);
                    $del_pack->delete();
                    $add_pack = new User_Pack;
                    $add_pack->user_id = $user->id;
                    $add_pack->pack_id = $pack->id;
                    $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
                    $add_pack->save();
                }
            }
            foreach ($can_claim as $ref) {
                $ref->claimed = 1;
                $ref->save();
            }
        }
        return redirect('/find-friends');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
