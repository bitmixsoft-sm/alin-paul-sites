<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Post;
use App\Like;
use App\Video;
use App\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\Action;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(Auth::user()->hasPermission('newsfeed')){
        $title= 'Newsfeed';
        if(Auth::user()->isAdmin()){
            $post = '';
            $posts = Post::orderBy('created_at', 'desc')->take(30)->get();
            $suggestions = User::whereNotIn('id', Auth::user()->allBlocked())->where('gender', 'male')->inRandomOrder()->take(5)->get();
        }else{
            $friends = User::whereIn('id', function($q){
                            $q->select('user')->from('friends')->where('user_friend', Auth::id())->whereNotIn('user', Auth::user()->allBlocked());
                       })->orWhereIn('id', function($q1){
                            $q1->select('user_friend')->from('friends')->where('user', Auth::id())->whereNotIn('user_friend', Auth::user()->allBlocked());
                       })->select('id')->get();
            $friends_post = User::where('gender', 'female')->select('id')->get();
            $post = '';
            $posts = Post::where('user_id', Auth::id())->orWhereIn('user_id', $friends_post)->orderBy('created_at', 'desc')->take(30)->get();
            $suggestions = User::whereNotIn('id', $friends)->whereNotIn('id', Auth::user()->allBlocked())->where('gender', 'female')->inRandomOrder()->take(5)->get();
        }
        
        if($request->post_id){           
            if(!Post::where('id', $request->post_id)->exists()){
                return redirect('/newsfeed');
            }
            $post = Post::where('id', $request->post_id)->firstOrFail();
            $notifications = Notification::where('for_user', Auth::id())->where('post_id', $post->id)->update(['seen' => 1]);
        }
        return view('newsfeed', compact('title', 'posts', 'suggestions', 'post'));
        }else{
            return redirect('/packages');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        if($request->input('type') == 'text' && $request->input('text') != ''){
            $post = new Post;
            $post->user_id =  Auth::id();
            $post->type = 'status';
            $post->description = $request->input('text');
            $post->save();
            $friends = User::whereIn('id', function($q){
                        $q->select('user')->from('friends')->where('user_friend', Auth::id())->whereNotIn('user', Auth::user()->allBlocked());
                   })->orWhereIn('id', function($q1){
                        $q1->select('user_friend')->from('friends')->where('user', Auth::id())->whereNotIn('user_friend', Auth::user()->allBlocked());
                   })->select('id')->get();
            foreach($friends as $friend){
                $notification = new Notification;
                $notification->user_id = $post->user_id;
                $notification->for_user = $friend->id;
                $notification->post_id = $post->id;
                $notification->type = 'status';
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
                $data['header'] = l('Your friend posted a new status!', $lang);
                $data['text'] = $user->firstname.' '.l('posted a new status! Start chatting!', $lang);
                $data['post_id'] = $post->id; 
                Mail::to($email->email)->send(new Action($data));
            }

        }
        if($request->input('type') == 'video' && $request->hasFile('video')){
            $video = new Video;
            $video->user_id = Auth::id();
            $file = $request->file('video');
            $video_ext = $file->getClientOriginalExtension();
            $filename = rand().'_'.rand().'_'.Auth::id().'.'.$video_ext;
            $video->name = $filename;
            if($request->input('text') != null){
                $video->description = $request->input('text');
            }else{
                $video->description = '';
            }
            $file->move(storage_path('app/public/videos/'), $filename);
            $video->save();
            $post = new Post;
            $post->user_id =  Auth::id();
            $post->type = 'video';
            $post->item_id = $video->id;
            $post->description = $video->description;
            $post->save();
            $friends = User::whereIn('id', function($q){
                        $q->select('user')->from('friends')->where('user_friend', Auth::id())->whereNotIn('user', Auth::user()->allBlocked());
                   })->orWhereIn('id', function($q1){
                        $q1->select('user_friend')->from('friends')->where('user', Auth::id())->whereNotIn('user_friend', Auth::user()->allBlocked());
                   })->select('id')->get();
            foreach($friends as $friend){
                $notification = new Notification;
                $notification->user_id = $post->user_id;
                $notification->for_user = $friend->id;
                $notification->post_id = $post->id;
                $notification->type = 'video';
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
                $data['header'] = l('Your friend posted a new video!', $lang);
                $data['text'] = $user->firstname.' '.l('posted a new video! Start chatting!', $lang);
                $data['post_id'] = $post->id; 
                Mail::to($email->email)->send(new Action($data));
            }

        }
        return redirect('/newsfeed');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function like(Request $request)
    {   
        if(Auth::user()->hasPermission('likes')){
        $ex = Like::where('user_id', Auth::id())->where('post_id', $request->post)->exists();
        if(!$ex){  
            $like = new Like;
            $like->user_id = Auth::id();
            $like->post_id = $request->post;
            $like->save();
            $post = Post::where('id', $request->post)->firstOrFail();
            $post->likes = $post->likes+1;
            $post->save();
            $status = 'liked';
            if($post->user_id != $like->user_id){
                $notification = new Notification;
                $notification->user_id = $like->user_id;
                $notification->for_user = $post->user_id;
                $notification->post_id = $post->id;
                $notification->type = 'like';
                $notification->save();
                $user = Auth::user();
                $email = User::where('id', $post->user_id)->firstOrFail();
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
                $data['header'] = l('You got a new Like!', $lang);
                $data['text'] = $user->firstname." ".l('just liked your post! Start chatting!', $lang);
                $data['post_id'] = $post->id; 
                Mail::to($email->email)->send(new Action($data));
            }

        }else{
            $like = Like::where('user_id', Auth::id())->where('post_id', $request->post)->delete();            
            $post = Post::where('id', $request->post)->firstOrFail();
            $post->likes = $post->likes-1;
            $post->save();
            $notification = Notification::where('user_id', $post->user_id)->where('post_id', $request->post)->where('type', 'like')->delete();
            $status = 'unliked';
        }
        $response = ['post_id' => $post->id, 'post_likes' => $post->likes, 'status' => $status];
        return response()->json($response);
        }else{
            $response = ['status' => 'packages'];
            return response()->json($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $friends = User::where('gender', 'female')->select('id')->get();
        if($request->from == 'all'){
            if(Auth::user()->isAdmin()){
                $posts = Post::orderBy('created_at', 'desc')->skip($request->posts)->take(30)->get();
            }else{

            $posts = Post::where('user_id', Auth::id())->orWhereIn('user_id', $friends)->orderBy('created_at', 'desc')->skip($request->posts)->take(30)->get();
        }
        }else{
            $posts = Post::where('user_id', $request->from)->orderBy('created_at', 'desc')->skip($request->posts)->take(30)->get();    
        }

        
        $tpl = '';
        foreach($posts as $post){
                if($post->type == 'album'){
                $tpl .= '<div class="ui-block">

                
                <!-- Post -->
                
                <article class="hentry post" data-post="'.$post->id.'">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/'.$post->getUser()->profile_image().'" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/'.$post->getUser()->username.'">'.$post->getUser()->name().'</a> uploaded '.$post->getContent()->images->count().' <a href="#" onclick="get_album(this);"'; if($post->getcontent()->privacy != ''){ $tpl .='data-protect="true"';} $tpl .='data-album="'.$post->getContent()->id.'">new photos</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    '.$post->getContent()->created_at->format("d/m/y H:i").'
                                </time>
                            </div>
                        </div>';
                if($post->user_id == Auth::id() || Auth::user()->isAdmin()){
                        $tpl .='<div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                            
                                <li>
                                    <a href="/delete-post/'.$post->id.'">Delete Post</a>
                                </li>
                            </ul>
                        </div>';}
                
                    $tpl .='</div>
                
                    <p>'.$post->getContent()->name.'</p>';

                    if($post->getcontent()->privacy == ''){
                        $count = $post->getContent()->images->count()-5;

                    $tpl .='<div class="post-block-photo">';
                        foreach($post->getContent()->images->take(5) as $image){
                            $tpl .='
                        <a href="/storage/images/'.$image->name.'" class="col '; if($post->getContent()->images->count() < 5){ $tpl .='half-width '; }else{ $tpl .='col-3-width'; }$tpl .='" onclick="get_album(this);" data-album="'.$post->getContent()->id.'">
                            <div class="post-photo-cont">
                                <img src="/storage/images/'.$image->name.'" alt="photo">
                            </div>
                        </a>';
                        }
                        if($post->getContent()->images->count() > 6){
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        $tpl .='
                        <a href="/storage/images/'.$image->name.'" onclick="get_album(this);" data-album="'.$post->getContent()->id.'" class="more-photos col-3-width">
                            <div class="post-photo-cont">
                                <img src="/storage/images/'.$image->name.'" alt="photo">
                            </div>
                            <span class="h2">+'.$count.'</span>
                        </a>';
                        }else{
                            if($post->getContent()->images->count() > 5){
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        $tpl .='
                            <a href="/storage/images/'.$image->name.'" class="col '; if($post->getContent()->images->count() < 5){ $tpl .='half-width '; }else{ $tpl .='col-3-width'; }$tpl .='" onclick="get_album(this);" data-album="'.$post->getContent()->id.'">
                            <div class="post-photo-cont">
                                <img src="/storage/images/'.$image->name.'" alt="photo">
                            </div>
                        </a>';
                    }
                        }
                    $tpl .='</div>';
                    }else{
                        $tpl .='
                        <div class="post-thumb">
                            <a href="#" onclick="get_album(this);" data-protect="true" data-album="'.$post->getContent()->id.'">
                                <img class="feed-protected" src="/img/lock.png" alt="photo">
                            </a>
                        </div>';                        
                    }
                
                    $tpl .='<div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post('.$post->id.');" class="likes post-add-icon inline-items ';
                                if($post->liked()){
                                    $tpl .= 'active';
                                }
                                $tpl.='">
                            <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>'.$post->likes.'</span>
                        </a>            
                
                    </div>
                
                    <div class="control-block-button post-control-button">';
                        if($post->user_id != Auth::id()){
                            $tpl .='
                        <a href="#" data-id="'.$post->getUser()->id.'" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>';
                        }
                    $tpl .='</div>
                
                </article>
                
                <!-- ... end Post -->
            </div>';
            }
            if($post->type == 'image'){
                $tpl .='<div class="ui-block">

                
                <!-- Post -->
                
                <article class="hentry post has-post-thumbnail" data-post="'.$post->id.'">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/'.$post->getUser()->profile_image().'" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/'.$post->getUser()->username.'">'.$post->getUser()->name().'</a> uploaded a <a id="new-photo-popup" href="/storage/images/'.$post->getContent()->name.'">new photo</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    '.$post->getContent()->created_at->format('d/m/y H:i').'
                                </time>
                            </div>
                        </div>';
                
                        if($post->user_id == Auth::id() || Auth::user()->isAdmin()){
                        $tpl .='<div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                            
                                <li>
                                    <a href="/delete-post/'.$post->id.'">Delete Post</a>
                                </li>
                            </ul>
                        </div>';}
                
                    $tpl .='</div>
                
                    <div class="post-thumb">
                        <a href="/storage/images/'.$post->getContent()->name.'" class="js-zoom-image" onclick="js_zoom_image(this)">
                             <img src="/storage/images/'.$post->getContent()->name.'" alt="photo">
                        </a>
                    </div>
                
                    <div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post('.$post->id.');" class="likes post-add-icon inline-items ';
                                if($post->liked()){
                                    $tpl .= 'active';
                                }
                                $tpl.='">
                            <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>'.$post->likes.'</span>
                        </a>               
                
                    </div>
                
                    <div class="control-block-button post-control-button">
                        ';
                        if($post->user_id != Auth::id()){
                        $tpl .='<a href="#" data-id="'.$post->getUser()->id.'" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>';
                        }
                
                    $tpl .='</div>
                
                </article>
                
                <!-- ... end Post -->

            </div>';
            }
            if($post->type == 'status'){
                $tpl .= '<div class="ui-block">
                    <!-- Post -->
                    
                    <article class="hentry post" data-post="'.$post->id.'">
                    
                            <div class="post__author author vcard inline-items">
                                <img src="/storage/images/'.$post->getUser()->profile_image().'" alt="author">
                    
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="/profile/'.$post->getUser()->username.'">'.$post->getUser()->name().'</a> posted a <a href="/profile/'.$post->getUser()->username.'">status</a>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            '.$post->created_at->format("d/m/y H:i").'
                                        </time>
                                    </div>
                                </div>';
                                if($post->user_id == Auth::id() || Auth::user()->isAdmin()){
                        $tpl .='<div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                            
                                <li>
                                    <a href="/delete-post/'.$post->id.'">Delete Post</a>
                                </li>
                            </ul>
                        </div>';}
                            $tpl .='</div>
                    
                            <p>'.$post->description.'</p>
                    
                            <div class="post-additional-info inline-items">
                    
                                <a href="#" onclick="like_post('.$post->id.');" class="likes post-add-icon inline-items ';
                                if($post->liked()){
                                    $tpl .= 'active';
                                }
                                $tpl.='">
                                    <svg class="olymp-heart-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                    </svg>
                                    <span>'.$post->likes.'</span>
                                </a>                                            
                    
                            </div>
                    
                            <div class="control-block-button post-control-button">
                                ';
                        if($post->user_id != Auth::id()){
                        $tpl .='<a href="#" data-id="'.$post->getUser()->id.'" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>';
                        }
                
                    $tpl .='
                            </div>
                    
                        </article>
                    
                    <!-- .. end Post -->                </div>';
            }
            }

        return response()->json($tpl);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_post($id)
    {
        if(Auth::user()->isAdmin()) {
            $user_id = Post::where('id', $id)->first()->user_id ?? 0;
        } else
            $user_id = Auth::id();
        
        $post = Post::where('user_id', $user_id)->where('id', $id)->delete();
        $notification = Notification::where('user_id', Auth::id())->where('post_id', $id)->delete();
        $notification2 = Notification::where('for_user', Auth::id())->where('post_id', $id)->delete();


        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if($request->seen == 'all'){
            $notification = Notification::where('for_user', Auth::id())->update(['seen' => 1]);
            return response()->json(Auth::user()->unreadNotifications());
        }
        return false;
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
