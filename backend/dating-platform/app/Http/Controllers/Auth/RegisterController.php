<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\User_Pack;
use App\Pack;
use App\Client;
use App\Referral;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\Password;
use Session;
use Cookie;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/profile';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'max:255'],
            'terms' => ['required']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $clients = Client::select('email')->get();
        $clients = $clients->pluck('email')->toArray();
        $user = new User;
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->email = $data['email'];
        //$user->banned = 'yes';
        if(in_array($user->email, $clients)){
            $user->banned = 'no';
        }
        if(Session::has('referral')){
            $user->banned = 'no';
        }
        if(Cookie::get('referral') != 'yes'){
            $user->banned = 'no';
        }
        $pass = $data['password'];
        $user->password = Hash::make($pass);
        $user->gender = 'male';
        $user->country = get_user_country();
        $user->county = get_user_region();
        $user->city = get_user_city();
        $user->lang = get_user_browser_lang();
        $user->save();
        $user->username = strtolower ($data['firstname'].$data['lastname'].$user->id);
        $user->save();
        $info = array();
        $info['firstname'] = $user->firstname;
        $info['lastname'] = $user->lastname;
        $info['email'] = $user->email;
        $info['password'] = $pass; 
        try {
          Mail::to($user->email)->send(new Password($info));
                } catch (\Throwable $e) {
          // handle $e
        }
        $pack = Pack::where('name', 'Trial')->exists();
        if($pack){
            $pack = Pack::where('name', 'Trial')->firstOrFail();
            $add_pack = new User_Pack;
            $add_pack->user_id = $user->id;
            $add_pack->pack_id = $pack->id;
            $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
            $add_pack->save();
            $user->credits = $user->credits+$pack->credits;
            $user->save();
        }
        if(Session::has('referral') && Cookie::get('referral') != 'yes'){
            $referral = Session::get('referral');
            $set_status = Referral::where('user', $referral)->where('referral', $user->email);
            if($set_status->exists()){
                $set_status = $set_status->firstOrFail();
                $set_status->status = 1;
                $set_status->save();
                Session::forget('referral');
                Cookie::queue(Cookie::forever('referral', 'yes'));
            }else{
                $new_ref = new Referral;
                $new_ref->user = $referral;
                $new_ref->referral = $user->email;
                $new_ref->status = 1;
                $new_ref->save();
                Session::forget('referral');
                Cookie::queue(Cookie::forever('referral', 'yes'));
            }
        }
        // Read once by the TikTok Pixel snippet in layouts/layout.blade.php on the very next
        // page load (the post-registration redirect target) to fire a CompleteRegistration
        // event - flash data so it's automatically gone after that one read.
        session()->flash('tiktok_track_registration', true);
        return $user;
    }
}
