<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Faker\Factory as Faker;
use App\User_Pack;
use App\Pack;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Password;
use App\EmailTracking;
use App\Settings;
use App\Message;
use App\Events\NewMessage;
use Cookie;

class AutoRegisterController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  string  $req_token
     * @param  string  $email
     * @param  string  $target
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function index($req_token, $email, $target, $code=false)
    {
        $token = md5(env('AUTOREGISTER_TOKEN', '98eb7a12f25a609674cfe50a064a84c2').$email);


        if($req_token == $token){
            
            $user = User::where('email', $email);

            if($user->exists()){
                $user = $user->firstOrFail();
               Auth::loginUsingId($user->id, true);
               if($code){
                    $user_tracking = EmailTracking::where('tracking', $code)->firstOrFail();
                    $user_tracking->status = 'login';
                    $user_tracking->save();
                }
               if($target == 'friends'){
                    return redirect('/find-friends');
                }else{
                    return redirect('/profile/'. $target);
                } 
            }else{
                $faker = Faker::create();
                $em = explode('@', $email);
                $firstname = $faker->firstName('male');
                $lastname = $faker->lastName('male');

                $user = new User;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->email = $email;
                $pass = $faker->password;
                $user->password = Hash::make($pass);
                $user->gender = 'male';
                $user->country = get_user_country();
                $user->county = get_user_region();
                $user->city = get_user_city();
                $user->lang = get_user_browser_lang();
                $user->save();
                $user->username = strtolower ($firstname.$lastname.$user->id);
                $user->save();
                $info = array();
                $info['firstname'] = $user->firstname;
                $info['lastname'] = $user->lastname;
                $info['email'] = $user->email;
                $info['password'] = $pass; 
                Mail::to($user->email)->send(new Password($info));
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
                Cookie::queue(Cookie::forever('autoregister', 1));
                Auth::loginUsingId($user->id, true);
                if($code){
                    $user_tracking = EmailTracking::where('tracking', $code)->firstOrFail();
                    $user_tracking->status = 'register';
                    $user_tracking->save();
                }
                if($target == 'friends'){
                    return redirect('/find-friends');
                }else{
                    return redirect('/profile/'. $target);
                }        

            } 

        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fastregister(Request $request)
    {

        $email = $request->email;
        $user = User::where('email', $email);

            if(!$user->exists()){
                $faker = Faker::create();
                $em = explode('@', $email);
                $firstname = $faker->firstName('male');
                $lastname = $faker->lastName('male');

                $user = new User;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->email = $email;
                $pass = $faker->password;
                $user->password = Hash::make($pass);
                $user->gender = 'male';
                $user->country = get_user_country();
                $user->county = get_user_region();
                $user->city = get_user_city();
                $user->lang = get_user_browser_lang();
                $user->save();
                $user->username = strtolower ($firstname.$lastname.$user->id);
                //$user->email_verified_at = rand();
                $user->save();
                //$info = array();
                //$info['firstname'] = $user->firstname;
                //$info['lastname'] = $user->lastname;
                //$info['email'] = $user->email;
                //$info['password'] = $pass;
                //$info['verification'] = $user->email_verified_at;
                //Mail::to($user->email)->send(new Password($info));
                $info = array();
                $info['firstname'] = $user->firstname;
                $info['lastname'] = $user->lastname;
                $info['email'] = $user->email;
                $info['password'] = $pass; 
                Mail::to($user->email)->send(new Password($info));
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
                Cookie::queue(Cookie::forever('autoregister', 1));
                Auth::loginUsingId($user->id, true);
                return redirect('/find-friends');
            } 
            return redirect('/');

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fakeregister($id, Request $request)
    {

                $messages = Settings::where('id', 29)->orWhere('id', 30)->orWhere('id', 31)->orderBy('id', 'asc')->get();

                $faker = Faker::create();
                $firstname = $faker->firstName('male');
                $lastname = $faker->lastName('male');
                $email = $firstname.$lastname.time()."@".$_SERVER['SERVER_NAME'];
                $user = new User;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->email = $email;
                $pass = $faker->password;
                $user->password = Hash::make($pass);
                $user->gender = 'male';
                $user->country = get_user_country();
                $user->county = get_user_region();
                $user->city = get_user_city();
                $user->lang = get_user_browser_lang();
                $user->save();
                $user->username = strtolower ($firstname.$lastname.$user->id);
                $user->save();
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
                Cookie::queue(Cookie::forever('autoregister_fake', 1));
                Auth::loginUsingId($user->id, true);
                if($id != 'random'){
                    $user_from = User::where('id', $id)->firstOrFail();
                }else{
                    $user_from = User::where('gender', 'female')->inRandomOrder()->firstOrFail();
                }
                $from = $user_from->id;
                $to = $user->id;
                foreach ($messages as $message) {
                    $bot_msg = new Message;
                      $bot_msg->from_user = $from;
                      $bot_msg->to_user = $to;
                      $bot_msg->chatbot = 'true';
                      $bot_msg->message = l($message->value);
                      $bot_msg->save();
                      $bot_chat = $user_from->chat($to);
                      $bot_chat->last_msg = $bot_msg->id;
                      $bot_chat->save();

                      $bot_message_response[] = Message::where('id', $bot_msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail();

                      $bot_response = ['user_name' => $user_from->name(), 'user_img' => $user_from->profile_image(), 'auth_img' => "", 'msg' => $bot_message_response, 'to_user' => $to, 'chat_bot' => 'true'];
                      event(new NewMessage($bot_response));
                }
                return redirect('/find-friends?open_chat='.$from);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  string  $email
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function verify($email, $token)
    {

        $user = User::where('email', $email)->firstOrFail();

        if($token == $user->email_verified_at){
            $user->email_verified_at = null;
            $user->save();
            Auth::loginUsingId($user->id, true);
        }

        return redirect('/find-friends');

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request)
    {
        $user = User::where('id', Auth::id())->firstOrFail();

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->password = Hash::make($request->password);
        $user->username = strtolower ($user->firstname.$user->lastname.$user->id);
        $user->save();
        $info = array();
        $info['firstname'] = $user->firstname;
        $info['lastname'] = $user->lastname;
        $info['email'] = $user->email;
        $info['password'] = $request->password; 
        Mail::to($user->email)->send(new Password($info));
        Cookie::queue(Cookie::forget('autoregister'));
        return back();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function complete_fake(Request $request)
    {
        $check_user = User::where('email', $request->email);
        if(!$check_user->exists()){
            $user = User::where('id', Auth::id())->firstOrFail();

            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->username = strtolower ($user->firstname.$user->lastname.$user->id);
            $user->save();
            $info = array();
            $info['firstname'] = $user->firstname;
            $info['lastname'] = $user->lastname;
            $info['email'] = $user->email;
            $info['password'] = $request->password; 
            Mail::to($user->email)->send(new Password($info));
            Cookie::queue(Cookie::forget('autoregister_fake'));
            return back();
        }
        return back()->with('error', ['email', 'Already exists!']);
    }
}
