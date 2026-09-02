@extends('layouts.layout')
@section('content')

<div class="header-spacer"></div>


<!-- Top Header-Profile -->

@include('components.top-header-profile')

<!-- ... end Top Header-Profile -->
<div class="container">
    <div class="row">
        @guest
            <div class="medium-padding120 guest-profile">
                <div class="container">
                    <div class="row">
                        <div class="col col-xl-12 col-lg-12 col-md-12 col-12 m-auto">
                            <div class="logout-content">
                                <div class="logout-icon">
                                    <i class="fas fa-times"></i>
                                </div>
                                <h6>{{l("Do you wanna check")}} {{$user->firstname}}{{l("’s Profile?")}}</h6>
                                <p><a href="/">{{l("Login")}}</a> {{l("or")}} <a href="/">{{l("Register")}}</a> {{l("now to create your own profile and have access to all the")}} {{config('app.name')}} {{l("awesome features!")}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
        @endguest

        <!-- Main Content -->
        @auth
        <div class="container">
			<div class="row">
				<div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
					<div class="ui-block responsive-flex">
						<div class="ui-block-title album-top-control">
							<div class="h6 title">{{$user->firstname}}{{l("’s Photo Gallery")}}</div>
							@if($user->id == Auth::id())
							<div class="block-btn align-right">
								<a href="#" data-toggle="modal" data-target="#create-photo-album" class="btn btn-primary btn-md-2">{{l("Create Album +")}}</a>

								<a href="#" data-imageupload="normal" data-toggle="modal" data-target="#update-header-photo" class="btn btn-md-2 btn-border-think custom-color c-grey">{{l("Add Photos")}}</a>
							</div>
							@endif
							<ul class="nav nav-tabs photo-gallery" role="tablist">
								<li class="nav-item">
									<a class="nav-link @if($page == 'photos') active @endif" href="/profile/{{$user->username}}/photos">
										<svg class="olymp-photos-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-photos-icon"></use></svg>
									</a>
								</li>

								<li class="nav-item">
									<a class="nav-link @if($page == 'albums') active @endif" href="/profile/{{$user->username}}/albums">
										<svg class="olymp-albums-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-albums-icon"></use></svg>
									</a>
								</li>

							</ul>
							<a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
					<!-- Tab panes -->
					<div class="tab-content">
						@if($page == 'photos')
						<div class="tab-pane active" id="photo-page" role="tabpanel">

							<div class="photo-album-wrapper photo-zoom-gallery">
								@foreach($images as $image)
								<div data-id="{{$image->id}}" class="photo-item col-4-width" href="/storage/images/{{$image->name}}">
									<img src="/storage/images/{{$image->name}}" alt="photo">
									@if($user->id == Auth::id() && Auth::user()->cover_image() != $image->name)
										<a href="#" onclick="alert_delete_photo({{$image->id}});" class="del-photo-item"><svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg></a>
									@endif
								</div>						
								@endforeach
								<a href="#" class="btn btn-control btn-more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg></a>

							</div>

						</div>
						@endif
						@if($page == 'albums')
						<div class="tab-pane active" id="album-page" role="tabpanel">

							<div class="photo-album-wrapper">
								@if($user->id == Auth::id())
								<div class="photo-album-item-wrap col-4-width" >
									
									<div class="photo-album-item create-album create-album-control" data-mh="album-item">
									
										<a href="#" data-toggle="modal" data-target="#create-photo-album" class="  full-block"></a>
									
										<div class="content">
									
											<a href="#" class="btn btn-control bg-primary" data-toggle="modal" data-target="#create-photo-album">
												<svg class="olymp-plus-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-plus-icon"></use></svg>
											</a>
									
											<a href="#" class="title h5" data-toggle="modal" data-target="#create-photo-album">{{ l("Create an Album") }}</a>
											<span class="sub-title">{{l("It only takes a few minutes!")}}</span>
									
										</div>
									
									</div>
								</div>
								@endif
								@foreach($albums as $album)
								<div class="photo-album-item-wrap col-4-width">															
									<div class="photo-album-item" data-mh="album-item">
										<div class="photo-item">
											@if($album->privacy != "")
											<img class="lock" src="/img/lock.png" alt="photo">
											@else
											<img src="/storage/images/{{$album->images()->latest()->first()->name}}" alt="photo">
											@endif
											
											<div class="overlay overlay-dark"></div>
											<a href="#" class="post-add-icon">
												<svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
												<span>{{$album->views}}</span>
											</a>
											<a href="#" onclick="get_album(this);" @if($album->privacy != '') data-protect="true" @endif data-album="{{$album->id}}" class="  full-block"></a>
										</div>
									
										<div class="content">
											<a href="#" onclick="get_album(this);" @if($album->privacy != '') data-protect="true" @endif data-album="{{$album->id}}" class="title h5">{{$album->name}}</a>
											<span class="sub-title">{{l("Last Added:")}} {{$album->images->last()->pivot->created_at->format('d/m/Y H:i')}}</span>
											<div class="swiper-containerr">
												<div class="swiper-wrapperr">
							
													<div class="swiper-slidee">
														<div class="friend-count" data-swiper-parallax="-500">
															<a href="#" onclick="get_album(this);" @if($album->privacy != '') data-protect="true" @endif data-album="{{$album->id}}" class="friend-count-item">
																<div class="h6">{{$album->images()->count()}}</div>
																<div class="title">{{l("Photos")}}</div>
															</a>
															<a href="#" onclick="get_album(this);" @if($album->privacy != '') data-protect="true" @endif data-album="{{$album->id}}" class="friend-count-item">
																<div class="h6">{{$album->views}}</div>
																<div class="title">{{l("Views")}}</div>
															</a>
														</div>
													</div>
												</div>
									
												<!-- If we need pagination 
												<div class="swiper-pagination"></div>-->
											</div>
										</div>
									
									</div>
								</div>
								@endforeach

							</div>

						</div>
						@endif
					</div>

				</div>
			</div>
		</div>

		<!-- Window-popup Open Photo Popup V2 -->

		<div class="modal fade modal-has-swiper" id="open-photo-popup-v2" tabindex="-1" role="dialog" aria-labelledby="open-photo-popup-v2" aria-hidden="true">
			<div class="modal-dialog window-popup open-photo-popup open-photo-popup-v2" role="document">
				<div class="modal-content">
					<a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
						<svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
					</a>

					<div class="modal-body">
						<div class="open-photo-thumb">

							<div class="swiper-container" data-effect="fade" data-autoplay="4000">

								<!-- Additional required wrapper -->
								<div id="album-slides" class="swiper-wrapper">
									<!-- Slides -->

								</div>

							</div>

							<!--Pagination tabs-->

							<div id="album-slides-pag" class="slider-slides">

								<!--Prev Next Arrows-->

								<svg class="btn-next olymp-popup-right-arrow"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-right-arrow"></use></svg>

								<svg class="btn-prev olymp-popup-left-arrow"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-popup-left-arrow"></use></svg>

							</div>

						</div>

						<div class="open-photo-content">

					<article class="hentry post">

						<div id="album-item-user" class="post__author author vcard inline-items">
							<img src="/storage/images/{{$user->profile_image()}}" alt="author">

							<div class="author-date">
								<a class="h6 post__author-name fn" href="/profile/{{$user->username}}">{{$user->name()}}</a>
								<div class="post__date">
									<time class="published" datetime="2017-03-24T18:18">
										
									</time>
								</div>
							</div>
							@if($user->id == Auth::id())
							<div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
								<ul class="more-dropdown">
									<li>
										<a id="delete-album-inner" onclick="delete_album();" href="#">{{l("Delete Album")}}</a>
									</li>
									<li>
										<a id="delete-photo-inner" onclick="delete_image(this);" href="#">{{l("Delete Photo")}}</a>
									</li>
								</ul>
							</div>
							@endif

						</div>

						<p id="album-item-desc"></p>

						<div id="control-block-album" class="control-block-button post-control-button">
							@if($user->id != Auth::id())
							<a href="#" data-id="{{$user->id}}" onclick="chat_open(this,event); modal_hide('open-photo-popup-v2');" data-toggle="tooltip" data-placement="right" data-original-title="Message {{$user->name()}}" class="btn btn-control">
								<svg class="olymp-comments-post-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-comments-post-icon"></use></svg>
							</a>
							@endif
						</div>

					</article>

				</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Window-popup Open Photo Popup V2 -->
		<!-- Window-popup Create Photo Album -->

		<div class="modal fade" id="create-photo-album" tabindex="-1" role="dialog" aria-labelledby="create-photo-album" aria-hidden="true">
			<div class="modal-dialog window-popup create-photo-album" role="document">
				<div class="modal-content">
					<a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
						<svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
					</a>

					<div class="modal-header">
						<h6 class="title">{{l("Create Photo Album")}}</h6>
					</div>

					<div class="modal-body">

					
						<div class="form-group label-floating">
							<label class="control-label">{{l("Album Name")}}</label>
							<input id="album-name-text" name="album-name" autocomplete="off" class="form-control" type="text">
						</div>
						<div class="form-group label-floating">
							<label class="control-label">{{l("Album Password (If you want to be protected)")}}</label>
							<input id="album-privacy-text" name="album-privacy" autocomplete="off" class="form-control" type="password">
						</div>
					

					<div id="album-upload-wrapper" class="photo-album-wrapper">
						<div id="add-album-photo-button" class="photo-album-item-wrap col-3-width" >
							<div class="photo-album-item create-album" data-mh="album-item">
								<div class="content">
									<a href="#" class="btn btn-control bg-primary add-album-photo">
										<svg class="olymp-plus-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-plus-icon"></use></svg>
									</a>

									<a href="#" class="title h5 add-album-photo">{{l("Add More Photos...")}}</a>
									
									<form id="album-submit-form" method="POST" enctype="multipart/form-data">
				                        @csrf
										<input id="upload-album" class="upload-display-none" type="file"/>
				                    </form>
								</div>
							</div>
						</div>
					</div>

					<a id="close-album-modal" href="/profile/{{$user->username}}/albums" class="btn btn-secondary btn-lg btn--half-width">{{l("Cancel")}}</a>
					<button id="post-new-album" class="btn btn-primary btn-lg btn--half-width">{{l("Post Album")}}</button>
						
				</div>
				</div>
			</div>
		</div>

		<!-- ... end Window-popup Create Photo Album -->




		<a class="back-to-top" href="#">
			<img src="/svg-icons/back-to-top.svg" alt="arrow" class="back-icon">
		</a>

        @endauth
@endsection