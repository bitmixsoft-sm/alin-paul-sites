@extends('layouts.layout')
@section('content')
@auth
<div class="header-spacer"></div>


<div class="container">
    <div class="row">

        <!-- Main Content -->
<div class="container-fluid">
<div class="ui-block">
                
                <!-- News Feed Form  -->
                
                <div class="news-feed-form">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active inline-items" data-toggle="tab" href="#home-1" role="tab" aria-expanded="true">
                
                                <svg class="olymp-status-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-status-icon"></use></svg>
                
                                <span>{{l("Status")}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link inline-items" data-toggle="tab" href="#video" role="tab" aria-expanded="true">
                
                                <svg class="olymp-play-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-play-icon"></use></svg>
                
                                <span>{{l("Videos")}}</span>
                            </a>
                        </li>
                    </ul>
                
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="home-1" role="tabpanel" aria-expanded="true">
                            <form method="POST" action="/newsfeed">
                                @csrf
                                <input type="hidden" name="type" value="text">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{Auth::user()->profile_image()}}" alt="author">
                                </div>
                                <div class="form-group with-icon label-floating is-empty">
                                    <label class="control-label">{{l("Share what you are thinking here...")}}</label>
                                    <textarea name="text" class="form-control" placeholder=""></textarea>
                                </div>
                                <div class="add-options-message">           
                                    <button type="submit" class="btn btn-primary btn-md-2">{{l("Post")}}</button>               
                                </div>
                
                            </form>
                        </div>
                        <div class="tab-pane" id="video" role="tabpanel" aria-expanded="true">
                            <form method="POST" action="/newsfeed" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="video">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{Auth::user()->profile_image()}}" alt="author">
                                </div>
                                <div id="video-1" class="form-group label-floating is-empty">
                                    <a id="upload_video_btn" href="#" class="upload-photo-item">
                                        <svg class="olymp-plus-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-plus-icon"></use></svg>

                                        <h6>Upload Video</h6>
                                        <span>Browse your computer.</span>
                                    </a>
                                    <input id="upload_video" accept="video/*" type="file" name="video">
                                </div>
                                <div id="video-2" class="form-group with-icon label-floating is-empty">
                                    <label class="control-label">{{l("Add a description to your video...")}}</label>
                                    <textarea name="text" class="form-control" placeholder=""></textarea>
                                </div>
                                <div id="video-3" class="form-group with-icon label-floating is-empty">
                                    <span>Your video is uploading. Please wait...</span>
                                </div>
                                <div class="add-options-message">           
                                    <button type="submit" disabled class="btn btn-primary btn-md-2">{{l("Post")}}</button>              
                                </div>
                
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- ... end News Feed Form  -->            </div></div>

        <main class="col col-xl-6 order-xl-2 col-lg-12 order-lg-3 order-3 col-md-12 col-sm-12 col-12">


            <div id="newsfeed-items-grid" data-id="all">
                @if($post != '')
                    @if($post->type == 'album')
                <div class="ui-block border-selected">

                
                <!-- Post -->
                
                <article class="hentry post" data-post="{{$post->id}}">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded")}} {{$post->getContent()->images->count()}} <a href="#" onclick="get_album(this);" @if($post->getcontent()->privacy != '') data-protect="true" @endif data-album="{{$post->getContent()->id}}">{{l("new photos")}}</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                </time>
                            </div>
                        </div>
                        @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                        <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                                <li>
                                    <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                
                    <p>{{$post->getContent()->name}}</p>

                    @if($post->getcontent()->privacy == '')
                    <div class="post-block-photo">
                        @foreach($post->getContent()->images->take(5) as $image)
                        <a href="/storage/images/{{$image->name}}" class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif" onclick="get_album(this);" data-album="{{$post->getContent()->id}}">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                        </a>
                        @endforeach
                        @if($post->getContent()->images->count() > 6)
                        @php
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        @endphp
                        <a href="/storage/images/{{$image->name}}" onclick="get_album(this);" data-album="{{$post->getContent()->id}}" class="more-photos col-3-width">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                            <span class="h2">+{{$post->getContent()->images->count()-5}}</span>
                        </a>
                        @else
                        @if($post->getContent()->images->count() > 5)
                        @php
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        @endphp
                            <a href="/storage/images/{{$image->name}}" class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif" onclick="get_album(this);" data-album="{{$post->getContent()->id}}">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                        </a>
                        @endif
                        @endif
                    </div>
                    @else
                        <div class="post-thumb">
                            <a href="#" onclick="get_album(this);" data-protect="true" data-album="{{$post->getContent()->id}}">
                                <img class="feed-protected" src="/img/lock.png" alt="photo">
                            </a>
                        </div>
                    @endif
                
                    <div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                            <svg class="olymp-heart-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>{{$post->likes}}</span>
                        </a>            
                
                    </div>
                
                    <div class="control-block-button post-control-button">
                        @if($post->user_id != Auth::id())
                        <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>
                        @endif
                    </div>
                
                </article>
                
                <!-- ... end Post -->
            </div>
            @endif
            @if($post->type == 'image')
                <div class="ui-block border-selected">

                
                <!-- Post -->
                
                <article class="hentry post has-post-thumbnail" data-post="{{$post->id}}">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded a")}} <a class="new-photo-popup" href="/storage/images/{{$post->getContent()->name}}">{{l("new photo")}}</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                </time>
                            </div>
                        </div>
                        @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                        <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                                <li>
                                    <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                
                    <div class="post-thumb">
                        <a href="/storage/images/{{$post->getContent()->name}}" class="js-zoom-image">
                             <img src="/storage/images/{{$post->getContent()->name}}" alt="photo">
                        </a>
                    </div>
                
                    <div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                            <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>{{$post->likes}}</span>
                        </a>               
                
                    </div>
                
                    <div class="control-block-button post-control-button">
                        @if($post->user_id != Auth::id())
                        <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>
                        @endif
                    </div>
                
                </article>
                
                <!-- ... end Post -->

            </div>
            @endif
            @if($post->type == 'status')
                <div class="ui-block border-selected">
                    <!-- Post -->
                    
                    <article class="hentry post" data-post="{{$post->id}}">
                    
                            <div class="post__author author vcard inline-items">
                                <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                    
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}} <a href="/profile/{{$post->getUser()->username}}">{{l("status")}}</a>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            {{$post->created_at->format('d/m/y H:i')}}
                                        </time>
                                    </div>
                                </div>
                                @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                                <div class="more">
                                    <svg class="olymp-three-dots-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                    </svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                    
                            <p>{{$post->description}}</p>
                    
                            <div class="post-additional-info inline-items">
                    
                                <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                    <svg class="olymp-heart-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                    </svg>
                                    <span>{{$post->likes}}</span>
                                </a>                                            
                    
                            </div>
                    
                            <div class="control-block-button post-control-button">
                                @if($post->user_id != Auth::id())
                                <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                                </a>
                                @endif
                            </div>
                    
                        </article>
                    
                    <!-- .. end Post -->                </div>  
            @endif
            @if($post->type == 'video')
                <div class="ui-block border-selected">
                    
                    <article class="hentry post video">
                    
                        <div class="post__author author vcard inline-items">
                                <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                    
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}} <a href="/newsfeed?post_id={{$post->id}}">{{l("a video")}}</a>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            {{$post->created_at->format('d/m/y H:i')}}
                                        </time>
                                    </div>
                                </div>
                                @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                                <div class="more">
                                    <svg class="olymp-three-dots-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                    </svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                        </div>
                    
                        <p>{{$post->description}}</p>
                    
                        <div class="post-video">
                            <div class="video-thumb">
                                <video>
                                    <source src="/storage/videos/{{$post->getContent()->name}}">
                                </video>
                                <a href="/storage/videos/{{$post->getContent()->name}}" class="play-video">
                                    <svg class="olymp-play-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-play-icon"></use></svg>
                                </a>
                            </div>
                        </div>
                    
                        <div class="post-additional-info inline-items">
                    
                                <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                    <svg class="olymp-heart-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                    </svg>
                                    <span>{{$post->likes}}</span>
                                </a>                                            
                    
                            </div>
                    
                            <div class="control-block-button post-control-button">
                                @if($post->user_id != Auth::id())
                                <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                                </a>
                                @endif
                            </div>
                    
                    </article>
                </div>
            @endif  
                @endif
                @foreach($posts as $post)
                @if($post->type == 'album')
                <div class="ui-block">

                
                <!-- Post -->
                
                <article class="hentry post" data-post="{{$post->id}}">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded")}} {{$post->getContent()->images->count()}} <a href="#" onclick="get_album(this);" @if($post->getcontent()->privacy != '') data-protect="true" @endif data-album="{{$post->getContent()->id}}">{{l("new photos")}}</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                </time>
                            </div>
                        </div>
                        @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                        <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                                <li>
                                    <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                
                    <p>{{$post->getContent()->name}}</p>

                    @if($post->getcontent()->privacy == '')
                    <div class="post-block-photo">
                        @foreach($post->getContent()->images->take(5) as $image)
                        <a href="/storage/images/{{$image->name}}" class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif" onclick="get_album(this);" data-album="{{$post->getContent()->id}}">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                        </a>
                        @endforeach
                        @if($post->getContent()->images->count() > 6)
                        @php
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        @endphp
                        <a href="/storage/images/{{$image->name}}" onclick="get_album(this);" data-album="{{$post->getContent()->id}}" class="more-photos col-3-width">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                            <span class="h2">+{{$post->getContent()->images->count()-5}}</span>
                        </a>
                        @else
                        @if($post->getContent()->images->count() > 5)
                        @php
                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                        @endphp
                            <a href="/storage/images/{{$image->name}}" class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif" onclick="get_album(this);" data-album="{{$post->getContent()->id}}">
                            <div class="post-photo-cont">
                                <img src="/storage/images/{{$image->name}}" alt="photo">
                            </div>
                        </a>
                        @endif
                        @endif
                    </div>
                    @else
                        <div class="post-thumb">
                            <a href="#" onclick="get_album(this);" data-protect="true" data-album="{{$post->getContent()->id}}">
                                <img class="feed-protected" src="/img/lock.png" alt="photo">
                            </a>
                        </div>
                    @endif
                
                    <div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                            <svg class="olymp-heart-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>{{$post->likes}}</span>
                        </a>            
                
                    </div>
                
                    <div class="control-block-button post-control-button">
                        @if($post->user_id != Auth::id())
                        <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>
                        @endif
                    </div>
                
                </article>
                
                <!-- ... end Post -->
            </div>
            @endif
            @if($post->type == 'image')
                <div class="ui-block">

                
                <!-- Post -->
                
                <article class="hentry post has-post-thumbnail" data-post="{{$post->id}}">
                
                    <div class="post__author author vcard inline-items">
                        <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                
                        <div class="author-date">
                            <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded a")}} <a class="new-photo-popup" href="/storage/images/{{$post->getContent()->name}}">{{l("new photo")}}</a>
                            <div class="post__date">
                                <time class="published" datetime="2017-03-24T18:18">
                                    {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                </time>
                            </div>
                        </div>
                        @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                        <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                            <ul class="more-dropdown">
                                <li>
                                    <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                
                    <div class="post-thumb">
                        <a href="/storage/images/{{$post->getContent()->name}}" class="js-zoom-image">
                             <img src="/storage/images/{{$post->getContent()->name}}" alt="photo">
                        </a>
                    </div>
                
                    <div class="post-additional-info inline-items">
                
                        <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                            <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                            <span>{{$post->likes}}</span>
                        </a>               
                
                    </div>
                
                    <div class="control-block-button post-control-button">
                        @if($post->user_id != Auth::id())
                        <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                            <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                        </a>
                        @endif
                    </div>
                
                </article>
                
                <!-- ... end Post -->

            </div>
            @endif
            @if($post->type == 'status')
                <div class="ui-block">
                    <!-- Post -->
                    
                    <article class="hentry post" data-post="{{$post->id}}">
                    
                            <div class="post__author author vcard inline-items">
                                <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                    
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}} <a href="/profile/{{$post->getUser()->username}}">{{l("status")}}</a>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            {{$post->created_at->format('d/m/y H:i')}}
                                        </time>
                                    </div>
                                </div>
                                @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                                <div class="more">
                                    <svg class="olymp-three-dots-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                    </svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                    
                            <p>{{$post->description}}</p>
                    
                            <div class="post-additional-info inline-items">
                    
                                <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                    <svg class="olymp-heart-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                    </svg>
                                    <span>{{$post->likes}}</span>
                                </a>                                            
                    
                            </div>
                    
                            <div class="control-block-button post-control-button">
                                @if($post->user_id != Auth::id())
                                <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                                </a>
                                @endif
                            </div>
                    
                        </article>
                    
                    <!-- .. end Post -->                </div>  
            @endif
            @if($post->type == 'video')
                <div class="ui-block">
                    
                    <article class="hentry post video">
                    
                        <div class="post__author author vcard inline-items">
                                <img src="/storage/images/{{$post->getUser()->profile_image()}}" alt="author">
                    
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}} <a href="/newsfeed?post_id={{$post->id}}">{{l("a video")}}</a>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            {{$post->created_at->format('d/m/y H:i')}}
                                        </time>
                                    </div>
                                </div>
                                @if($post->user_id == Auth::id() || Auth::user()->isAdmin())
                                <div class="more">
                                    <svg class="olymp-three-dots-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                    </svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                        </div>
                    
                        <p>{{$post->description}}</p>
                    
                        <div class="post-video">
                            <div class="video-thumb">
                                <video>
                                    <source src="/storage/videos/{{$post->getContent()->name}}">
                                </video>
                                <a href="/storage/videos/{{$post->getContent()->name}}" class="play-video">
                                    <svg class="olymp-play-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-play-icon"></use></svg>
                                </a>
                            </div>
                        </div>
                    
                        <div class="post-additional-info inline-items">
                    
                                <a href="#" onclick="like_post({{$post->id}});" class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                    <svg class="olymp-heart-icon">
                                        <use xlink:href="svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                    </svg>
                                    <span>{{$post->likes}}</span>
                                </a>                                            
                    
                            </div>
                    
                            <div class="control-block-button post-control-button">
                                @if($post->user_id != Auth::id())
                                <a href="#" data-id="{{$post->getUser()->id}}" onclick="chat_open(this,event);" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                                </a>
                                @endif
                            </div>
                    
                    </article>
                </div>
            @endif
            @endforeach
                
            </div>
