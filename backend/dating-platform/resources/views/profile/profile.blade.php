@extends('layouts.layout')

@section('content')
    <div class="header-spacer"></div>
    <!-- Top Header-Profile -->

    @include('components.top-header-profile')

    <!-- ... end Top Header-Profile -->
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col col-xl-6 order-xl-2 col-lg-12 order-lg-3 order-3 col-md-12 col-sm-12 col-12">
                @auth
                    <div id="newsfeed-items-grid" data-id="{{$user->id}}">
                        @foreach($posts as $post)
                            @if($post->type === 'album')
                                <div class="ui-block">
                                    <!-- Post -->
                                    <article class="hentry post" data-post="{{$post->id}}">

                                        <div class="post__author author vcard inline-items">
                                            <img src="/storage/images/{{$post->getUser()->profile_image()}}"
                                                 alt="author">

                                            <div class="author-date">
                                                <a class="h6 post__author-name fn"
                                                   href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded")}} {{$post->getContent()->images->count()}}
                                                <a href="#" onclick="get_album(this);"
                                                   @if($post->getcontent()->privacy !== '') data-protect="true"
                                                   @endif data-album="{{$post->getContent()->id}}">{{l("new photos")}}</a>
                                                <div class="post__date">
                                                    <time class="published" datetime="2017-03-24T18:18">
                                                        {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                                    </time>
                                                </div>
                                            </div>

                                            @if($post->user_id === Auth::id())
                                                <div class="more">
                                                    <svg class="olymp-three-dots-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                                    </svg>
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
                                                    <a href="/storage/images/{{$image->name}}"
                                                       class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif"
                                                       onclick="get_album(this);"
                                                       data-album="{{$post->getContent()->id}}">
                                                        <div class="post-photo-cont">
                                                            <img src="/storage/images/{{$image->name}}" alt="photo">
                                                        </div>
                                                    </a>
                                                @endforeach
                                                @if($post->getContent()->images->count() > 6)
                                                    @php
                                                        $image = $post->getContent()->images->slice(5)->take(1)->first();
                                                    @endphp
                                                    <a href="/storage/images/{{$image->name}}"
                                                       onclick="get_album(this);"
                                                       data-album="{{$post->getContent()->id}}"
                                                       class="more-photos col-3-width">
                                                        <div class="post-photo-cont">
                                                            <img src="/storage/images/{{$image->name}}" alt="photo">
                                                        </div>
                                                        <span
                                                            class="h2">+{{$post->getContent()->images->count()-5}}</span>
                                                    </a>
                                                @else
                                                    @if($post->getContent()->images->count() > 5)
                                                        @php
                                                            $image = $post->getContent()->images->slice(5)->take(1)->first();
                                                        @endphp
                                                        <a href="/storage/images/{{$image->name}}"
                                                           class="col @if($post->getContent()->images->count() < 5) half-width @else col-3-width @endif"
                                                           onclick="get_album(this);"
                                                           data-album="{{$post->getContent()->id}}">
                                                            <div class="post-photo-cont">
                                                                <img src="/storage/images/{{$image->name}}" alt="photo">
                                                            </div>
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <div class="post-thumb">
                                                <a href="#" onclick="get_album(this);" data-protect="true"
                                                   data-album="{{$post->getContent()->id}}">
                                                    <img class="feed-protected" src="/img/lock.png" alt="photo">
                                                </a>
                                            </div>
                                        @endif

                                        <div class="post-additional-info inline-items">

                                            <a href="#" onclick="like_post({{$post->id}});"
                                               class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                                <svg class="olymp-heart-icon">
                                                    <use
                                                        xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                                </svg>
                                                <span>{{$post->likes}}</span>
                                            </a>

                                        </div>

                                        <div class="control-block-button post-control-button">
                                            @if($user->id !== Auth::id())
                                                <a href="#" data-id="{{$post->getUser()->id}}"
                                                   onclick="chat_open(this,event);" class="btn btn-control">
                                                    <svg class="olymp-comments-post-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>

                                    </article>

                                    <!-- ... end Post -->
                                </div>
                            @endif
                            @if($post->type === 'image')
                                <div class="ui-block">


                                    <!-- Post -->

                                    <article class="hentry post has-post-thumbnail" data-post="{{$post->id}}">

                                        <div class="post__author author vcard inline-items">
                                            <img src="/storage/images/{{$post->getUser()->profile_image()}}"
                                                 alt="author">

                                            <div class="author-date">
                                                <a class="h6 post__author-name fn"
                                                   href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("uploaded a")}}
                                                <a class="new-photo-popup"
                                                   href="/storage/images/{{$post->getContent()->name}}">{{l("new photo")}}</a>
                                                <div class="post__date">
                                                    <time class="published" datetime="2017-03-24T18:18">
                                                        {{$post->getContent()->created_at->format('d/m/y H:i')}}
                                                    </time>
                                                </div>
                                            </div>

                                            @if($post->user_id === Auth::id())
                                                <div class="more">
                                                    <svg class="olymp-three-dots-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
                                                    </svg>
                                                    <ul class="more-dropdown">
                                                        <li>
                                                            <a href="/delete-post/{{$post->id}}">{{l("Delete Post")}}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif

                                        </div>

                                        <div class="post-thumb">
                                            <a href="/storage/images/{{$post->getContent()->name}}"
                                               class="js-zoom-image">
                                                <img src="/storage/images/{{$post->getContent()->name}}" alt="photo">
                                            </a>
                                        </div>

                                        <div class="post-additional-info inline-items">

                                            <a href="#" onclick="like_post({{$post->id}});"
                                               class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                                <svg class="olymp-heart-icon">
                                                    <use
                                                        xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                                </svg>
                                                <span>{{$post->likes}}</span>
                                            </a>

                                        </div>

                                        <div class="control-block-button post-control-button">

                                            @if($user->id !== Auth::id())
                                                <a href="#" data-id="{{$post->getUser()->id}}"
                                                   onclick="chat_open(this,event);" class="btn btn-control">
                                                    <svg class="olymp-comments-post-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use>
                                                    </svg>
                                                </a>
                                            @endif

                                        </div>

                                    </article>

                                    <!-- ... end Post -->

                                </div>
                            @endif
                            @if($post->type === 'status')
                                <div class="ui-block">
                                    <!-- Post -->

                                    <article class="hentry post" data-post="{{$post->id}}">

                                        <div class="post__author author vcard inline-items">
                                            <img src="/storage/images/{{$post->getUser()->profile_image()}}"
                                                 alt="author">

                                            <div class="author-date">
                                                <a class="h6 post__author-name fn"
                                                   href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}}
                                                <a href="/profile/{{$post->getUser()->username}}">{{l("status")}}</a>
                                                <div class="post__date">
                                                    <time class="published" datetime="2017-03-24T18:18">
                                                        {{$post->created_at->format('d/m/y H:i')}}
                                                    </time>
                                                </div>
                                            </div>
                                            @if($post->user_id === Auth::id())
                                                <div class="more">
                                                    <svg class="olymp-three-dots-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
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

                                            <a href="#" onclick="like_post({{$post->id}});"
                                               class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                                <svg class="olymp-heart-icon">
                                                    <use
                                                        xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                                </svg>
                                                <span>{{$post->likes}}</span>
                                            </a>

                                        </div>

                                        <div class="control-block-button post-control-button">
                                            @if($post->user_id !== Auth::id())
                                                <a href="#" data-id="{{$post->getUser()->id}}"
                                                   onclick="chat_open(this,event);" class="btn btn-control">
                                                    <svg class="olymp-comments-post-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>

                                    </article>

                                    <!-- .. end Post -->                </div>
                            @endif
                            @if($post->type === 'video')
                                <div class="ui-block">

                                    <article class="hentry post video">

                                        <div class="post__author author vcard inline-items">
                                            <img src="/storage/images/{{$post->getUser()->profile_image()}}"
                                                 alt="author">

                                            <div class="author-date">
                                                <a class="h6 post__author-name fn"
                                                   href="/profile/{{$post->getUser()->username}}">{{$post->getUser()->name()}}</a> {{l("posted a")}}
                                                <a href="/newsfeed?post_id={{$post->id}}">{{l("a video")}}</a>
                                                <div class="post__date">
                                                    <time class="published" datetime="2017-03-24T18:18">
                                                        {{$post->created_at->format('d/m/y H:i')}}
                                                    </time>
                                                </div>
                                            </div>
                                            @if($post->user_id === Auth::id())
                                                <div class="more">
                                                    <svg class="olymp-three-dots-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
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
                                                <a href="/storage/videos/{{$post->getContent()->name}}"
                                                   class="play-video">
                                                    <svg class="olymp-play-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-play-icon"></use>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="post-additional-info inline-items">

                                            <a href="#" onclick="like_post({{$post->id}});"
                                               class="likes post-add-icon inline-items @if($post->liked()) active @endif">
                                                <svg class="olymp-heart-icon">
                                                    <use
                                                        xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use>
                                                </svg>
                                                <span>{{$post->likes}}</span>
                                            </a>

                                        </div>

                                        <div class="control-block-button post-control-button">
                                            @if($post->user_id !== Auth::id())
                                                <a href="#" data-id="{{$post->getUser()->id}}"
                                                   onclick="chat_open(this,event);" class="btn btn-control">
                                                    <svg class="olymp-comments-post-icon">
                                                        <use
                                                            xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>

                                    </article>
                                </div>
                            @endif
                        @endforeach

                    </div>

                @else

                    @guest
                        <div class="medium-padding120 guest-profile">
                            <div class="container">
                                <div class="row">
                                    <div class="col col-xl-12 col-lg-12 col-md-12 col-12 m-auto">
                                        <div class="logout-content">
                                            <div class="logout-icon">
                                                <i class="fas fa-times"></i>
                                            </div>
                                            <h6>{{l("Do you wanna check")}} {{$user->firstname}}{{l("’s Profile?")}}</h6>
                                            <p><a href="#" data-toggle="modal"
                                                  data-target="#login-form-popup">{{l("Login")}}</a> {{l("or")}} <a
                                                    href="#" data-toggle="modal"
                                                    data-target="#register-form-popup">{{l("Register")}}</a> {{l("now to create your own profile and have access to all the")}} {{config('app.name')}} {{l("awesome features!")}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endguest

                @endauth
            </div>


            <!-- ... end Main Content -->


            <!-- Left Sidebar -->

            <div class="col col-xl-3 order-xl-1 col-lg-12 order-lg-1 order-1 col-md-12 col-sm-12 col-12">
                @if($user->description !== "" || $user->social_status !== "" || $user->job !== "")
                    <div class="ui-block">
                        <div class="ui-block-title">
                            <h6 class="title">{{l("Profile Intro")}}</h6>
                        </div>
                        <div class="ui-block-content">

                            <!-- W-Personal-Info -->

                            <ul class="widget w-personal-info item-block">
                                @if($user->description !== "")
                                    <li>
                                        <span class="title">{{l("About Me:")}}</span>
                                        <span class="text">{{$user->description}}</span>
                                    </li>
                                @endif
                                @if($user->social_status !== "")
                                    <li>
                                        <span class="title">{{l("Social Status:")}}</span>
                                        <span class="text">
                                @switch($user->social_status)
                                                @case('single')
                                                Single
                                                @break
                                                @case('relationship')
                                                In a relationship
                                                @break
                                                @case('married')
                                                Married
                                                @break
                                                @case('dating')
                                                Dating
                                                @break
                                                @case('complicated')
                                                It's complicated
                                                @break
                                            @endswitch
                            </span>
                                    </li>
                                @endif
                                @if($user->job !== "")
                                    <li>
                                        <span class="title">{{l("Occupation:")}}</span>
                                        <span class="text">{{$user->job}}</span>
                                    </li>
                                @endif
                            </ul>

                            <!-- .. end W-Personal-Info -->
                        </div>
                    </div>
                @endif

            </div>

            <!-- ... end Left Sidebar -->

            <!-- Right Sidebar -->

            <div class="col col-xl-3 order-xl-3 col-lg-12 order-lg-2 order-2 col-md-12 col-sm-12 col-12">
                <div class="row">
                    @if($user->images->count() > 0)
                        <div class="col-lg-12 col-md-6 col-xs-12">
                            <div class="ui-block">
                                <div class="ui-block-title">
                                    <h6 class="title">{{l("Last Photos")}}</h6>
                                </div>

                                <div class="ui-block-content">

                                    <!-- W-Latest-Photo -->

                                    <ul class="widget w-last-photo js-zoom-gallery">
                                        @foreach($user->images->where('privacy', '')->take(9) as $image)
                                            <a href="/storage/images/{{$image->name}}">
                                                <li>
                                                    <div class="last-photo-widget-custom">
                                                        <img src="/storage/images/{{$image->name}}" alt="photo">
                                                    </div>
                                                </li>
                                            </a>
                                        @endforeach
                                    </ul>


                                    <!-- .. end W-Latest-Photo -->
                                </div>

                            </div>
                        </div>
                    @endif
                    @auth
                        <div
                            class="col-lg-12 @if($user->images->count() > 0) col-md-6 @else col-md-12 @endif col-xs-12">
                            <div class="ui-block">
                                <div class="ui-block-title">
                                    <h6 class="title">{{l("Friends")}} ({{$user->allFriends('female')->count()}})</h6>
                                </div>
                                <div class="ui-block-content">

                                    <!-- W-Faved-Page -->

                                    <ul class="widget w-faved-page">

                                        @foreach($user->allFriends('female')->take(15) as $friend)
                                            <li data-toggle="tooltip" data-placement="top" title=""
                                                data-original-title="{{$friend->name()}}">
                                                <a href="/profile/{{$friend->username}}">
                                                    <img src="/storage/images/{{$friend->profile_image()}}"
                                                         alt="author">
                                                </a>
                                            </li>
                                        @endforeach
                                        @if($user->allFriends('female')->count() > 15)
                                            <li class="all-users">
                                                <a href="#">+{{$user->allFriends('female')->count() - 15}}</a>
                                            </li>
                                        @endif
                                    </ul>

                                    <!-- .. end W-Faved-Page -->
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- ... end Right Sidebar -->

        </div>
    </div>

    <a class="back-to-top" href="#">
        <img src="/svg-icons/back-to-top.svg" alt="arrow" class="back-icon">
    </a>

    <div class="modal fade modal-has-swiper" id="open-photo-popup-v2" tabindex="-1" role="dialog"
         aria-labelledby="open-photo-popup-v2" aria-hidden="true">
        <div class="modal-dialog window-popup open-photo-popup open-photo-popup-v2" role="document">
            <div class="modal-content">
                <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                    <svg class="olymp-close-icon">
                        <use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use>
                    </svg>
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

                            <svg class="btn-next olymp-popup-right-arrow">
                                <use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-right-arrow"></use>
                            </svg>

                            <svg class="btn-prev olymp-popup-left-arrow">
                                <use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-left-arrow"></use>
                            </svg>

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
                                @if($user->id !== Auth::id())
                                    <a href="#" data-id=""
                                       onclick="chat_open(this,event); modal_hide('open-photo-popup-v2');"
                                       data-toggle="tooltip" data-placement="right" data-original-title="Message"
                                       class="btn btn-control">
                                        <svg class="olymp-comments-post-icon">
                                            <use
                                                xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use>
                                        </svg>
                                    </a>
                                @endif
                            </div>

                        </article>

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection