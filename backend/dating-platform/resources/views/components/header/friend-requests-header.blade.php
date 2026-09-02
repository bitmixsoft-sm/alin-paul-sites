<div class="control-icon more has-items">
                <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                <div id="fr-req-counter" class="label-avatar bg-blue">{{Auth::user()->allFriendRequests()->count()}}</div>

                <div class="more-dropdown more-with-triangle triangle-top-center">
                    <div class="ui-block-title ui-block-title-small">
                        <h6 class="title">{{l("FRIEND REQUESTS")}}</h6>
                    </div>

                    <div class="mCustomScrollbar" data-mcs-theme="dark">
                        <ul id="friend-requests-block" class="notification-list friend-requests">
                            @if(Auth::user()->allFriendRequests()->count() != 0)
                            @foreach(Auth::user()->allFriendRequests() as $request)
                            <li tpl-id="{{$request->userFrom()->id}}">
                                <div class="author-thumb">
                                    <img src="/storage/images/{{$request->userFrom()->profile_image()}}" alt="author">
                                </div>
                                <div class="notification-event">
                                    <a href="/profile/{{$request->userFrom()->username}}" class="h6 notification-friend">{{$request->userFrom()->name()}}</a>
                                    <span class="chat-message-item">Since: {{date_format(date_create($request->created_at),"d/m/Y")}}</span>
                                </div>
                                <span class="notification-icon">
                                    <a href="/acc-friend/{{$request->userFrom()->id}}" onClick="acc_fr_req(this, event);" class="accept-request acc-fr-req">
                                        <span class="icon-add without-text">
                                            <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                                        </span>
                                    </a>

                                    <a href="/delete-request/{{$request->userFrom()->id}}" onClick="del_fr_req(this, event);" class="accept-request request-del del-fr-req">
                                        <span class="icon-minus">
                                            <svg class="olymp-happy-face-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-happy-face-icon"></use></svg>
                                        </span>
                                    </a>

                                </span>

                                <div class="more">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                                </div>
                            </li>
                            @endforeach
                            @else
                                <span class="no-req">{{l("No friend requests")}}</span>
                            @endif
                        </ul>
                    </div>

                </div>
            </div>