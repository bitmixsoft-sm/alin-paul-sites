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
					<h6 class="title">{{l("Account Information")}}</h6>
				</div>
				<div class="ui-block-content">
					<div class="row">
						<div class="account_info_text"><span>{{l("Subscription Type:")}}</span> @if($user->package()) {{$user->package()->name}} @else {{l("None")}} @endif</div>
						<div class="account_info_text"><span>{{l("Expiration Date:")}}</span> @if($user->package()) {{$user->package_expire() ? $user->package_expire()->format('d/m/Y H:i') : l('None')}} @else {{l("None")}} @endif</div>
					</div>
				</div>
				<div class="ui-block-title">
					<h6 class="title">{{l("Personal Information")}}</h6>
				</div>
				<div class="ui-block-content">

					<!-- Personal Information Form  -->

					<form method="post" action="{{ route('save-profile-info') }}" enctype="multipart/form-data">
						@csrf
						<div class="row">

							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<div class="form-group label-floating">
									<label class="control-label">{{l("First Name")}}</label>
									<input name="firstname" class="form-control" placeholder="" type="text" value="{{old('firstname', $user->firstname)}}">
								</div>

								<div class="form-group label-floating">
									<label class="control-label">{{l("Your Email")}}</label>
									<input name="email" class="form-control" placeholder="" type="email" value="{{old('email', $user->email)}}">
								</div>

								<div class="form-group date-time-picker label-floating">
									<label class="control-label">{{l("Your Birthday")}}</label>
									<input autocomplete="off" name="datetimepicker" value="{{  old('datetimepicker',$user->birthday) }}" />
									<span class="input-group-addon">
															<svg class="olymp-month-calendar-icon icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-month-calendar-icon"></use></svg>
														</span>
									@if ($errors->has('datetimepicker'))
								 		<span class="text-danger" role="alert">
								 		<b>{{  $errors->first('datetimepicker') }}</b>
								 		</span>
                                	@endif
								</div>
							</div>

							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<div class="form-group label-floating">
									<label class="control-label">{{l("Last Name")}}</label>
									<input name="lastname" class="form-control" placeholder="" type="text" value="{{old('lastname', $user->lastname)}}">
								</div>

								<div class="form-group label-floating is-empty">
									<label class="control-label">{{l("Your Phone Number")}}</label>
									<input name="phone" class="form-control" placeholder="" type="tel" value="{{old('phone', $user->phone)}}">

									@if ($errors->has('phone'))
								 		<span class="text-danger" role="alert">
								 		<b>{{  $errors->first('phone') }}</b>
								 		</span>
                                	@endif
								</div>



								<div class="form-group label-floating">
									<label class="control-label">{{l("Your Occupation")}}</label>
									<input name="job" class="form-control" placeholder="" type="text" value="{{old('job', $user->job)}}">
								</div>
							</div>
							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<div class="form-group label-floating">
									<label class="control-label">{{l("Write a little description about you")}}</label>
									<textarea name="description" class="form-control" placeholder="">{{old('description', $user->description)}}</textarea>
								</div>

							</div>
							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<div class="form-group label-floating is-select">
									<label class="control-label">{{l("Status")}}</label>
									<select name="social_status" class="selectpicker form-control">
										<option @if(old('social_status', $user->social_status) == '') selected="selected" @endif disabled value="">Select status</option>
										<option @if(old('social_status', $user->social_status) == 'single') selected="selected" @endif  value="single">Single</option>
										<option @if(old('social_status', $user->social_status) == 'relationship') selected="selected" @endif  value="relationship">In a relationship</option>
										<option @if(old('social_status', $user->social_status) == 'married') selected="selected" @endif  value="married">Married</option>
										<option @if(old('social_status', $user->social_status) == 'dating') selected="selected" @endif  value="dating">Dating</option>
										<option @if(old('social_status', $user->social_status) == 'complicated') selected="selected" @endif  value="complicated">Complicated</option>
									</select>
								</div>
                                <div class="form-group label-floating">
                                    <label class="control-label">{{l("Profile background image")}}</label>
                                    <input type="file" name="background_image" class="form-control-file">
                                </div>
							</div>
							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<a href="/profile-settings" class="btn btn-secondary btn-lg full-width">{{l("Restore all Attributes")}}</a>
							</div>
							<div class="col col-lg-6 col-md-6 col-sm-12 col-12">
								<button type="submit" class="btn btn-primary btn-lg full-width">{{l("Save all Changes")}}</button>
							</div>

						</div>
					</form>

					<!-- ... end Personal Information Form  -->
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
