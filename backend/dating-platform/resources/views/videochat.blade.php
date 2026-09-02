<!DOCTYPE html>
<html lang="en">
<head>

    <title>{{$title}} - {{config('app.name')}}</title>

    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Main Font -->
    <script src="/js/webfontloader.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/main.css">

    <script>
        WebFont.load({
            google: {
                families: ['Roboto:300,400,500,700:latin']
            }
        });
    </script>

    <!-- Main Styles CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">


    {{-- <link rel="stylesheet" type="text/css" href="/css/videochat.css?v={{ filemtime(public_path('css/videochat.css')) }}"> --}}
    <link rel="stylesheet" type="text/css" href="/css/videochat.css">



</head>
<body>
    @php
$pusher_key = env('PUSHER_APP_KEY');

@endphp
<script type="text/javascript">

    var pusher_key = '{{$pusher_key}}' ;

</script>
    <div id="video_container" class="container">
        <div class="calling_now">
            <img id="profile_img_call" src="/storage/images/{{$otherUser->profile_image()}}">
            <span class="caller_name">{{$otherUser->name()}}</span>
            <span class="call_state">dialing...</span>
        </div>
        <video id="myvideo" muted="" autoplay playsinline></video>
        <video id="peerVideo" autoplay playsinline></video>
        <span id="call_timer">00:00</span>
        @php
            /*
        @endphp
        <button id="mute_mic" type="button" onclick="mute_mic();"><i class="fas fa-microphone"></i></button>
         <button id="end_call" type="button" onclick="close_call('<?php echo isset($_GET['call_to']) ? $_GET['call_to'] : $_GET['called_by'];?>');"><i class="fas fa-phone-slash"></i></button>
        <button id="remove_video" type="button" onclick="remove_video();"><i class="fas fa-video"></i></button>
        @php
            */
        @endphp
    </div>
        @csrf
        <input type="hidden" id="auth_id" name="auth" value="{{Auth::id()}}">
    <script src="/js/jquery-3.2.1.js"></script>
    <script src="https://js.pusher.com/4.3/pusher.min.js"></script>
    <script src="/js/simplepeer.min.js"></script>
    <script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
    <script src="/assets/js/videochat.js?v=1.0.0"></script>

</body>
</html>
