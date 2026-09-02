@auth
@if(Auth::user()->isAdmin())
<!DOCTYPE html>
<html lang="ro">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="au theme template">
    <meta name="author" content="Hau Nguyen">
    <meta name="keywords" content="au theme template">

    <!-- Title Page-->
    <title>{{$on_page}} - Dating</title>

    <!-- Fontfaces CSS-->
    <link href="/admin_assets/css/font-face.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="/admin_assets/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="/css/main.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link href="/admin_assets/vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="/admin_assets/vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="/admin_assets/css/theme.css" rel="stylesheet" media="all">
   	<link href="/admin_assets/dropify/css/dropify.min.css" rel="stylesheet">

</head>
    <body class="animsition">
        <div class="page-wrapper">
            @csrf
            @include('admin.components.navbar')
            @include('admin.components.sidebar')
            @yield('content') 
            @include('components.sidebar-right')
            @include('components.chat')
        </div>

    <!-- Jquery JS-->
    @php
    $pusher_key = env('PUSHER_APP_KEY');

    @endphp
    <script type="text/javascript">

        var pusher_key = '{{$pusher_key}}' ;

    </script>

    <script src="/admin_assets/vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="/admin_assets/vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="/admin_assets/vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="/admin_assets/vendor/slick/slick.min.js">
    </script>
    <script src="/admin_assets/vendor/wow/wow.min.js"></script>
    <script src="/admin_assets/vendor/animsition/animsition.min.js"></script>
    <script src="/admin_assets/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="/admin_assets/vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="/admin_assets/vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="/admin_assets/vendor/circle-progress/circle-progress.min.js"></script>
    <script src="/admin_assets/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/admin_assets/vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="/admin_assets/vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="/admin_assets/js/main.js"></script>
    <script src="https://js.pusher.com/4.3/pusher.min.js"></script>
    <script src="/assets/js/online.js?v=1.0.1"></script>
    <script src="/assets/js/dating.js?v=1.0.0"></script>
    <!-- Hotjar Tracking Code for https://chit-chat.me -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:1882808,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
<script src=""></script>
  <script src="/admin_assets/dropify/js/dropify.min.js"></script>
@yield('js-vupload-script')
</body>

</html>
<!-- end document-->
@endif
@endauth