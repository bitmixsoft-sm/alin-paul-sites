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
	<link rel="stylesheet" type="text/css" href="/css/fonts.min.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">


	<link rel="stylesheet" type="text/css" href="/css/game.css?version=1">

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



</head>
<body>
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

<div id="gameBackground"></div>
<div id="game">
	<div class="container">
		<div id="header" class="row">
			<div class="col col-md-12">
				<h1 data-step="1">{{l("Wanna play a game?")}}</h1>
				<h1 data-step="2">{{l("Great choice!")}}</h1>
				<h1 data-step="3">{{l("Last one...")}}</h1>
				<h2 data-step="1">{{l("Choose your favorite model:")}}</h2>
				<h2 data-step="2">{{l("Choose your favorite photo:")}}</h2>
				<h2 data-step="3">{{l("Choose your favorite video:")}}</h2>
				<h1 id="final_step_header">{{l("You got a perfect match!")}}</h1>
			</div>
		</div>
		<div class="step row" data-step="1">
			@php
			$x=1;
			@endphp
			@foreach($user as $usr)
			<div class="optionContainer col-md-4" onclick="show_step(2,{{$usr->id}})" >
				
				<div class="option images" style="background: url('/storage/images/{{$usr->images->take(1)->first()->name}}');">

					<div class="optionContent">
						
					</div>

					<div class="optionInfo">
						<span>{{$x}}. {{$usr->name()}}</span>
					</div>

				</div>

			</div>
			@php
			$x++;
			@endphp
			@endforeach
		</div>
		@foreach($user as $usr)
		@php
			$y=1;
		@endphp
		<div class="step row" data-step="2" data-id="{{$usr->id}}">
			@foreach($usr->images()->skip(1)->take(3)->get() as $img)
			<div class="optionContainer col-md-4" onclick="show_step(3)">
				
				<div class="option images" style="background: url('/storage/images/{{$img->name}}');">

					<div class="optionContent">
						
					</div>

					<div class="optionInfo">
						<span>{{$y}}.</span>
					</div>

				</div>

			</div>
			@php
			$y++;
			@endphp
			@endforeach
		</div>
		@endforeach
		<div class="step row" data-step="3">
			<div class="optionContainer col-md-4" onclick="final_step()" >
				
				<div class="option video">

					<div class="optionContent">
						<video loop muted preload="none">
							<source src="https://chit-chat.me/storage/videos/1542057975_499883063_1.mp4">
						</video>
					</div>

					<div class="optionInfo">
						<span>1</span>
					</div>

				</div>

			</div>
			<div class="optionContainer col-md-4" onclick="final_step()" >
				
				<div class="option video">

					<div class="optionContent">
						<video loop muted preload="none">
							<source src="https://chit-chat.me/storage/videos/563009823_181997965_1.mp4">
						</video>
					</div>

					<div class="optionInfo">
						<span>2</span>
					</div>

				</div>

			</div>
			<div class="optionContainer col-md-4" onclick="final_step()" >
				
				<div class="option video">

					<div class="optionContent">
						<video loop muted preload="none">
							<source src="https://chit-chat.me/storage/videos/1550740908_1772882632_1.mp4">
						</video>
					</div>

					<div class="optionInfo">
						<span>3</span>
					</div>

				</div>

			</div>
		</div>
		@php

			$messages = App\Settings::where('id', 29)->orWhere('id', 30)->orWhere('id', 31)->orderBy('id', 'asc')->get();

		@endphp
		<div class="final_step row">
			<div class="chat">
				<div class="chat-header">
					<img src="https://chit-chat.me/storage/images/1041275421_1024659838_43.jpeg">
					<span class="name">Bella Bionda</span>
					<span class="icon-status online"></span>
				</div>
				<div class="chat-messages">
					<span class="chat-connected"><span class="acc-name">Bella Bionda</span> {{l("is connected!")}}</span>
					<div id="message1" class="received-message">
						<img src="https://chit-chat.me/storage/images/1041275421_1024659838_43.jpeg">
						<span>{{preg_replace("/[^?! ]/",'*', l($messages[0]->value))}}</span>
					</div>
					<div id="message2" class="received-message">
						<img src="https://chit-chat.me/storage/images/1041275421_1024659838_43.jpeg">
						<span>{{preg_replace("/[^?! ]/",'*', l($messages[1]->value))}}</span>
					</div>
					<div id="message3" class="received-message">
						<img src="https://chit-chat.me/storage/images/1041275421_1024659838_43.jpeg">
						<span>{{preg_replace("/[^?! ]/",'*', l($messages[2]->value))}}</span>
					</div>
					<div class="chat-box">
						<!-- <form class="form" method="POST" action="{{ route('fast_register') }}">
                        @csrf 
							<input type="email" name="email" required placeholder="{{l('Type your email')}}">
							<input type="submit" name="submit" value="{{l('Send')}}">
						</form> -->

						<a href="" class="game-button">{{l("See messages!")}}</a>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>


<script src="/js/jquery-3.2.1.js"></script>
<script src="/js/main.js"></script>
<script src="/js/game.js"></script>
</body>
</html>