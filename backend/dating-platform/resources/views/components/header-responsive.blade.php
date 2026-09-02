{{-- Responsive Header-BP  --}}

<header class="header header-responsive" id="site-header-responsive">
    @auth
    <div class="header-content-wrapper">
        <ul class="nav nav-tabs mobile-app-tabs" role="tablist">
            <li class="nav-item">
                @php /* @endphp
                <a class="nav-link" data-toggle="tab" href="#request" role="tab">
                @php */ @endphp
                <a class="nav-link" href="/onlineusers">
                    <div class="control-icon has-items">
                        <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                        {{-- <div id="fr-req-counter2" class="label-avatar bg-blue">@if(Auth::check()) {{Auth::user()->allFriendRequests()->count()}} @endif</div> --}}
                        <div id="fr-req-counter2" class="label-avatar bg-blue">@if(Auth::check() && Auth::user()->isAdmin()){{  App\User::where('gender', 'male')->where('status', 'online')->count() }} @else {{  App\User::where('gender', 'female')->where('status', 'online')->count() }} @endif</div>
                    </div>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#chat" role="tab">
                    <div class="control-icon has-items">
                        <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                        <div id="chat-unread-counter3" class="label-avatar bg-purple">@if(Auth::check()) {{Auth::user()->unreadMessages()}} @endif</div>
                    </div>
                </a>
            </li>
  
            <li class="nav-item">
                <a class="nav-link" href="/packages">
                    <div class="has-items">
                        <svg class="coins-icon @if(Auth::user()->package()) @if(Auth::user()->package_expire_indays() <= 5) class="blink" @endif @endif"><use xlink:href="/svg-icons/sprites/icons.svg#coins-icon"></use></svg>
                    </div>
                </a>
            </li>
            @auth
               {{--  <li class="nav-item">
                    <a class="nav-link" href="/roulette">
                        <div class="has-items">
                            <img src = "/svg/roulette.svg" alt="Roulette" style="width:30px;height:30px;"/>
                        </div>
                    </a>
                </li> --}}
            @endauth
            @php
            	/*
            @endphp
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#notification" role="tab">
                    <div class="control-icon has-items">
                        <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>
                        <div class="label-avatar bg-primary">@if(Auth::check()) {{Auth::user()->unreadNotifications()}} @endif</div>
                    </div>
                </a>
            </li>
            @php
            	*/
            @endphp

            <li class="nav-item">
                <a id="closeResponsiveSearch" class="nav-link" data-toggle="tab" href="#search" role="tab">
                    <svg class="olymp-magnifying-glass-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                    <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                </a>
            </li>
            <li class="hideonmobile">
                <a href="#" class="" id="theme-toggler-m" onclick="toggleTheme(); return false;"></a>
            </li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content tab-content-responsive">

        <div class="tab-pane " id="request" role="tabpanel">

            @php
                	/*
             @endphp
            <div class="mCustomScrollbar" data-mcs-theme="dark">
                <div class="ui-block-title ui-block-title-small">
                    <h6 class="title">{{l("FRIEND REQUESTS")}}</h6>
                    {{-- <a href="/find-friends" onclick="FB_Lead();">{{l("Find Friends")}}</a> --}}
                </div>
                
                <ul id="friend-requests-block2" class="notification-list friend-requests">
                    @if(Auth::check() && Auth::user()->allFriendRequests()->count() != 0)
                            @foreach(Auth::user()->allFriendRequests() as $request)
                            <li tpl-id="{{$request->userFrom()->id}}">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$request->userFrom()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <a href="#" class="h6 notification-friend">{{$request->userFrom()->name()}}</a>
                                    <span class="chat-message-item">Since: {{date_format(date_create($request->created_at),"d/m/Y")}}</span>
                                </div>
                                <span class="notification-icon">
                                    <a href="/acc-friend/{{$request->userFrom()->id}}" onClick="acc_fr_req(this, event);" class="accept-request acc-fr-req">
                                        <span class="icon-add without-text">
                                            <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                                        </span>
                                    </a>

                                    <a href="/delete-request/{{$request->userFrom()->id}}" onClick="del_fr_req(this, event);" class="accept-request request-del del-fr-req">
                                        <span class="icon-minus">
                                            <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                                        </span>
                                    </a>

                                </span>

                                <div class="more">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                                </div>
                            </li>
                            @endforeach
                            @else
                                <span class="no-req">{{l("No friend requests")}}</span>
                            @endif
                </ul>
            </div>
                  @php
                	*/
                @endphp


                <div class="mCustomScrollbar" data-mcs-theme="dark">
                    <div class="ui-block-title ui-block-title-small">
                        <h6 class="title">{{l("ONLINE USERS")}}</h6>
                    </div>
                    <div class="mCustomScrollbar online-users" data-mcs-theme="dark">
                            @if(Auth::check() && Auth::user()->isAdmin())
                            @php
                            $online_users = App\User::where('gender', 'male')->where('status', 'online')->orderBy('created_at', 'desc')->take(10)->get();
                            @endphp
                            @else
                            @php
                            $online_users = App\User::where('gender', 'female')->where('status', 'online')->orderBy('created_at', 'desc')->take(10)->get();

                            @endphp
                             @endif
                            
                                <div class="form-control">
                                <div class="online_users_left_content">
                                <ul class="notification-list online-users-list">
                                @foreach($online_users as $usr)
                                <li>
                                <a href="/profile/{{$usr->username}}">
                                    <div class="inline-items">
                                        <div class="author-thumb">
                                            <img src="/storage/images/{{$usr->profile_image()}}" alt="avatar">
                                        </div>
                                        <div class="notification-event">
                                            <span class="h6 notification-friend">
                                                <span class="highlight">{{$usr->firstname}}</span> {{$usr->lastname}}
                                            </span>   
                                            <span class="chat-message-item"></span>
                                        </div>
                                    </div>
                                </a>
                                </li>
                                @endforeach
                                </ul>
                                </div>
                                {{-- <div id="online_users_left_threshold"></div> --}}
                                </div>
                    </div>
                </div>

        </div>

        <div class="tab-pane " id="chat" role="tabpanel">

            <div class="mCustomScrollbar" data-mcs-theme="dark">
                <div class="ui-block-title ui-block-title-small">
                    <h6 class="title">{{l("Chat / Messages")}}</h6>
                </div>

                <ul id="chat-messages-top2" class="notification-list chat-message">
                    @if(Auth::check())
                    @php
                                            if(Auth::user()->isAdmin()){
                                                $auth_id = Auth::user()->getAccountIds();
                                            }else{
                                                $auth_id = [Auth::id()];
                                            }
                                            $headerChats2 = Auth::user()->lastChats(15);
                                            @endphp
                                            @endif
                            @if(Auth::check() && $headerChats2->count() != 0)
                            @foreach($headerChats2 as $chat)
                            @php
                                $isOwnConversation2 = in_array(Auth::id(), [$chat->lastMessage()->from_user, $chat->lastMessage()->to_user]);
                            @endphp
                            <li @if(!in_array($chat->lastMessage()->from_user, $auth_id))
                                    data-id="{{$chat->lastMessage()->userFrom()->id}}" data-from="{{$chat->lastMessage()->userTo()->id}}"
                                    @elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id())
                                    data-id="{{$chat->lastMessage()->userFrom()->id}}" data-from="{{$chat->lastMessage()->userTo()->id}}"
                                    @else
                                    data-id="{{$chat->lastMessage()->userTo()->id}}" data-from="{{$chat->lastMessage()->userFrom()->id}}"
                                    @endif class="mess__item @if(Auth::user()->isAdmin() && !$isOwnConversation2) mess__item-managed @endif" @if($chat->lastMessage()->status == 0 && !in_array($chat->lastMessage()->from_user, $auth_id)) class="message-unread" @endif onclick="chat_open(this,event);">
                                <div class="author-thumb">
                                    <img src="/storage/images/@if(!in_array($chat->lastMessage()->from_user, $auth_id)){{$chat->lastMessage()->userFrom()->profile_image()}}@elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id()){{$chat->lastMessage()->userFrom()->profile_image()}}@else{{$chat->lastMessage()->userTo()->profile_image()}}@endif" alt="author">
                                </div>
                                <div class="notification-event">
                                    <a href="#" class="h6 notification-friend">
                                        @if(Auth::user()->isAdmin())
                                    @if(!$isOwnConversation2)
                                    <span class="badge badge-warning">{{ l('Cont gestionat') }}</span>
                                    @endif
                                    @if(!in_array($chat->lastMessage()->from_user, $auth_id))
                                    {{$chat->lastMessage()->userFrom()->name()}} - {{$chat->lastMessage()->userTo()->name()}}
                                    @elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id())
                                    {{$chat->lastMessage()->userFrom()->name()}} - {{$chat->lastMessage()->userTo()->name()}}
                                    @else
                                    {{$chat->lastMessage()->userTo()->name()}} - {{$chat->lastMessage()->userFrom()->name()}}
                                    @endif
                                    @else
                                        @if($chat->lastMessage()->from_user != Auth::id())
                                    {{$chat->lastMessage()->userFrom()->name()}}
                                    @else
                                    {{$chat->lastMessage()->userTo()->name()}}
                                    @endif
                                    @endif
                                    </a>
                                    <span class="chat-message-item">@if(in_array($chat->lastMessage()->from_user, $auth_id)) You: @elseif($chat->lastMessage()->from_user == Auth::id() && $chat->lastMessage()->to_user != Auth::id()) You: @endif {{$chat->lastMessage()->message}}</span>
                                </div>
                                <span class="notification-icon">
                                    <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                                </span>
                                <div class="more">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                                </div>
                            </li>
                            @endforeach
                            @else
                                <span class="no-req">{{l("No conversation initiated")}}</span>
                            @endif
                </ul>
            </div>

        </div>

        <div class="tab-pane " id="notification" role="tabpanel">

            <div class="mCustomScrollbar" data-mcs-theme="dark">
                <div class="ui-block-title ui-block-title-small">
                    <h6 class="title">{{l("Notifications")}}</h6>
                    <a onclick="notifications_seen();" href="#">{{l("Mark all as read")}}</a>
                </div>

                <ul class="notification-list">
                    @if(Auth::check())
                    @foreach(Auth::user()->notifications()->take(10) as $nt)
                            @if($nt->type == 'like')
                            <li class="@if($nt->seen == 0) un-read @endif notification-link-btn" data-link="/newsfeed?post_id={{$nt->getPost()->id}}">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> likes your new <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                                    </span>
                            </li>
                            @else
                            <li class="@if($nt->seen == 0) un-read @endif notification-link-btn" data-link="/newsfeed?post_id={{$nt->getPost()->id}}">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> posted a new <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-newsfeed-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-newsfeed-icon"></use></svg>
                                    </span>
                            </li>
                            @endif
                            @endforeach
                            @endif
                </ul>

            </div>

        </div>

        <div class="tab-pane " id="search" role="tabpanel">


                <form class="search-bar w-search notification-list friend-requests">
                    <div class="form-group with-button">
                        <input id="userSearchInputResponsive" class="form-control" placeholder="{{l('Search here people or pages...')}}" type="text">
                    </div>
                </form>


        </div>
        <div class="selectize-dropdown multi form-control js-user-search">
                </div>

    </div>
    <!-- ... end  Tab panes -->
    @else

    <div class="top_auth_mobile">
            <a href="#" class="btn btn-purple btn-md-5 btn-lg" data-toggle="modal" data-target="#register-form-popup">{{l("Register")}}</a>
            <span class="top_auth_spacer">{{l("Or")}}</span>
            <a href="#" class="btn btn-primary btn-md-5 btn-lg" data-toggle="modal" data-target="#login-form-popup">{{l("Login")}}</a>
    </div>

    @endauth

</header>

{{-- ... end Responsive Header-BP  --}}
