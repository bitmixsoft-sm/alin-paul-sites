@if(Auth::check())
    @php
    $bypass_ban = false;
    @endphp
@else
    @php
    $bypass_ban = true;
    redirect_autoregister_fake();
    @endphp
@endif
@if(Auth::check() && Auth::user()->email_verified_at != null)
    @php
    die('Verify your email to continue!');
    @endphp
@endif
@if(Auth::check() && Auth::user()->banned == 'no' || $bypass_ban)
@php
    // Admin-switchable site skin (see AdminThemeController / resources/admin/themes.blade.php).
    // "classic" is the current, untouched design - no extra CSS loads and no theme-* class
    // does anything for it, so it stays byte-for-byte identical to before this feature existed.
    // NOTE: Blade's "extends" directive renders the child view's sections before this layout
    // runs, so any page that needs $activeTheme inside its own content section (not just here,
    // or things this layout includes) must call \App\Support\ActiveTheme::current() itself too
    // - see find_friends.blade.php.
    $activeTheme = \App\Support\ActiveTheme::current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>

    <title>{{$title}} - {{config('app.name')}}</title>

    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <link rel="stylesheet" type="text/css" href="/css/main.css?version=2">
    <link rel="stylesheet" type="text/css" href="/css/croppie.css">
    <link rel="stylesheet" type="text/css" href="/css/fonts.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="https://cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css?ver={{ is_file(storage_path('app/assets/css/style.css')) ? filemtime(storage_path('app/assets/css/style.css')) : '1' }}">

    @if($activeTheme !== 'classic')
        {{-- Theme CSS is purely additive - loaded AFTER the base stylesheet, only overrides
             it (scoped under body.theme-{{ $activeTheme }}), never replaces it. --}}
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
        @endif
        <link rel="stylesheet" type="text/css" href="/assets/css/themes/{{ $activeTheme }}.css?ver={{ is_file(storage_path('app/assets/css/themes/' . $activeTheme . '.css')) ? filemtime(storage_path('app/assets/css/themes/' . $activeTheme . '.css')) : '1' }}">
    @endif

    @php
        $main_color = App\Settings::where('id', 1)->firstOrFail();
        $main_color_rgb = App\Settings::where('id', 5)->firstOrFail();
        $hover_color = App\Settings::where('id', 2)->firstOrFail();
        $bg_color = App\Settings::where('id', 6)->firstOrFail();
    @endphp

    <style type="text/css">
        :root{
            --main-color: {{$main_color->value}};
            --main-color-rgb: {{$main_color_rgb->value}};
            --hover-color: {{$hover_color->value}};
            --bg-color: {{$bg_color->value}};
        }
    </style>

    <link id="dark-theme-style" rel="stylesheet" />

</head>

<body class="theme-{{ $activeTheme }}" @if((Route::currentRouteName() === 'profile' || Route::currentRouteName() === 'user_profile') && !empty($user->background_image)) style="background-image: url({{ asset('storage/' . $user->background_image) }}); background-repeat: no-repeat; background-size: cover;" @endif>
<!-- Legacy hidden CSRF field: online.js/dating.js (and the Pusher presence-channel auth
     they set up) still read the token via $('input[name="_token"]').val()' instead of the
     <meta name="csrf-token"> tag above - without this, those requests (e.g. /pusher/auth)
     get rejected and users never register as "online". -->
<input type="hidden" name="_token" value="{{ csrf_token() }}">
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

<!-- ... end Preloader -->
@auth
    @if(Auth::user()->isAdmin())
        @include('components.theme-quickswitch')
    @endif
@endauth
@include('components.sidebar-left')
@include('components.sidebar-right')
@include('components.header')
@auth
<div class="upgrade-mobile">
    <div id="discount_top_mobile" class="badge flashit @if(!Auth::user()->hasDiscount()) no_display @endif">
        <span>@if(Auth::user()->hasDiscount()) -{{Auth::user()->getDiscount()->value}}% @endif</span>
    </div>
    <a href="/packages" class="btn btn-primary btn-lg full-width" onclick="FB_Lead();">{{l("Packages")}}</a>
</div>
@endauth
<div id="main-wrapper-content">
@yield('content')
</div>
@auth
@include('components.chat')
@endauth
<div id="livefeed_container">
    <span id="livefeed_close">x</span>
    <a id="livefeed_img_link" href=""><img id="livefeed_img" src=""></a>
    <a id="livefeed_name" href=""></a>
    <span id="livefeed_text1"> purchased a</span>
    <a id="livefeed_pack" href=""></a>
    <span id="livefeed_text2"> subscription!</span>
    <div id="livefeed_time_container">
        <span id="livefeed_time"></span><span> ago</span>
    </div>
