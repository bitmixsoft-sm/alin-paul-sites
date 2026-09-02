<div class="container">
    <div class="row">
        <div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="ui-block">
                <div class="top-header">
                    <div class="top-header-thumb">
                        <img src="/storage/images/{{$user->cover_image()}}" alt="{{$user->name()}}">
                    </div>
                    <div class="profile-section">
                        <div class="row">
                            <div class="col col-lg-5 col-md-5 col-sm-12 col-12">
                                <ul class="profile-menu">
                                    <li>
                                        <a href="/profile/{{$user->username}}" @if($title == l("Profile Page")) class="active" @endif>{{l("Timeline")}}</a>
                                    </li>
                                    <li>
                                        <a href="/friends/{{$user->username}}" @if($title == l("Friends")) class="active" @endif>{{l("Friends")}}</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col col-lg-5 ml-auto col-md-5 col-sm-12 col-12">
                                <ul class="profile-menu">
                                    @if(Auth::check())
                                    <li>
                                        <a href="/profile/{{$user->username}}/photos" @if($title == l("Photos")) class="active" @endif>{{l("Photos")}}</a>
                                    </li>
                                    @else
                                    <li>
                                        <a href="/" @if($title == l("Photos")) class="active" @endif>{{l("Photos")}}</a>
                                    </li>
                                    @endif
                                    @if(Auth::check())
                                    <li>
                                        <a href="/profile/{{$user->username}}/albums" @if($title == l("Albums")) class="active" @endif>{{l("Albums")}}</a>
                                    </li>
                                    @else
                                    <li>
                                        <a href="/" @if($title == l("Albums")) class="active" @endif>{{l("Albums")}}</a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        @auth
                        <div class="control-block-button">
                            @if($user->id != Auth::id())
                            <a id="add-friend-btn" data-id="{{$user->id}}" href="
                            @if($user->hasFriendRequestFromAuth())
                                            /delete-request/{{$user->id}}    
                                        @elseif($user->hasFriendRequest())
                                            /acc-friend/{{$user->id}}
                                        @elseif($user->isFriendWithAuth())
                                            /delete-friend/{{$user->id}}
                                        @else
                                            /add-friend/{{$user->id}}
                                        @endif
                            " class="btn btn-control bg-blue add-friend">
                                <ul>
                                    <li>
                                        <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                                    </li>
                                    <li class="align-friend">
                                        <span class="add-friend-text">
                                        @if($user->hasFriendRequestFromAuth())
                                            {{l("Pending...")}}    
                                        @elseif($user->hasFriendRequest())
                                            {{l("Accept")}}
                                        @elseif($user->isFriendWithAuth())
                                            {{l("Friends")}}
                                        @else
                                            {{l("Add Friend")}}
                                        @endif
                                        </span>
                                    </li>
                                </ul>
                            </a>
                            @endif
                            @if($user->id != Auth::id())
                            <a href="#" data-id="{{$user->id}}" onclick="chat_open(this,event);" class="btn btn-control bg-purple">
                                <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                            </a>
                            @endif
                            @if($user->id == Auth::id())
                            <div class="btn btn-control bg-primary more">
                                <svg class="olymp-settings-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-settings-icon"></use></svg>

                                <ul class="more-dropdown more-with-triangle triangle-bottom-right">
                                    <li>
                                        <a data-image-upload="profile" href="#" data-toggle="modal" data-target="#update-header-photo">{{l("Update Profile Photo")}}</a>
                                    </li>
                                    <li>
                                        <a data-image-upload="cover" href="#" data-toggle="modal" data-target="#update-header-photo">{{l("Update Cover Photo")}}</a>
                                    </li>
                                    <li>
                                        <a href="/profile-settings">{{l("Profile Settings")}}</a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                        @endauth
                    </div>
                    <div class="top-header-author">
                        <a href="/profile/{{$user->username}}" class="author-thumb">
                            <img id="profile-image" src="/storage/images/{{$user->profile_image()}}" alt="{{$user->name()}}">
                        </a>
                        @if($user->profile_image()!='default.png' && optional(Auth::user())->isAdmin())
                            <div class="more">
                                <i class="fas fa-ellipsis-h" style="cursor: pointer;font-size: x-large;"></i>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="/delete-profile-image/{{$user->id}}">{{l("Delete Profile Image")}}</a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        <div class="author-content">
                            <a href="/profile/{{$user->username}}" class="h4 author-name">{{$user->name()}}</a>
                            @if($user->status == 'online' || $user->gender == 'female')
                                <span class="span-online">Online</span>
                            @else
                                <span data-user-id="{{$user->id}}" class="profile_status span-offline">Offline</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('components.image-upload')