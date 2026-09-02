@extends('layouts.layout')

@section('content')
<div class="header-spacer header-spacer-small"></div>
<!-- Main Header Account -->

<div class="main-header">
	<div class="content-bg-wrap bg-account"></div>
	<div class="container">
		<div class="row">
			<div class="col col-lg-8 m-auto col-md-8 col-sm-12 col-12">
				<div class="main-header-content">
					<h1>{{l("Your Account Dashboard")}}</h1>
					<p>{{l("Welcome to your account dashboard! Here you’ll find everything you need to change your profile
																					information, settings, read notifications and requests, view your latest messages, change your pasword and much
																					more! Also you can create or manage your own favourite page, have fun!")}}</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ... end Main Header Account -->

<!-- Your Account Personal Information -->

<div class="container">
	<div class="row">
		<div class="col col-xl-9 order-xl-2 col-lg-9 order-lg-2 col-md-12 order-md-2 order-2 col-sm-12 col-12">
			<div class="ui-block">
				<div class="ui-block-title">
					<h6 class="title">{{l("Blocked Users")}}</h6>
				</div>
				<div class="ui-block-content">
					@if($users->count() != 0)
					@foreach($users as $user)
					<div class="user_blocked">
						<img src="/storage/images/{{$user->profile_image()}}">
						<span>{{$user->name()}}</span>
						<a href="/unblock/{{$user->id}}">{{l("Unblock")}}</a>
					</div>
					@endforeach
					@else
					<span>{{l("No blocked users")}}</span>
					@endif
				</div>
			</div>

		</div>

		<div class="col col-xl-3 order-xl-1 col-lg-3 order-lg-1 col-md-12 order-md-1 order-1 col-sm-12 col-12">
			<div class="ui-block">

				@include('components.inner.profile-sidebar-left')				

			</div>
		</div>
	</div>
</div>

<!-- ... end Your Account Personal Information -->
@endsection