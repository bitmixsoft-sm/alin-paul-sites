<!-- Fixed Sidebar Left -->
@php
$pages = App\CMS::select('name', 'route')->where('lang', session('lang'))->get();
if(!$pages->count())
{
    $pages = App\CMS::select('name', 'route')->get();
}
@endphp

<div class="fixed-sidebar">
    <div class="fixed-sidebar-left sidebar--small" id="sidebar-left">

        <a href="/newsfeed" class="logo">
            <div class="img-wrap">
                <img src="/img/logo.png" alt="Olympus">
            </div>
        </a>

        <div class="mCustomScrollbar" data-mcs-theme="dark">
            <ul class="left-menu">
                <li>
                    <a href="#" class="js-sidebar-open">
                        <svg class="olymp-menu-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="{{l('OPEN MENU')}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-menu-icon"></use></svg>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                <li>
                    <a href="/admin" target="_blank">
                        <svg class="olymp-manage-widgets-icon left-menu-icon" data-toggle="tooltip" data-placement="right" data-original-title="Administrare"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-manage-widgets-icon"></use></svg>
                    </a>
                </li>
                @endif
                <li class="open_online_users">
                    <a href="#" onclick="FB_Lead();">
                        <svg class="olymp-manage-widgets-icon left-menu-icon" data-toggle="tooltip" data-placement="right" data-original-title="Utilizatori Online"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                    </a>
                </li>
                <li>
                    <a href="/newsfeed" onclick="FB_Lead();">
                        <svg class="olymp-newsfeed-icon left-menu-icon" data-toggle="tooltip" data-placement="right"   data-original-title="{{l('Newsfeed')}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-newsfeed-icon"></use></svg>
                    </a>
                </li>
                <li>
                    <a href="/find-friends">
                        <svg class="olymp-happy-faces-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="{{l('Find Friends')}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-faces-icon"></use></svg>
                    </a>
                </li>
                    @if($pages->count())
                    @foreach($pages as $page)
                    <li>
                        <a href="/pages/{{$page->route}}">
                            <svg class="olymp-blog-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="{{l($page->name)}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-blog-icon"></use></svg>
                        </a>
                    </li>
                    @endforeach
                    @endif
               
            </ul>

        </div>
        @if(Auth::check() && Auth::user()->isAdmin())
        @php
        $online_users = App\User::where('gender', 'male')->where('status', 'online')->orderBy('created_at', 'desc')->take(5)->get();
        @endphp
        <div id="online_users_left">
            <div class="form-control">
                <div class="online_users_left_content">
                    @foreach($online_users as $usr)
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
                    @endforeach
                </div>
                <div id="online_users_left_threshold"></div>
            </div>
        </div>
        @else
        @php
        $online_users = App\User::where('gender', 'female')->where('status', 'online')->orderBy('created_at', 'desc')->take(5)->get();
        @endphp
        <div id="online_users_left">
            <div class="form-control">
                <div class="online_users_left_content">
                    @foreach($online_users as $usr)
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
                    @endforeach
                </div>
                <div id="online_users_left_threshold"></div>
            </div>
        </div>
        @endif
    </div>

    <div class="fixed-sidebar-left sidebar--large" id="sidebar-left-1">
        <a href="/newsfeed" class="logo">
            <div class="img-wrap">
                <img src="/img/logo.png" alt="Olympus">
            </div>
            <div class="title-block">
                <h6 class="logo-title">DATING</h6>
            </div>
        </a>

        <div class="mCustomScrollbar" data-mcs-theme="dark">
            <ul class="left-menu">
                <li>
                    <a href="#" class="js-sidebar-open">
                        <svg class="olymp-close-icon left-menu-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                        <span class="left-menu-title">{{l("Collapse Menu")}}</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                <li>
                    <a href="/admin">
                        <svg class="olymp-manage-widgets-icon left-menu-icon" data-toggle="tooltip" data-placement="right"   data-original-title="Administrare"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-manage-widgets-icon"></use></svg>
                        <span class="left-menu-title">Administrare</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="/newsfeed" onclick="FB_Lead();">
                        <svg class="olymp-newsfeed-icon left-menu-icon" data-toggle="tooltip" data-placement="right"   data-original-title="NEWSFEED"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-newsfeed-icon"></use></svg>
                        <span class="left-menu-title">{{l("Newsfeed")}}</span>
                    </a>
                </li>
                <li>
                    <a href="/find-friends">
                        <svg class="olymp-happy-faces-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="FIND FRIENDS"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-faces-icon"></use></svg>
                        <span class="left-menu-title">{{l("Find Friends")}}</span>
                    </a>
                </li>
                 @auth
                    @if($pages->count())
                    @foreach($pages as $page)
                    <li>
                        <a href="/pages/{{$page->route}}">
                             <svg class="olymp-blog-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="{{l($page->name)}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-blog-icon"></use></svg>
                            <span>{{l($page->name)}}</span>
                        </a>
                    </li>
                    @endforeach
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</div>

<!-- ... end Fixed Sidebar Left -->


<!-- Fixed Sidebar Left -->

<div class="fixed-sidebar fixed-sidebar-responsive">

    <div class="fixed-sidebar-left sidebar--small" id="sidebar-left-responsive">
        <a href="#" class="logo js-sidebar-open">
            <svg class="olymp-menu-icon left-menu-icon color-white"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-menu-icon"></use></svg>
        </a>

    </div>

    <div class="fixed-sidebar-left sidebar--large" id="sidebar-left-1-responsive">
        <a href="#" class="logo">
            <div class="img-wrap">
                <img src="/img/logo.png" alt="Olympus">
            </div>
            <div class="title-block">
                <h6 class="logo-title">{{config('app.name')}}</h6>
            </div>
        </a>
        <div class="mCustomScrollbar" data-mcs-theme="dark">

            <div class="control-block">
                <div class="author-page author vcard inline-items">
                    <div class="author-thumb">
                        @if(Auth::check())
                        <img alt="author" src="/storage/images/{{Auth::user()->profile_image()}}" class="avatar">
                        <span class="icon-status {{Auth::user()->status}}"></span>
                        @else
                        <img alt="author" src="/storage/images/default.png" class="avatar">
                        <span class="icon-status online"></span>
                        @endif
                    </div>
                    @if(Auth::check())
                    <a href="/profile" class="author-name fn">
                        <div class="author-title">
                            {{Auth::user()->name()}} <svg class="olymp-dropdown-arrow-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-dropdown-arrow-icon"></use></svg>
                        </div>
                        <span class="author-subtitle">{{Auth::user()->name()}}</span>
                    </a>
                    @else
                    <a href="/profile" class="author-name fn">
                        <div class="author-title">
                            Guest <svg class="olymp-dropdown-arrow-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-dropdown-arrow-icon"></use></svg>
                        </div>
                        <span class="author-subtitle">Guest</span>
                    </a>
                    @endif
                </div>
            </div>

            <div class="block">
            <div class="float-right pb-3"><a href="#" class="" id="theme-toggler-m" onclick="toggleTheme(); return false;"></a></div>
            </div>

            <div class="ui-block-title ui-block-title-small">
                <h6 class="title">{{l("Quick Links")}}</h6>
            </div>

            <ul class="left-menu">

                <li>
                    <a href="#" class="js-sidebar-open">
                        <svg class="olymp-close-icon left-menu-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                        <span class="left-menu-title">{{l("Collapse Menu")}}</span>
                    </a>
                </li>
                 @if(Auth::check() && Auth::user()->isAdmin())
                 <li>
                    <a href="/admin">
                        <svg class="olymp-manage-widgets-icon left-menu-icon" data-toggle="tooltip" data-placement="right"   data-original-title="Administrare"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-manage-widgets-icon"></use></svg>
                        <span class="left-menu-title">Administrare</span>
                    </a>
                </li>
                @endif
                @auth
                <li>
                    <a href="/newsfeed">
                        <svg class="olymp-newsfeed-icon left-menu-icon" data-toggle="tooltip" data-placement="right"   data-original-title="NEWSFEED"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-newsfeed-icon"></use></svg>
                        <span class="left-menu-title">{{l("Newsfeed")}}</span>
                    </a>
                </li>
                @endauth
                <li>
                    <a href="/find-friends">
                        <svg class="olymp-happy-faces-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="FIND FRIENDS"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-faces-icon"></use></svg>
                        <span class="left-menu-title">{{l("Find Friends")}}</span>
                    </a>
                </li>
                 @auth
                    @if($pages->count())
                    @foreach($pages as $page)
                    <li>
                        <a href="/pages/{{$page->route}}">
                            <svg class="olymp-blog-icon left-menu-icon"  data-toggle="tooltip" data-placement="right"   data-original-title="{{l($page->name)}}"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-blog-icon"></use></svg>
                            <span>{{l($page->name)}}</span>
                        </a>
                    </li>
                    @endforeach
                    @endif
                @endauth
            </ul>
            @auth
            <div class="ui-block-title ui-block-title-small">
                <h6 class="title">{{l("YOUR ACCOUNT")}}</h6>
            </div>

            <ul class="account-settings">
                <li>
                    <a href="/profile-settings">

                        <svg class="olymp-menu-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-menu-icon"></use></svg>

                        <span>{{l("Profile Settings")}}</span>
                    </a>
                </li>
                <li>
                    <a href="/logout">
                        <svg class="olymp-logout-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-logout-icon"></use></svg>

                        <span>{{l("Log Out")}}</span>
                    </a>
                </li>
            </ul>
            @endauth

            @php

                            $langs = App\Lang::get();

                            @endphp

                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{l("Language")}}</h6>
                            </div>
                            <ul class="lang-settings">
                                @foreach($langs as $lang)
                                    <li class="select_lang_responsive">
                                        <a href="/language/{{$lang->code}}">
                                            <span><img src="/storage/lang/{{$lang->code}}.{{$lang->ext}}"></span>
                                            <span>{{$lang->name}} @if(session('lang') == $lang->code) ({{ l('Selected') }}) @endif</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
            @auth
            <div class="ui-block-title ui-block-title-small">
                <h6 class="title">{{l("About")}}</h6>
            </div>
                @php
                $pages = App\CMS::select('name', 'route')->where('lang', session('lang'))->get();
                @endphp
            <ul class="about-olympus">
                @foreach($pages as $page)
                <li>
                    <a href="/pages/{{$page->route}}">
                        <span>{{l($page->name)}}</span>
                    </a>
                </li>
                @endforeach
            </ul>
            @endauth

        </div>
    </div>
</div>

<!-- ... end Fixed Sidebar Left -->