<div id="newsfeed_loader" style="height: 1px;"></div>
        </main>

        <!-- ... end Main Content -->


        <!-- Left Sidebar -->

        <aside class="hidden-sm hidden-xs col col-xl-3 order-xl-1 order-1 col-lg-6 order-lg-1 col-md-6 col-sm-12 col-12">
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{l("Friend Suggestions")}}</h6>
                </div>

                
                
                <!-- W-Action -->
                
                <ul class="widget w-friend-pages-added notification-list friend-requests">
                    @foreach($suggestions as $item)
                    <li class="inline-items">
                        <div class="author-thumb">
                            <img src="/storage/images/{{$item->profile_image()}}" alt="author">
                        </div>
                        <div class="notification-event">
                            <a href="/profile/{{$item->username}}" class="h6 notification-friend">{{$item->name()}}</a>
                            <span class="chat-message-item">{{$item->commonFriends()->count()}} {{l("Friends in Common")}}</span>
                        </div>
                        <span class="notification-icon">
                            <a href="/profile/{{$item->username}}" class="accept-request">
                                <span class="icon-add without-text">
                                    <svg class="olymp-magnifying-glass-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                                </span>
                            </a>
                        </span>
                    </li>
                    @endforeach
                
                </ul>
                
                <!-- ... end W-Action -->
            </div>
        </aside>

        <!-- ... end Left Sidebar -->

        <aside class="col col-xl-3 order-xl-2 order-2 col-lg-6 order-lg-2 col-md-6 col-sm-12 col-12">
            <div class="ui-block">

                <div class="ui-block-title">
                    <h6 class="title">{{l("Activity Feed")}}</h6>
                    <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg></a>
                </div>

                
                <!-- W-Activity-Feed -->
                
                <ul class="widget w-activity-feed notification-list notifications-widget">
                    @foreach(Auth::user()->notifications()->take(5) as $nt)
                            @if($nt->type == 'like')
                            <li @if($nt->seen == 0) class="un-read" @endif>
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> {{l("likes your new")}} <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                            </li>
                            @else
                            <li @if($nt->seen == 0) class="un-read" @endif>
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> {{l("posted a new")}} <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                            </li>
                            @endif

                            @endforeach
                </ul>
                
                <!-- .. end W-Activity-Feed -->
            </div>
        </aside>


    </div>
