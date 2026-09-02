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

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap-reboot.css">
	<link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="/Bootstrap/dist/css/bootstrap-grid.css">

	<!-- Main Styles CSS -->
	<link rel="stylesheet" type="text/css" href="/css/main.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">


	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">



</head>
<body>

<input type="hidden" id="auth_id" value="{{Auth::id()}}">
@csrf
<input id="id_call" type="text">
<button id="send_call">Call</button>
<div id="video_container" class="container">
<video id="myvideo" class="col-md-6" muted></video>
</div>

<!-- JS Scripts -->
<script src="/js/jquery-3.2.1.js"></script>
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>
<script src="/js/simplepeer.min.js"></script>
<script src="/assets/js/videochat.js?v=1.0.0"></script>

</body>
</html>