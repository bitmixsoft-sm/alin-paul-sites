<!-- Header-BP -->

<header class="header" id="site-header">

    <div class="page-title">
        <h6>{{$title}}</h6>
    </div>

    <div class="header-content-wrapper">
        <form class="search-bar w-search notification-list friend-requests">
            <div class="form-group with-button">
                <input id="userSearchInput" autocomplete="off" class="form-control" placeholder="{{l('Search here people or pages...')}}" type="text">
                {{-- Mobile-only (see themes/*.css): under aurora/nordic this button also toggles
                     the search input open/closed as a downward dropdown instead of the input
                     always taking up header width - classic's behavior is untouched since the
                     onclick only exists for the other themes. --}}
                <button @if(($activeTheme ?? 'classic') !== 'classic') onclick="toggleThemeSearch(); return false;" @endif>
                    <svg class="olymp-magnifying-glass-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                </button>
                <div class="selectize-dropdown multi form-control js-user-search" style="display:none; width:500px; top:70px; left:0px; visibility:visible;">
                </div>
            </div>
        </form>

        @if(($activeTheme ?? 'classic') === 'classic')
        <a href="/find-friends" class="link-find-friend" onclick="FB_Lead();">{{l("Find Friends")}}</a>
        <a href="/newsfeed" class="link-find-friend">{{l("Newsfeed")}}</a>
        @else
        {{-- Clean, icon-free horizontal nav matching the theme reference design exactly -
             includes the same real links the left sidebar used to show (CMS pages, admin),
             since that sidebar is fully hidden under this theme (see themes/*.css). The
             sidebar's "online users" toggle panel isn't reproduced here since its target
             panel lives inside that now-hidden sidebar - flagged, not silently dropped.
             Shown inline on desktop; collapses into the .theme-nav-toggle hamburger dropdown
             on mobile widths (pure CSS/JS toggle - see themes/*.css and theme-selector.js). --}}
        <a href="#" class="theme-nav-toggle" onclick="toggleThemeNavDropdown(this); return false;" title="{{ l('Menu') }}">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </a>
        <nav class="theme-nav">
            <a href="/find-friends" class="theme-nav-link @if(request()->is('find-friends')) active @endif" onclick="FB_Lead();">{{ l('Find Friends') }}</a>
            <a href="/newsfeed" class="theme-nav-link @if(request()->is('newsfeed')) active @endif">{{ l('Newsfeed') }}</a>
            {{-- Same "open_online_users" class the old sidebar toggle used - the existing
                 click handler in dating.js binds by class, so it works unchanged here too.
                 Its #online_users_left panel (still physically inside the now-hidden
                 sidebar) is pulled back into view via CSS - see themes/*.css. --}}
            <a href="#" class="theme-nav-link open_online_users" onclick="FB_Lead();">{{ l('Online Users') }}</a>
            @php
                $themeNavCmsPages = \App\CMS::select('name', 'route')->where('lang', session('lang'))->get();
                if (! $themeNavCmsPages->count()) {
                    $themeNavCmsPages = \App\CMS::select('name', 'route')->get();
                }
            @endphp
            @foreach($themeNavCmsPages as $themeNavPage)
                <a href="/pages/{{ $themeNavPage->route }}" class="theme-nav-link @if(request()->is('pages/' . $themeNavPage->route)) active @endif">{{ l($themeNavPage->name) }}</a>
            @endforeach
            @if(Auth::check() && Auth::user()->isAdmin())
                <a href="/admin" target="_blank" class="theme-nav-link">{{ l('Admin') }}</a>
            @endif
            {{-- Account/language/logout links - mobile-only (see .theme-nav-mobile-only in
                 themes/*.css). Desktop keeps the profile avatar's own native hover dropdown
                 (components/header/profile-header.blade.php) for these - showing them here too
                 at desktop widths caused the nav row to wrap awkwardly against the credits/
                 packages controls. Grouped into clearly separated sections for the mobile
                 dropdown, since it's a single flat list there. --}}
            @auth
                <div class="theme-nav-section-label theme-nav-mobile-only">{{ l('Account') }}</div>
                <a href="/profile-settings" class="theme-nav-link theme-nav-mobile-only">{{ l('Profile Settings') }}</a>
                <a href="/logout" class="theme-nav-link theme-nav-mobile-only">{{ l('Log Out') }}</a>
                {{-- Custom status ("moto") editor - same feature as the profile avatar's
                     native hover dropdown (components/header/profile-header.blade.php,
                     #moto-change), which is unreachable on mobile once that dropdown is
                     hidden there (see themes/*.css). Reuses the exact same submit handler
                     (storage/app/assets/js/dating.js), which was widened from an ID-based
                     selector (only ever binds once) to a class-based one so both instances
                     of this form work independently. --}}
                <div class="theme-nav-section-label theme-nav-mobile-only">{{ l('Custom Status') }}</div>
                <form class="theme-nav-mobile-only theme-nav-status-form form-group with-button custom-status">
                    @csrf
                    <input name="moto" class="form-control" placeholder="" type="text" value="{{ Auth::user()->moto }}">
                    <button type="submit" class="bg-purple">
                        <svg class="olymp-check-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-check-icon"></use></svg>
                    </button>
                </form>
                <div class="theme-nav-section-label theme-nav-mobile-only">{{ l('Language') }}</div>
                @php $themeNavLangs = \App\Lang::get(); @endphp
                @foreach($themeNavLangs as $themeNavLang)
                    <a href="/language/{{ $themeNavLang->code }}" class="theme-nav-link theme-nav-mobile-only @if(session('lang') == $themeNavLang->code) active @endif">{{ $themeNavLang->name }}</a>
                @endforeach
            @endauth
        </nav>
        @endif

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
            @if(($activeTheme ?? 'classic') === 'classic')
            <span class="link-find-friend">{{l("Credits")}}: <span id="credits_header">{{number_format(Auth::user()->credits, 0, '.', ',')}}</span></span>
            @else
            <span class="theme-credits-pill"><span id="credits_header">{{number_format(Auth::user()->credits, 0, '.', ',')}}</span> {{l("credits")}}</span>
            @endif
            
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