</div>
<div class="modal fade modal-has-swiper" id="open-photo-popup-v2" tabindex="-1" role="dialog" aria-labelledby="open-photo-popup-v2" aria-hidden="true">
            <div class="modal-dialog window-popup open-photo-popup open-photo-popup-v2" role="document">
                <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-body">
                        <div class="open-photo-thumb">

                            <div class="swiper-container" data-effect="fade" data-autoplay="4000">

                                <!-- Additional required wrapper -->
                                <div id="album-slides" class="swiper-wrapper">
                                    <!-- Slides -->

                                </div>

                            </div>

                            <!--Pagination tabs-->

                            <div id="album-slides-pag" class="slider-slides">

                                <!--Prev Next Arrows-->

                                <svg class="btn-next olymp-popup-right-arrow"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-right-arrow"></use></svg>

                                <svg class="btn-prev olymp-popup-left-arrow"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-left-arrow"></use></svg>

                            </div>

                        </div>

                        <div class="open-photo-content">

                    <article class="hentry post">

                        <div id="album-item-user" class="post__author author vcard inline-items">
                            <img src="" alt="author">

                            <div class="author-date">
                                <a class="h6 post__author-name fn" href=""></a>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        
                                    </time>
                                </div>
                            </div>

                        </div>

                        <p id="album-item-desc"></p>

                        <div id="control-block-album" class="control-block-button post-control-button">
                            <a href="#" data-id="" onclick="chat_open(this,event); modal_hide('open-photo-popup-v2');" data-toggle="tooltip" data-placement="right" data-original-title="Message" class="btn btn-control">
                                <svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
                            </a>
                        </div>

                    </article>

                </div>
                    </div>
                </div>
            </div>
        </div>
@endauth
@endsection
@section('scripts')
<script type="text/javascript">
    $("#upload_video_btn").click(function(e){
       e.preventDefault();
       $("#upload_video").trigger('click');
    });
    $("#upload_video").change(function(){
       $("#video-1").hide();
       $("#video-2").show();
       $("#video button[type='submit']").prop("disabled", false);
    });
    $("#video button[type='submit']").click(function(e){
       e.preventDefault();
       $("#video-2").hide();
       $("#video-3").show();
       $( "#video form" ).submit();
    }); 
</script>
@endsection