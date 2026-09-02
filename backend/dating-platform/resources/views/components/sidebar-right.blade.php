<!-- Fixed Sidebar Right -->

<div id="sidebar-right-listener" class="fixed-sidebar right">
    <div class="fixed-sidebar-right sidebar--small" id="sidebar-right">

        <div class="mCustomScrollbar" data-mcs-theme="dark">
            <ul id="chat-users-small" class="chat-users">
                @if(Auth::check())
                @php
                    if(Auth::user()->isAdmin()){
                        $chat_usr = Auth::user()->allFriends();
                    }else{
                        $chat_usr = Auth::user()->allFriends();
                    } 
                @endphp
                @foreach(Auth::user()->lastChats() as $chat)
                @php
                $friend = $chat->otherUser();
                @endphp
                <li data-id="{{$friend->id}}" data-from="{{$chat->thisUser()->id}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span class="icon-status @if($friend->gender =='female') online @else @if($friend->status == 'status-invisible') disconected @else {{$friend->status}} @endif @endif"></span>
                        @if($chat->thisUser()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{$chat->thisUser()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>
                </li>
                @endforeach
                @foreach(Auth::user()->allFriendsNotIn(Auth::user()->allUserChatsOnly()) as $friend)
                <li data-id="{{$friend->id}}" data-from="{{Auth::id()}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span class="icon-status @if($friend->gender =='female') online @else @if($friend->status == 'status-invisible') disconected @else {{$friend->status}} @endif @endif"></span>
                        @if(Auth::user()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{Auth::user()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>
                </li>
                @endforeach
                @endif
            </ul>
            <div id="chat_load_threshold"></div>
        </div>

        <div class="search-friend inline-items">
            <a href="#" class="js-sidebar-open">
                <svg class="olymp-menu-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-menu-icon"></use></svg>
            </a>
        </div>

        <a href="#" class="olympus-chat inline-items">
            <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
        </a>

    </div>

    <div class="fixed-sidebar-right sidebar--large" id="sidebar-right-1">

        <div class="mCustomScrollbar" data-mcs-theme="dark">

            <ul id="chat-users-large" class="chat-users">
                @if(Auth::check())
                @foreach(Auth::user()->lastChats() as $chat)
                @php
                $friend = $chat->otherUser();
                @endphp
                <li data-id="{{$friend->id}}" data-from="{{$chat->thisUser()->id}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span  data="status" class="icon-status @if($friend->gender =='female') online @else {{$friend->status}} @endif"></span>
                        @if($chat->thisUser()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{$chat->thisUser()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>

                    <div class="author-status">
                        @if(Auth::user()->isAdmin())
                        <a href="#" class="h6 author-name">{{$friend->name()}} - {{$chat->thisUser()->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @else
                        <a href="#" class="h6 author-name">{{$friend->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @endif
                    </div>



                </li>
                @endforeach
                @foreach(Auth::user()->allFriendsNotIn(Auth::user()->allUserChatsOnly()) as $friend)
                <li data-id="{{$friend->id}}" data-from="{{Auth::id()}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span  data="status" class="icon-status @if($friend->gender =='female') online @else {{$friend->status}} @endif"></span>
                        @if(Auth::user()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{Auth::user()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>

                    <div class="author-status">
                        @if(Auth::user()->isAdmin())
                        <a href="#" class="h6 author-name">{{$friend->name()}} - {{Auth::user()->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @else
                        <a href="#" class="h6 author-name">{{$friend->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @endif
                    </div>



                </li>
                @endforeach
                @endif
            </ul>
            <div id="chat_load_threshold_lg"></div>


        </div>

        <div class="search-friend inline-items">
            <form class="form-group" >
                <input class="form-control" placeholder="{{l('Search Friends...')}}" value="" onkeyup="search_friends(this);" type="text">
            </form>

            <a href="#" class="js-sidebar-open">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>
        </div>

        <a href="/find-friends" class="olympus-chat inline-items">

            <h6 class="olympus-chat-title">{{env('APP_NAME')}}</h6>
            <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
        </a>

    </div>
</div>

<!-- ... end Fixed Sidebar Right -->


<!-- Fixed Sidebar Right-Responsive -->

<div class="fixed-sidebar right fixed-sidebar-responsive">

    <div class="fixed-sidebar-right sidebar--small" id="sidebar-right-responsive">

        <a href="#" class="olympus-chat inline-items js-sidebar-open">
            <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
        </a>

        <div class="mCustomScrollbar" data-mcs-theme="dark">
            <ul id="chat-users-small-responsive" class="chat-users">
                @if(Auth::check())
                @php
                    if(Auth::user()->isAdmin()){
                        $chat_usr = Auth::user()->allFriends();
                    }else{
                        $chat_usr = Auth::user()->allFriends();
                    } 
                @endphp
                @foreach(Auth::user()->lastChats() as $chat)
                @php
                $friend = $chat->otherUser();
                @endphp
                <li data-id="{{$friend->id}}" data-from="{{$chat->thisUser()->id}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span class="icon-status @if($friend->gender =='female') online @else @if($friend->status == 'status-invisible') disconected @else {{$friend->status}} @endif @endif"></span>
                        @if($chat->thisUser()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{$chat->thisUser()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>
                </li>
                @endforeach
                @foreach(Auth::user()->allFriendsNotIn(Auth::user()->allUserChatsOnly()) as $friend)
                <li data-id="{{$friend->id}}" data-from="{{Auth::id()}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span class="icon-status @if($friend->gender =='female') online @else @if($friend->status == 'status-invisible') disconected @else {{$friend->status}} @endif @endif"></span>
                        @if(Auth::user()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{Auth::user()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>
                </li>
                @endforeach
                @endif
            </ul>
            <div id="chat_load_threshold"></div>
        </div>

    </div>
    <div class="fixed-sidebar-right sidebar--large" id="sidebar-right-1-responsive">

        <div class="mCustomScrollbar" data-mcs-theme="dark">

            <ul id="chat-users-large-responsive" class="chat-users">
                @if(Auth::check())
                @foreach(Auth::user()->lastChats() as $chat)
                @php
                $friend = $chat->otherUser();
                @endphp
                <li data-id="{{$friend->id}}" data-from="{{$chat->thisUser()->id}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span  data="status" class="icon-status @if($friend->gender =='female') online @else {{$friend->status}} @endif"></span>
                        @if($chat->thisUser()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{$chat->thisUser()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>

                    <div class="author-status">
                        @if(Auth::user()->isAdmin())
                        <a href="#" class="h6 author-name">{{$friend->name()}} - {{$chat->thisUser()->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @else
                        <a href="#" class="h6 author-name">{{$friend->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @endif
                    </div>



                </li>
                @endforeach
                @foreach(Auth::user()->allFriendsNotIn(Auth::user()->allUserChatsOnly()) as $friend)
                <li data-id="{{$friend->id}}" data-from="{{Auth::id()}}" onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/{{$friend->profile_image()}}" class="avatar">
                        <span  data="status" class="icon-status @if($friend->gender =='female') online @else {{$friend->status}} @endif"></span>
                        @if(Auth::user()->unreadFrom($friend->id, true) != 0)
                        <div data-from='{{$friend->id}}' class='label-avatar unread-right bg-orange'>{{Auth::user()->unreadFrom($friend->id, true)}}</div>
                        @endif
                    </div>

                    <div class="author-status">
                        @if(Auth::user()->isAdmin())
                        <a href="#" class="h6 author-name">{{$friend->name()}} - {{Auth::user()->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @else
                        <a href="#" class="h6 author-name">{{$friend->name()}}</a>
                        <span class="status">{{$friend->moto}}</span>
                        @endif
                    </div>



                </li>
                @endforeach
                @endif
            </ul>
            <div id="chat_load_threshold_lg"></div>


        </div>
        <div class="search-friend inline-items">
            <form class="form-group" >
                <input class="form-control" placeholder="{{l('Search Friends...')}}" value="" onkeyup="search_friends(this);" type="text">
            </form>
            <a href="#" class="js-sidebar-open">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>
        </div>

        <a href="/find-friends" class="olympus-chat inline-items">

            <h6 class="olympus-chat-title">{{env('APP_NAME')}}</h6>
            <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
        </a>
    </div>

</div>
<!-- ... end Fixed Sidebar Right-Responsive -->