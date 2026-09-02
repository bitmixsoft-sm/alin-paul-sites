<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\ImageGet;
use App\Album;
use App\Post;
use App\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\Action;

class AlbumController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        if(Auth::user()->hasPermission('albums')){
        $title = "Albums";
        $user = User::where('username', $id)->firstOrFail();
        $albums = Album::where('user_id', $user->id)->get();
        $page = 'albums';
        return view('profile.albums',compact('title', 'user', 'albums', 'page'));
        }else{
            return redirect('/packages');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function photo_page($id)
    {
        if(Auth::user()->hasPermission('images')){
        $title = "Photos";
        $user = User::where('username', $id)->firstOrFail();
        $images = ImageGet::where('user_id', $user->id)->where('privacy', null)->where('role', '!=', 'profile')->orderBy('created_at', 'desc')->take(20)->get();
        $page = 'photos';
        return view('profile.albums',compact('title', 'user', 'images', 'page'));
        }else{
            return redirect('/packages');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $images = $request->images;
        $descriptions = $request->descriptions;
        $user_id = Auth::id();
        $album = new Album;
        $album->user_id = $user_id;
        $album->name = $request->name;        
        $album->privacy = $request->privacy;
        $album->save();
        $album->slug = str_slug($request->name." ".$album->id, "-");
        $album->save();
        if($descriptions != ''){
        foreach ($descriptions as $desc) {
            $img = ImageGet::where('id', $desc[0])->where('user_id', $user_id)->firstOrFail();
            $img->description = $desc[1];
            $img->privacy = $request->privacy; 
            $img->save();
        }
        }
        
        if(is_array($images)){
            foreach($images as $image){
                if($image != ""){
                    $img = ImageGet::where('id', $image)->where('user_id', $user_id)->firstOrFail();
                    $img->privacy = $request->privacy; 
                    $img->save();
                    $album->images()->attach($image);
                }
            }
        }else{
            if($images != ""){
                $img = ImageGet::where('id', $images)->where('user_id', $user_id)->firstOrFail();
                $img->privacy = $request->privacy; 
                $img->save();
                $album->images()->attach($images);
            }
        }
        $post = new Post;
        $post->user_id = $user_id;
        $post->item_id = $album->id;
        $post->type = 'album';
        $post->save();

        $friends = User::whereIn('id', function($q) use ($user_id){
                        $q->select('user')->from('friends')->where('user_friend', $user_id)->whereNotIn('user', Auth::user()->allBlocked());
                   })->orWhereIn('id', function($q1) use ($user_id){
                        $q1->select('user_friend')->from('friends')->where('user', $user_id)->whereNotIn('user_friend', Auth::user()->allBlocked());
                   })->select('id')->get();
            foreach($friends as $friend){
                $notification = new Notification;
                $notification->user_id = $post->user_id;
                $notification->for_user = $friend->id;
                $notification->post_id = $post->id;
                $notification->type = 'album';
                $notification->save();
                $user = Auth::user();
                $email = User::where('id', $friend->id)->firstOrFail();
                $data = array();
                $data['user_id'] = $email->id;
                $data['cover'] = url('/storage/images').'/'.$user->cover_image();
                $data['profile'] = url('/storage/images').'/'.$user->profile_image();
                $data['name'] = $user->name();
                if($email->lang != ''){
                    $lang = $email->lang;
                    $data['lang'] = $lang;
                }else{
                    $lang = null;
                    $data['lang'] = $lang;
                }
                $data['age'] = $user->age().' '.l('years', $lang);
                $data['header'] = l('Your friend uploaded a new album!', $lang);
                $data['text'] = $user->firstname.l(' uploaded a new status! Start chatting!', $lang);
                $data['post_id'] = $post->id; 
                Mail::to($email->email)->send(new Action($data));
            }

        $response = "/profile/".Auth::user()->username."/albums";
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $image = $request->image;
        $user_id = Auth::id();

        $image_ext = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
        $image_name = rand().'_'.rand().'_'.$user_id.'.'.$image_ext;

        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);
        Storage::put('public/images/'.$image_name, $image);

        $img = new ImageGet;

        $img->user_id = $user_id;
        $img->name = $image_name;
        $img->save();
        $route = '/profile/'.Auth::user()->username.'/photos';
        if(!$request->album){
            $post = new Post;
            $post->user_id = $user_id;
            $post->item_id = $img->id;
            $post->type = 'image';
            $post->save();

            $friends = User::whereIn('id', function($q) use ($user_id){
                        $q->select('user')->from('friends')->where('user_friend', $user_id)->whereNotIn('user', Auth::user()->allBlocked());
                   })->orWhereIn('id', function($q1) use ($user_id){
                        $q1->select('user_friend')->from('friends')->where('user', $user_id)->whereNotIn('user_friend', Auth::user()->allBlocked());
                   })->select('id')->get();
            foreach($friends as $friend){
                $notification = new Notification;
                $notification->user_id = $post->user_id;
                $notification->for_user = $friend->id;
                $notification->post_id = $post->id;
                $notification->type = 'image';
                $notification->save();
                $user = Auth::user();
                $email = User::where('id', $friend->id)->firstOrFail();
                $data = array();
                $data['user_id'] = $email->id;
                $data['cover'] = url('/storage/images').'/'.$user->cover_image();
                $data['profile'] = url('/storage/images').'/'.$user->profile_image();
                $data['name'] = $user->name();
                if($email->lang != ''){
                    $lang = $email->lang;
                    $data['lang'] = $lang;
                }else{
                    $lang = null;
                    $data['lang'] = $lang;
                }
                $data['age'] = $user->age().' '.l('years', $lang);
                $data['header'] = l('Your friend uploaded a new photo!', $lang);
                $data['text'] = $user->firstname.l(' uploaded a new photo! Start chatting!', $lang);
                $data['post_id'] = $post->id; 
                Mail::to($email->email)->send(new Action($data));
            }
        }
        $response = (object) ['name' => $image_name, 'id' => $img->id, 'route' => $route];

        return response()->json($response);      
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_photo(Request $request)
    {
        $images = $request->images;
        $user_id = Auth::id();
        if(is_array($images)){
            foreach($images as $image){
                if($image != ""){
                    $img = ImageGet::where('user_id', $user_id)->where('id', $image)->firstOrFail();
                    $image_name = $img->name;
                    $img->delete();
                    Storage::delete('public/images/'.$image_name);
                }
            }
        }else{
            $img = ImageGet::where('user_id', $user_id)->where('id', $images)->firstOrFail();
                $image_name = $img->name;
            $albums = Album::where('user_id', $user_id)->whereHas('images', function($q) use ($images){
                                $q->where('images.id', $images);
                            })->get();    
            if($albums->count() != 0){
                $deleted = false;
                foreach($albums as $album){
                    if($album->images->count() == 1){
                        $post = Post::where('user_id', Auth::id())->where('item_id', $album->id)->where('type', 'album')->exists();
                        if($post){
                            $post = Post::where('user_id', Auth::id())->where('item_id', $album->id)->where('type', 'album')->firstOrFail();
                        
                        $notifications = Notification::where('user_id', Auth::id())->where('post_id', $post->id);
                        $notifications2 = Notification::where('for_user', Auth::id())->where('post_id', $post->id);
                        $notifications->delete();
                        $notifications2->delete();
                        $post->delete();
                        }
                        $album->delete();
                        $deleted = true;
                    }
                }
            }
            if(Post::where('user_id', Auth::id())->where('item_id', $img->id)->where('type', 'image')->exists()){
                $post = Post::where('user_id', Auth::id())->where('item_id', $img->id)->where('type', 'image')->firstOrFail();
            $notifications = Notification::where('user_id', Auth::id())->where('post_id', $post->id);
            $notifications2 = Notification::where('for_user', Auth::id())->where('post_id', $post->id);
            $notifications->delete();
            $notifications2->delete();
            $post->delete();
            }
                $img->delete();
                Storage::delete('public/images/'.$image_name);   
                $deleted = true;
            $route = '/profile/'.Auth::user()->username.'/photos';
            return response()->json(['image' => $images, 'deleted' => $deleted, 'route' => $route]);
        }
        $response = $images;
        if($request->album){
            $route = '/profile/'.Auth::user()->username.'/albums';
            return response()->json(['image' => $images, 'deleted' => $deleted, 'route' => $route]);  
        }
        return response()->json($response);      
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $album_id = $request->album;

        $album = Album::where('id', $album_id)->firstOrFail();

        $album->views = $album->views+1;

        $album->save(); 

        $user = User::where('id', $album->user_id)->firstOrFail();

        $i = 0;
        $tpl_slide = '';
        $tpl_pag = '';
        $show_image = array();
        foreach ($album->images as $image) {
                            $tpl_slide .= '<div class="swiper-slide" data-id="'.$image->id.'"><div class="photo-item" data-swiper-parallax="-300" data-swiper-parallax-duration="500">';
                            $tpl_pag .= '<a href="#" class="slides-item" data-desc="'.$image->id.'">';
                            $tpl_slide .= '<img src="/storage/images/'.$image->name.'" alt="photo">';
                            $tpl_slide .= '<div class="overlay"></div><div class="content"><a href="#" class="h6 title">'.$album->name.'</a></div></div></div>';

                            $tpl_pag .= '<img src="/storage/images/'.$image->name.'" alt="slide">';
                            $tpl_pag .= '<div class="overlay overlay-dark"></div></a>';

                            //if(i == data.images.length-1){
                            //    $("#album-slides-pag").append("<svg class='btn-next olymp-popup-right-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-right-arrow'></use></svg><svg class='btn-prev olymp-popup-left-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-left-arrow'></use></svg>");  
                            //}
                            $show_image[$image->id] = [$image->description, $image->created_at->format('d/m/Y H:i')];
                            $i++;
                        }
        if($album->privacy != $request->pass){
            $response = false;
            return response()->json($response);    
        }
        $response = ["user_name" => $user->name(), "profile_image" => $user->profile_image(), "user_username" => $user->username, "user_id" => $user->id,"images" => $album->images, 'tpl_slides' => $tpl_slide, 'tpl_pags' => $tpl_pag, 'show_image' => $show_image];

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_album(Request $request)
    {
        $album = Album::where('user_id', Auth::id())->where('id', $request->id)->firstOrFail();
        $post = Post::where('user_id', Auth::id())->where('item_id', $album->id)->where('type', 'album')->exists();
                        if($post){
                            $post = Post::where('user_id', Auth::id())->where('item_id', $album->id)->where('type', 'album')->firstOrFail();
                            $notifications = Notification::where('user_id', Auth::id())->where('post_id', $post->id);
                            $notifications->delete();
                            $post->delete();
                        }
        $album->delete();

        return response()->json('/profile/'.Auth::user()->username.'/albums');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $album_id = $request->album;
        $photos = $request->photos;

        $album = Album::where('user_id', Auth::id())->where('id', $album_id)->firstOrFail();

        foreach($photos as $photo){
            if (!$album->images->contains($photo)) {
                $album->images()->attach($photo);
            }
        }

        $album->save();
        return response()->json('/profile/'.Auth::user()->username.'/albums');
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
