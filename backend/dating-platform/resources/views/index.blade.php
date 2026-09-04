<!DOCTYPE html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{config('app.name')}}</title>

    <!-- Required meta tags always come first -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Main Font -->
    <script src="/js/webfontloader.min.js"></script>

    <script>
        WebFont.load({
            google: {
                families: ['Roboto:300,400,500,700:latin']
            }
        });
    </script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap-reboot.css">
    <link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap-grid.css">

    <!-- Main Styles CSS -->
    <link rel="stylesheet" type="text/css" href="/css/main.css">
    <link rel="stylesheet" type="text/css" href="/css/fonts.min.css">

    @php
        // This page doesn't extend layouts/layout.blade.php (it's the pre-login landing
        // page, standalone), so it never picked up the theme system that page wires up -
        // the site-wide theme selection (see AdminThemeController / admin/themes.blade.php)
        // had no effect here, leaving this the one page still stuck looking like classic
        // regardless of which theme is active. Mirrors layouts/layout.blade.php's own
        // activeTheme/font-link/theme-css block so this page gets the same treatment.
        $activeTheme = \App\Support\ActiveTheme::current();
    @endphp
    @if($activeTheme !== 'classic')
        @if($activeTheme === 'aurora')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'nordic')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'volt')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'velvet')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'bloom')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Karla:wght@400;500;600;700&family=Red+Hat+Mono&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'binder')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Bungee&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @elseif($activeTheme === 'rosewood')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Jost:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
        @endif
        {{-- Loaded straight from the site css (no /assets/css/style.css base here, this page
             never had one) - these theme files are self-contained (own :root tokens, all
             rules scoped under body.theme-X) so they don't depend on that file being present. --}}
        <link rel="stylesheet" type="text/css" href="/assets/css/themes/{{ $activeTheme }}.css?ver={{ is_file(storage_path('app/assets/css/themes/' . $activeTheme . '.css')) ? filemtime(storage_path('app/assets/css/themes/' . $activeTheme . '.css')) : '1' }}">
    @endif

    @php
        $main_color = App\Settings::where('id', 1)->firstOrFail();
        $main_color_rgb = App\Settings::where('id', 5)->firstOrFail();
        $hover_color = App\Settings::where('id', 2)->firstOrFail();
        $main_img = App\Settings::where('id', 7)->firstOrFail();
    @endphp

    <style type="text/css">
        :root{
            --main-color: {{$main_color->value}};
            --main-color-rgb: {{$main_color_rgb->value}};
            --hover-color: {{$hover_color->value}};
        }
        .landing-page .content-bg-wrap {
            background-image: url("{{$main_img->value}}");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        /* Same look as #theme-toggler in storage/app/assets/css/style.css (which this page
           doesn't load), positioned for this page's own transparent, white-text header. */
        .theme-toggler-landing {
            float: right;
            margin-top: 18px;
            margin-left: 24px;
            font-size: 22px;
            line-height: 1;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
        .theme-toggler-landing:hover {
            opacity: .8;
        }
    </style>

    <link id="dark-theme-style" rel="stylesheet" />


</head>

<body class="landing-page theme-{{ $activeTheme }}">


<!-- Preloader -->

<div id="hellopreloader">
    <div class="preloader">
        <svg width="45" height="45" stroke="#fff">
            <g fill="none" fill-rule="evenodd" stroke-width="2" transform="translate(1 1)">
                <circle cx="22" cy="22" r="6" stroke="none">
                    <animate attributeName="r" begin="1.5s" calcMode="linear" dur="3s" repeatCount="indefinite" values="6;22"/>
                    <animate attributeName="stroke-opacity" begin="1.5s" calcMode="linear" dur="3s" repeatCount="indefinite" values="1;0"/>
                    <animate attributeName="stroke-width" begin="1.5s" calcMode="linear" dur="3s" repeatCount="indefinite" values="2;0"/>
                </circle>
                <circle cx="22" cy="22" r="6" stroke="none">
                    <animate attributeName="r" begin="3s" calcMode="linear" dur="3s" repeatCount="indefinite" values="6;22"/>
                    <animate attributeName="stroke-opacity" begin="3s" calcMode="linear" dur="3s" repeatCount="indefinite" values="1;0"/>
                    <animate attributeName="stroke-width" begin="3s" calcMode="linear" dur="3s" repeatCount="indefinite" values="2;0"/>
                </circle>
                <circle cx="22" cy="22" r="8">
                    <animate attributeName="r" begin="0s" calcMode="linear" dur="1.5s" repeatCount="indefinite" values="6;1;2;3;4;5;6"/>
                </circle>
            </g>
        </svg>

        <div class="text">{{l("Loading ...")}}</div>
    </div>
</div>

<div class="content-bg-wrap"></div>
<!-- ... end Preloader

<div class="video_content_index">
<video id="index_bg_video" loop autoplay>
  <source src="/videos/bg_index.mp4" type="video/mp4">
</video>
</div>
<div id="video_overlay"></div>-->


<!-- Header Standard Landing  -->

<div class="header--standard header--standard-landing" id="header--standard">
    <div class="container">
        <div class="header--standard-wrap">

            <a href="#" class="logo">
                <div class="/img-wrap">
                    <img src="/img/logo.png" alt="Olympus">
                    <img src="/img/logo-colored-small.png" alt="Olympus" class="logo-colored">
                </div>
                <div class="title-block">
                    <h6 class="logo-title">{{config('app.name')}}</h6>
                    <div class="sub-title">Get in touch with people</div>
                </div>
            </a>

            {{-- Light/dark MODE switch (not the theme picker - that's admin-only). Shares the
                 same storage/app/assets/js/theme-selector.js and localStorage key every other
                 page uses, so a preference set here or elsewhere on the site stays in sync.
                 #theme-toggler-m is a hidden stub, not a real mobile control - the script
                 unconditionally writes to both ids (that's how the header/header-responsive
                 split works elsewhere) and throws if either is missing, which would silently
                 break the toggle entirely on this page. --}}
            <a href="#" id="theme-toggler" class="theme-toggler-landing" onclick="toggleTheme(); return false;" title="{{ l('Toggle dark/light mode') }}"></a>
            <a href="#" id="theme-toggler-m" style="display:none;" onclick="toggleTheme(); return false;"></a>
        </div>
    </div>
</div>

<!-- ... end Header Standard Landing  -->
<div class="header-spacer--standard"></div>

<div class="container">
    <div class="row display-flex">
        <div class="col col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

        </div>

        <div class="col col-xl-5 col-lg-6 col-md-12 col-sm-12 col-12">

            <!-- Login-Registration Form  -->

            @php
                // A failed login attempt puts its error under the 'email' key too, so the
                // only reliable way to tell it apart from a failed registration (same key)
                // is that only the registration form ever submits 'firstname'.
                $loginTabActive = $errors->any() && session()->hasOldInput('email') && ! session()->hasOldInput('firstname');
            @endphp
            <div class="registration-login-form @if(config('services.unified_login.enabled')) unified @endif">
@php
$pages = App\CMS::select('name', 'route')->where('lang', session('lang'))->get();
if(!$pages->count())
{
    $pages = App\CMS::select('name', 'route')->get();
}
@endphp
            @if(config('services.unified_login.enabled'))
                {{-- No tabs to pick between - a single email decides login vs. register for
                     the visitor. Keeps the same footer links (Find Friends, CMS pages) the old
                     Register tab had, just no longer duplicated per-tab. --}}
                <div class="title h6">{{ l('Sign in or sign up') }}</div>
                <div class="content">
                    @include('components.unified-auth-form', ['unifiedAuthId' => 'unified-auth-landing', 'showForgotPassword' => true])
                    <p class="text-center"><a href="/find-friends">{{l("Find Friends")}}</a></p>
                    @if($pages->count())
                    <p class="text-center pt-3">
                        @foreach($pages as $page)
                            <span class="px-2"><a href="/pages/{{$page->route}}">{{l($page->name)}}</a></span>
                        @endforeach
                    </p>
                    @endif
                </div>
            @else
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $loginTabActive ? '' : 'active' }}" data-toggle="tab" href="#home" role="tab">
                            <span class="display_mobile tab_head">Register</span>
                            <svg class="olymp-register-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-register-icon"></use></svg>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $loginTabActive ? 'active' : '' }}" data-toggle="tab" href="#profile" role="tab">
                            <span class="display_mobile tab_head">Login</span>
                            <svg class="olymp-login-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-login-icon"></use></svg>
                        </a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane {{ $loginTabActive ? '' : 'active' }}" id="home" role="tabpanel" data-mh="log-tab">
                        <div class="title h6">{{l("Register to")}} {{config('app.name')}}</div>
                        <form class="content" method="POST" action="{{ route('register') }}">
                        @include('components.google-auth-button')
                        @csrf
                            <div class="row">
                                <div class="col col-12 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group label-floating {{$errors->has('firstname') ? 'has-error' : ''}}">
                                        <label class="control-label">{{l("First Name")}}</label>
                                        <input name="firstname" required class="form-control {{$errors->has('firstname') ? 'form-control-danger' : ''}}" placeholder="" type="text" value="{{ old('firstname') }}">
                                        @if ($errors->has('firstname'))
                                            <div class="text-danger">{{ $errors->first('firstname') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col col-12 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group label-floating {{$errors->has('lastname') ? 'has-error' : ''}}">
                                        <label class="control-label">{{l("Last Name")}}</label>
                                        <input name="lastname" required class="form-control {{$errors->has('lastname') ? 'form-control-danger' : ''}}" placeholder="" type="text" value="{{ old('lastname') }}">
                                        @if ($errors->has('lastname'))
                                            <div class="text-danger">{{ $errors->first('lastname') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group label-floating {{$errors->has('email') ? 'has-error' : ''}}">
                                        <label class="control-label">{{l("Your Email")}}</label>
                                        <input name="email" required class="form-control {{$errors->has('email') ? 'form-control-danger' : ''}}" placeholder="" type="email" value="{{ old('email') }}">
                                        @if ($errors->has('email'))
                                            <div class="text-danger">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group label-floating {{$errors->has('password') ? 'has-error' : ''}}">
                                        <label class="control-label">{{l("Password")}}</label>
                                        <input name="password" required class="form-control {{$errors->has('password') ? 'form-control-danger' : ''}}" placeholder="" type="password">
                                        @if ($errors->has('password'))
                                            <div class="text-danger">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>

                                    <div class="remember">
                                        <div class="checkbox">
                                            <label>
                                                <input name="terms" checked type="checkbox">
                                                {{l("I accept the")}} <a href="/pages/terms-of-use">{{l("Terms and Conditions")}}</a> {{l("of the website")}}
                                            </label>
                                        </div>
                                        @if ($errors->has('terms'))
                                            <div class="text-danger">
                                                {{ $errors->first('terms') }}
                                            </div>
                                        @endif
                                    </div>

                                    <input type="submit" class="btn btn-purple btn-lg full-width" value="Complete Registration!" onclick="FB_Trial();">
                                    <div class="or"></div>


                                    <p>{{l("Already registered?")}} <a data-open="login" href="#profile">{{l("Log In!")}}</a></p>
                                    <div class="or"></div>
                                    <p class="text-center"><a href="/find-friends">{{l("Find Friends")}}</a></p>
                                    <p class="text-center pt-3">
                                        @if($pages->count())
                                        @foreach($pages as $page)
                                            <span class="px-2"><a href="/pages/{{$page->route}}">{{l($page->name)}}</a></span>
                                        @endforeach
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane {{ $loginTabActive ? 'active' : '' }}" id="profile" role="tabpanel" data-mh="log-tab">
                        <div class="title h6">{{l("Login to your Account")}}</div>
                        <form class="content" method="POST" action="{{ route('login') }}">
                            @include('components.google-auth-button')
                            @csrf
                            <div class="row">
                                <div class="col col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group label-floating {{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label class="control-label">{{l("Your Email")}}</label>
                                        <input name="email" class="form-control" placeholder="" type="email" value="{{ $email ?? old('email') }}">
                                        @if ($errors->has('email'))
                                            <div class="text-danger">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group label-floating {{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label class="control-label">{{l("Your Password")}}</label>
                                        <input name="password" class="form-control" placeholder="" type="password">
                                        @if ($errors->has('password'))
                                            <div class="text-danger">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>

                                    <div class="remember">

                                        <div class="checkbox">
                                            <label>
                                                <input name="remember" {{ old('remember') ? 'checked' : '' }} type="checkbox">
                                                {{l("Remember Me")}}
                                            </label>
                                        </div>
                                        <a href="#" class="forgot" data-toggle="modal" data-target="#restore-password">{{l("Forgot my Password")}}</a>
                                    </div>

                                    <input type="submit" class="btn btn-lg btn-primary full-width" value="{{l('Login')}}" onclick="FB_Lead();">

                                    <div class="or"></div>


                                    <p>{{l("Don’t you have an account?")}} <a data-open="register" href="#home">{{l("Register Now!")}}</a> {{l("it’s really simple and you can start enjoing all the benefits!")}}</p>
                                    <div class="or"></div>
                                    <p class="text-center"><a href="/find-friends">{{l("Find Friends")}}</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            </div>

            <!-- ... end Login-Registration Form  -->       </div>
    </div>
</div>

<!-- Window-popup Restore Password -->

<div class="modal fade" id="restore-password" tabindex="-1" role="dialog" aria-labelledby="restore-password" aria-hidden="true">
    <div class="modal-dialog window-popup restore-password-popup" role="document">
        <div class="modal-content">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>

            <div class="modal-header">
                <h6 class="title">{{l("Restore your Password")}}</h6>
            </div>

            <div class="modal-body">
                @if(!isset($token))
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <p>{{l("Enter your email and click the send code button. You’ll receive a code in your email. Please use that code below to change the old password for a new one.")}}
                    </p>
                    <div class="form-group label-floating">
                        <label class="control-label">{{l("Your Email")}}</label>
                        <input name="email" class="form-control" placeholder="" type="email" value="">
                    </div>

                    <button class="btn btn-purple btn-lg full-width">{{l("Send me the Code")}}</button>

                </form>
                @endif
                @isset($token)
                <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{$token}}">
                    <div class="form-group label-floating @if($errors->has('email')) has-error @endif">
                        <label class="control-label">{{l("Your Email")}}</label>
                        <input id="email" name="email" autocomplete="off" class="form-control" placeholder="" type="email" required value="{{ $email ?? old('email') }}">
                    </div>
                    <div class="form-group label-floating @if($errors->has('password')) has-error @endif">
                        <label class="control-label">{{l("Enter the New Password")}}</label>
                        <input id="password" class="form-control" autocomplete="off" placeholder="" type="password" name="password" required value="">
                    </div>
                    <div class="form-group label-floating @if($errors->has('password')) has-error @endif">
                        <label class="control-label">{{l("Confirm New Password")}}</label>
                        <input id="password-confirmation" class="form-control" autocomplete="off" placeholder="" type="password" name="password_confirmation" required value="">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg full-width">{{l("Change your Password!")}}</button>
                </form>
                @endisset
            </div>
        </div>
    </div>
</div>

<!-- ... end Window-popup Restore Password -->


<!-- Window Popup Main Search -->

<div class="modal fade" id="main-popup-search" tabindex="-1" role="dialog" aria-labelledby="main-popup-search" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered window-popup main-popup-search" role="document">
        <div class="modal-content">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>
            <div class="modal-body">
                <form class="form-inline search-form" method="post">
                    <div class="form-group label-floating">
                        <label class="control-label">{{l("What are you looking for?")}}</label>
                        <input class="form-control bg-white" placeholder="" type="text" value="">
                    </div>

                    <button class="btn btn-purple btn-lg">{{l("Search")}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ... end Window Popup Main Search -->


<!-- JS Scripts -->
<script src="/js/jquery-3.2.1.js"></script>
<script src="/js/jquery.appear.js"></script>
<script src="/js/jquery.mousewheel.js"></script>
<script src="/js/perfect-scrollbar.js"></script>
<script src="/js/jquery.matchHeight.js"></script>
<script src="/js/svgxuse.js"></script>
<script src="/js/imagesloaded.pkgd.js"></script>
<script src="/js/Headroom.js"></script>
<script src="/js/velocity.js"></script>
<script src="/js/ScrollMagic.js"></script>
<script src="/js/jquery.waypoints.js"></script>
<script src="/js/jquery.countTo.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/material.min.js"></script>
<script src="/js/bootstrap-select.js"></script>
<script src="/js/smooth-scroll.js"></script>
<script src="/js/selectize.js"></script>
<script src="/js/swiper.jquery.js"></script>
<script src="/js/moment.js"></script>
<script src="/js/daterangepicker.js"></script>
<script src="/js/simplecalendar.js"></script>
<script src="/js/fullcalendar.js"></script>
<script src="/js/isotope.pkgd.js"></script>
<script src="/js/ajax-pagination.js"></script>
<script src="/js/Chart.js"></script>
<script src="/js/chartjs-plugin-deferred.js"></script>
<script src="/js/circle-progress.js"></script>
<script src="/js/loader.js"></script>
<script src="/js/run-chart.js"></script>
<script src="/js/jquery.magnific-popup.js"></script>
<script src="/js/jquery.gifplayer.js"></script>
<script src="/js/mediaelement-and-player.js"></script>
<script src="/js/mediaelement-playlist-plugin.min.js"></script>
<script src="/js/ion.rangeSlider.js"></script>

<script src="/js/base-init.js"></script>
<script defer src="/fonts/fontawesome-all.js"></script>

<script src="/Bootstrap/dist/js/bootstrap.bundle.js"></script>

<script src="/js/dating.js"></script>
<script src="/js/fb-tracking.js"></script>
<script src="/assets/js/theme-selector.js"></script>
@if(config('services.unified_login.enabled'))
    <script src="/js/unified-auth-form.js"></script>
@endif

<script type="text/javascript">
    @isset($token)
    $('#restore-password').modal();
    @endisset
    @if(Request::get('page') == 'login')
    $('.nav-item a[href="#profile"]').tab('show');
    @endif
    $(document).ready(function() {
         if ($('#index_bg_video').length>0) {
            $('#index_bg_video').prop('muted',true).play();
         }
    });
</script>
@include('popup_adult_alert')
</body>
</html>
