<!-- Header-BP -->

<header class="header" id="site-header">

    <div class="page-title">
        <h6>{{$title}}</h6>
    </div>

    <div class="header-content-wrapper">
        <form class="search-bar w-search notification-list friend-requests">
            <div class="form-group with-button">
                <input id="userSearchInput" autocomplete="off" class="form-control" placeholder="{{l('Search here people or pages...')}}" type="text">
                <button>
                    <svg class="olymp-magnifying-glass-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                </button>
                <div class="selectize-dropdown multi form-control js-user-search" style="display:none; width:500px; top:70px; left:0px; visibility:visible;">
                </div>
            </div>
        </form>

        <a href="/find-friends" class="link-find-friend" onclick="FB_Lead();">{{l("Find Friends")}}</a>
        <a href="/newsfeed" class="link-find-friend">{{l("Newsfeed")}}</a>
	    
        <a href="#" class="" id="theme-toggler" onclick="toggleTheme(); return false;"></a>
        
        <div class="control-block">
            @auth
            <a href="/packages" class="link-find-friend packages-top" onclick="FB_Lead();">{{l("Packages")}}
                <div id="discount_top" class="badge flashit @if(!Auth::user()->hasDiscount()) no_display @endif">
                  <span>@if(Auth::user()->hasDiscount()) -{{Auth::user()->getDiscount()->value}}% @endif</span>
                </div>   
            </a>
           {{--  <a href="/roulette" class="link-find-friend packages-top" style="margin-left:10px;padding:1px;">
                <img src = "/svg/roulette.svg" alt="Roulette" style="width:35px;height:35px;"/>
            </a> --}}
            <span class="link-find-friend">{{l("Credits")}}: <span id="credits_header">{{number_format(Auth::user()->credits, 0, '.', ',')}}</span></span>
            
            @include('components.header.friend-requests-header')

            @include('components.header.chat-header')

            @include('components.header.notifications-header')

            @endauth

            @include('components.header.profile-header')

        </div>
    </div>

</header>

<!-- ... end Header-BP -->
    @include('components.header-responsive')
