<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Client;
use App\Pack;
use App\User_Pack;
use App\Order;
use App\Album;
use App\Block;
use App\Chat;
use App\Friend;
use App\FriendRequest;
use App\ImageGet;
use App\Like;
use App\Message;
use App\Notification;
use App\Post;
use App\WebAccount;
use App\WelcomeMessage;
use App\RouletteUser;
use App\Services\AI\ElevenLabsVoiceCatalog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;


class WebUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->search){
            $search = $request->search;
            $users = User::where('role', 'user')->where(function($q) use ($search) {
                            $q->where('firstname', 'like', '%'.$search.'%')
                    ->orWhere('lastname', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
                      })->orderBy('created_at', 'desc')
                    ->paginate(20);
        }elseif(isset($request->option) && $request->option == 'banned'){
                $users = User::where('role', 'user')->where('banned', 'yes')->orderBy('created_at', 'desc')->paginate(20);
        }else{
            if(Auth::user()->role == 'editor'){
                // $users= User::leftJoin('clients', 'users.email','=', 'clients.email')->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
                $users= User::where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
            } elseif(Auth::user()->role == 'admin'){
                $users = User::where('role', 'user')->orderBy('created_at', 'desc')->paginate(20);
            } else {
                die("You do not have permission to access this area!");
            }
        }

        $on_page = 'Utilizatori';
        return view('admin.users', compact('users', 'on_page'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'video' => 'max:50000', //5MB
        ]);
          if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

          $user = User::where('id', $request->input('user'))->firstOrFail();
        if($request->input('block') == 'block'){
            $user->banned = 'yes';
            $user->save();
        }

        if($request->hasFile('background_image')){
            $user->background_image = $request->file('background_image')->store('/images/users/background_images', 'public');
            $user->save();
        }

        if($request->input('block') == 'unblock'){

            $user->banned = 'no';
            $user->save();
        }
        if($request->input('update') == 'update'){
            if(Auth::user()->role == 'admin'){
                $user->role = $request->input('role');
                $user->gender = $request->input('gender');
                $user->discount = $request->input('discount');
                if($request->input('password'))
                    $user->password = Hash::make($request->input('password'));
            }

            if((Auth::user()->role == 'admin' || (Auth::user()->role == 'editor' && $user->gender=='female'))) {
                if($request->hasFile('video')) {
                    if(!\File::exists(public_path('videos')))
                        \File::makeDirectory(public_path('videos'), 0777);
                    $file = $request->file('video');
                    $extension = \File::extension($file->getClientOriginalName());
                    $filename = \Illuminate\Support\Str::random(32).".".$extension;
                    $path = public_path('videos');
                    $file->move($path, $filename);
                    $user->video = $filename;
                } elseif($request->has('video-delete')) {
                    \File::delete(public_path('videos/').$user->video);
                    $user->video = NULL;
                }
            }

            if($request->pack != '0' && $request->pack != 'no'){
                if(!$user->package() || $user->package()->id != $request->pack){
                    $pack = Pack::where('id', $request->pack)->firstOrFail();
                    $user->credits = $user->credits+$pack->credits;
                    if($pack->type != 'credits'){
                        $del_pack = User_Pack::where('user_id', $user->id)->exists();
                        if($del_pack){
                            $del_pack = User_Pack::where('user_id', $user->id);
                            $del_pack->delete();
                        }
                        $add_pack = new User_Pack;
                        $add_pack->user_id = $user->id;
                        $add_pack->pack_id = $pack->id;
                        $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
                        $add_pack->save();
                    }
                }
            }
            if($request->pack == 'no'){
                $pack = User_Pack::where('user_id', $user->id);
                $pack->delete();
            }
            $user->credits = $request->credits;

            if($user->gender == 'female' && $request->has('ai_enabled')){
                $user->ai_enabled = (bool) ((int) $request->input('ai_enabled'));
                $user->ai_system_prompt = trim((string) $request->input('ai_system_prompt', ''));
                $user->simli_face_id = trim((string) $request->input('simli_face_id', '')) ?: null;
                $user->simli_voice_id = trim((string) $request->input('simli_voice_id', '')) ?: null;
            }

            $user->save();

            if($request->input('welcome_message') && $user->gender == 'female'){
                $del_message = WelcomeMessage::where('user_id', $user->id);
                $del_message->delete();
                $welcome_message = new WelcomeMessage;
                $welcome_message->user_id = $user->id;
                $welcome_message->message = $request->input('welcome_message');
                $welcome_message->website = $_SERVER['SERVER_NAME'];
                $welcome_message->save();
            }
        }
        return redirect('/admin/users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, ElevenLabsVoiceCatalog $elevenLabsVoiceCatalog)
    {
        $user = User::where('username', $id)->firstOrFail();
        $packages = Pack::where('custom', '!=', 1)->orderBy('price', 'asc')->get();
        $on_page = "Profil";
        // Only needed for female profiles' "Simli Voice ID" field, but cheap enough (cached
        // by ElevenLabs on their side) to just always fetch rather than branch on gender here.
        $elevenLabsVoices = $user->gender === 'female' ? $elevenLabsVoiceCatalog->list() : [];
        return view('admin.user_profile', compact('user', 'on_page', 'packages', 'elevenLabsVoices'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if(Auth::user()->isAdmin()){

            $albums = Album::where('user_id', $id);
            $block = Block::where('user', $id)->orWhere('block', $id);
            $chats = Chat::where('from_user', $id)->orWhere('to_user', $id);
            $freinds = Friend::where('user', $id)->orWhere('user_friend', $id);
            $freind_requests = FriendRequest::where('user_from', $id)->orWhere('user_to', $id);
            $images = ImageGet::where('user_id', $id)->get();
            $likes = Like::where('user_id', $id);
            $messages = Message::where('from_user', $id)->orWhere('to_user', $id);
            $notifications = Notification::where('user_id', $id)->orWhere('for_user', $id);
            $posts = Post::where('user_id', $id);
            $user_pack = User_Pack::where('user_id', $id);
            $web_accounts = WebAccount::where('user_id', $id);
            $roulette_user = RouletteUser::where('user_id', $id);
            $user = User::where('id', $id);

            foreach ($images as $image) {
                Storage::delete('public/images/'.$image->name);
            }
            $images = ImageGet::where('user_id', $id);
            $albums->delete();
            $block ->delete();
            $chats->delete();
            $freinds->delete();
            $freind_requests->delete();
            $images->delete();
            $likes->delete();
            $messages->delete();
            $notifications->delete();
            $posts->delete();
            $user_pack->delete();
            $web_accounts->delete();
            $roulette_user->delete();
            $user->delete();

            return redirect('/admin/users');

        }else{
            return redirect('/profile');
        }
    }
}