</div>
<!-- JS Scripts -->
@php
$pusher_key = env('PUSHER_APP_KEY');
$pixel_id = env('FACEBOOK_PIXEL_ID');

@endphp
<input id="timezone" type="hidden" value="{{date_default_timezone_get()}}">
<script type="text/javascript">

    var pusher_key = '{{$pusher_key}}' ;

</script>
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
<script src="/js/user-activity.js"></script>
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
<!--<script src="/js/Chart.js"></script>
<script src="/js/chartjs-plugin-deferred.js"></script>-->
<script src="/js/circle-progress.js"></script>
<script src="/js/loader.js"></script>
<script src="/js/run-chart.js"></script>
<script src="/js/jquery.magnific-popup.js"></script>
<script src="/js/jquery.gifplayer.js"></script>
<script src="/js/mediaelement-and-player.js"></script>
<script src="/js/mediaelement-playlist-plugin.min.js"></script>
<script src="/js/ion.rangeSlider.js"></script>

<script src="/js/base-init.js"></script>
<!--<script defer src="/fonts/fontawesome-all.js"></script>-->
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>
<script src="/Bootstrap/dist/js/bootstrap.bundle.js"></script>
<script src="/js/croppie.min.js"></script>
<script src="/assets/js/online.js?v=1.0.1"></script>
<script src="/assets/js/dating.js?v=1.0.0"></script>
@if ($_SERVER['REMOTE_ADDR'] == '82.78.230.101' || $_SERVER['REMOTE_ADDR'] == '86.125.60.125')
<script src="/js/videochat2__NEW.js?ver={{ is_file(public_path('js/videochat2__NEW.js')) ? filemtime(public_path('js/videochat2__NEW.js')) : '1' }}"></script>
<script src="/js/simplepeer_9.11.0.min.js"></script>
@else
<script src="/js/videochat2.js?ver={{ is_file(public_path('js/videochat2.js')) ? filemtime(public_path('js/videochat2.js')) : '1' }}"></script>
<script src="/js/simplepeer.min.js"></script>
@endif
<script src="/js/fb-tracking.js"></script>
@yield('scripts')
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-171454090-1">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-171454090-1');
</script>
@php
    // Simple admin on/off switch for the TikTok Pixel (marketing ask: promote the site via
    // TikTok ads). Reuses the existing generic key/value `settings` table/admin page (see
    // resources/views/admin/settings.blade.php) - looked up by name, not id, so it never
    // collides with that page's numeric-id-keyed rows. Purely passive/analytics - safe to
    // leave switched off until an actual TikTok ad campaign/Pixel ID exists.
    $tiktokPixelActive = \App\Settings::where('name', 'TIKTOK_PIXEL_ACTIVE')->value('value') === 'yes';
    $tiktokPixelId = trim((string) \App\Settings::where('name', 'TIKTOK_PIXEL_ID')->value('value'));
@endphp
@if($tiktokPixelActive && $tiktokPixelId !== '')
<!-- TikTok Pixel Code -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<e.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=i+"?sdkid="+e+"&lib="+t;var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(a,s)};
  ttq.load({{ json_encode($tiktokPixelId) }});
  ttq.page();
}(window, document, 'ttq');
</script>
<!-- End TikTok Pixel Code -->
@php
    // Fired once, right after the registration/payment flow lands the user back on a page
    // (see RegisterController::create() for the session flash, and PaymentsController@accepted
    // / PaymentsStripeController's success redirect for the payment=accepted&order=ID pattern).
    // NOTE: CCBill/CentralPay confirm purchases via an async server-to-server webhook instead
    // of a client-facing redirect, so this won't fire for those - only for the direct/PayPal
    // and Stripe checkout flows, which both converge on the same "payment=accepted" redirect.
    $tiktokJustRegistered = session('tiktok_track_registration', false);
    $tiktokPurchaseOrder = null;
    if (request('payment') === 'accepted' && request('order')) {
        $tiktokPurchaseOrder = \App\Order::find(request('order'));
    }
@endphp
@if($tiktokJustRegistered)
<script>ttq.track('CompleteRegistration');</script>
@endif
@if($tiktokPurchaseOrder)
<script>
ttq.track('CompletePayment', {
    value: {{ json_encode((float) ($tiktokPurchaseOrder->package() ? $tiktokPurchaseOrder->package()->price : 0)) }},
    currency: 'EUR'
});
</script>
@endif
@endif
@guest
<div class="auth_modals">
        <!-- Modal -->
