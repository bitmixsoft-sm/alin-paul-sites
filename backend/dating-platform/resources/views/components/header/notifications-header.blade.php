<div class="control-icon more has-items">
                <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>

                <div id="unread-notifications" class="label-avatar bg-primary">{{Auth::user()->unreadNotifications()}}</div>

                <div class="more-dropdown more-with-triangle triangle-top-center">
                    <div class="ui-block-title ui-block-title-small">
                        <h6 class="title">{{l("Notifications")}}</h6>
                        <a onclick="notifications_seen();" href="#">{{l("Mark all as read")}}</a>
                    </div>

                    <div class="mCustomScrollbar" data-mcs-theme="dark">
                        <ul class="notification-list">
                            @foreach(Auth::user()->notifications()->take(10) as $nt)
                            @if($nt->type == 'like')
                            <li @if($nt->seen == 0) class="un-read" @endif>
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> likes your new <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-heart-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-heart-icon"></use></svg>
                                    </span>
                            </li>
                            @else
                            <li @if($nt->seen == 0) class="un-read" @endif>
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$nt->getUser()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <div><a href="/profile/{{$nt->getUser()->username}}" class="h6 notification-friend">{{$nt->getUser()->name()}}</a> posted a new <a href="/newsfeed?post_id={{$nt->getPost()->id}}" class="notification-link">{{$nt->getPost()->type}}</a>.</div>
                                    <span class="notification-date"><time class="entry-date updated" datetime="2004-07-24T18:18">{{$nt->created_at->format("d/m/y H:i")}}</time></span>
                                </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-newsfeed-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-newsfeed-icon"></use></svg>
                                    </span>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>