@extends('layouts.layout')
@section('content')
@auth
<!-- ... end Responsive Header-BP -->

<!-- ... end Responsive Header-BP -->
<div class="header-spacer"></div>



<!-- Top Header-Profile -->

@include('components.top-header-profile')

<!-- ... end Top Header-Profile -->

<div class="container">
	<div class="row">
		<div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
			<div class="ui-block responsive-flex">
				<div class="ui-block-title">
					<div class="h6 title">{{$user->firstname}}{{l("’s Friends")}} ({{$user->allFriends('female')->count()}})</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- Friends -->

<div class="container">
	<div class="row">
		@foreach($friends as $friend)
		@if($friend->id != Auth::id())
		<div class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
			<div class="ui-block">
				
				<!-- Friend Item -->
				
				<div class="friend-item">
					<div class="friend-header-thumb">
						<img src="/storage/images/{{$friend->cover_image()}}" alt="{{$friend->name()}}">
					</div>
				
					<div class="friend-item-content">
						<div class="friend-avatar">
							<div class="author-thumb friends-page-profile-img">
								<img src="/storage/images/{{$friend->profile_image()}}" alt="{{$friend->name()}}">
							</div>
							<div class="author-content friends-page-name">
								<a href="/profile/{{$friend->username}}" class="h5 author-name">{{$friend->name()}}</a>
								<div class="country">{{$friend->country}}</div>
							</div>
						</div>
				
						<div class="swiper-container" data-slide="fade">
							<div class="swiper-wrapper">
								<div class="swiper-slide">
									<div class="friend-count" data-swiper-parallax="-500">
										<a href="/friends/{{$friend->username}}" class="friend-count-item">
											<div class="h6">{{$friend->allFriends('female')->count()}}</div>
											<div class="title">{{l("Friends")}}</div>
										</a>
										<a href="/profile/{{$friend->username}}/albums" class="friend-count-item">
											<div class="h6">{{$friend->images()->count()}}</div>
											<div class="title">{{l("Photos")}}</div>
										</a>
									</div>
									<div class="control-block-button" data-swiper-parallax="-100">
										<a href="/profile/{{$friend->username}}" class="btn btn-control bg-blue">
											<svg class="olymp-magnifying-glass-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-magnifying-glass-icon"></use></svg>
										</a>
				
										<a href="#" data-id="{{$friend->id}}" onclick="chat_open(this,event);" class="btn btn-control bg-purple">
											<svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
										</a>
				
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- ... end Friend Item -->			</div>
		</div>
		@endif
		@endforeach
		
	</div>
	<div class="pags">
		{{$friends->links()}}
	</div>
</div>

<!-- ... end Friends -->



		<a class="back-to-top" href="#">
			<img src="/svg-icons/back-to-top.svg" alt="arrow" class="back-icon">
		</a>
        @endauth
@endsection