<div class="modal fade" id="login-form-popup" tabindex="-1" role="dialog" aria-labelledby="login-form-popupLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                        <h6 class="title">{{l("Login")}}</h6>
                    </div>

                    <div class="modal-body">
                        <form class="form" method="POST" action="{{ route('login') }}">
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
                                    </div>

                                    <input type="submit" class="btn btn-lg btn-primary full-width" value="{{l('Login')}}" onclick="FB_Lead();">

                                    <div class="or"></div>


                                    <p>{{l("Don’t you have an account?")}} <a data-dismiss="modal" data-toggle="modal" data-target="#register-form-popup" href="#">{{l("Register Now!")}}</a> {{l("it’s really simple and you can start enjoing all the benefits!")}}</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
  </div>
        </div>

@if ($errors->any() && session()->hasOldInput('email') && ! session()->hasOldInput('firstname'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#login-form-popup').modal('show');
        });
    </script>
@endif

<!-- Modal -->
<div class="modal fade" id="register-form-popup" tabindex="-1" role="dialog" aria-labelledby="register-form-popupLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                        <h6 class="title">{{l("Register")}}</h6>
                    </div>

                    <div class="modal-body">
                        <form class="form" method="POST" action="{{ route('register') }}">
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
                                                <input name="terms" type="checkbox">
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


                                    <p>{{l("Already registered?")}} <a data-dismiss="modal" data-toggle="modal" data-target="#login-form-popup" href="#">{{l("Log In!")}}</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
  </div>
</div>
</div>

@if ($errors->any() && session()->hasOldInput('firstname'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#register-form-popup').modal('show');
        });
    </script>
@endif
@endguest
@auth
    @if(isset($_COOKIE['autoregister']) || isset($_COOKIE['autoregister_fake']))
        <!-- Modal -->
<div class="modal fade" id="complete-register-form-popup" tabindex="-1" role="dialog" aria-labelledby="complete-register-form-popupLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                        <h6 class="title">{{l("Finish your registration")}}</h6>
                    </div>

                    <div class="modal-body">
                        <form class="form" method="POST" action="@if(isset($_COOKIE['autoregister_fake'])) {{ route('complete_autoregister_fake') }} @else {{ route('complete_autoregister') }} @endif">
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
                                    @if(isset($_COOKIE['autoregister_fake']))
                                        <div class="form-group label-floating {{$errors->has('email') ? 'has-error' : ''}}">
                                            <label class="control-label">{{l("Email")}}</label>
                                            <input name="email" required class="form-control {{$errors->has('email') ? 'form-control-danger' : ''}}" placeholder="" type="email" value="{{ old('email') }}">
                                            @if ($errors->has('email'))
                                                <div class="text-danger">{{ $errors->first('email') }}</div>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="form-group label-floating {{$errors->has('password') ? 'has-error' : ''}}">
                                        <label class="control-label">{{l("Password")}}</label>
                                        <input name="password" required class="form-control {{$errors->has('password') ? 'form-control-danger' : ''}}" placeholder="" type="password">
                                        @if ($errors->has('password'))
                                            <div class="text-danger">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>


                                    <input type="submit" class="btn btn-purple btn-lg full-width" value="Complete Registration!" onclick="FB_Trial();">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
  </div>
</div>
    @if ($errors->any() && session()->hasOldInput('firstname'))
        <script type="text/javascript">
            $('#complete-register-form-popup').modal('show');
        </script>
    @elseif(isset($_COOKIE['autoregister_fake']))
        <script type="text/javascript">
            setTimeout(function() {
                $('#complete-register-form-popup').modal();
            }, 20000);
        </script>
    @elseif(isset($_COOKIE['autoregister']))
        <script type="text/javascript">
            setTimeout(function() {
                $('#complete-register-form-popup').modal();
            }, 7500);
        </script>
    @endif
    @endif

    @if(isset($_GET['open_chat']))

        <script type="text/javascript">
            var open_chat_id = {{$_GET['open_chat']}};
            chat_open(0,0,open_chat_id);
        </script>

    @endif

@endauth
<script src="/assets/js/theme-selector.js"></script>

@php
    /*
@endphp
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pure.js/2.82/pure.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.textcomplete/1.8.5/jquery.textcomplete.min.js"></script>
<script src="https://cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.js"></script>

<script>
$(document).ready(function() {
$(".chatbox").emojioneArea({
     inline: true,
  });
});
</script>
@php
    */
@endphp

</body>
</html>
@else
@php
        header("Location: " . URL::to('/logout'), true, 302);
        exit();
@endphp
@endif
