<div class="control-icon more has-items">
                <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                <div id="chat-unread-counter" class="label-avatar bg-purple">{{Auth::user()->unreadMessages()}}</div>

                <div class="more-dropdown more-with-triangle triangle-top-center">
                    <div class="ui-block-title ui-block-title-small">
                        <h6 class="title">{{l("Chat / Messages")}}</h6>
                    </div>

                    <div class="mCustomScrollbar" data-mcs-theme="dark">
                        <ul id="chat-messages-top" class="notification-list chat-message">
                            @php
                                            if(Auth::user()->isAdmin()){
                                                $auth_id = Auth::user()->getAccountIds();
                                            }else{
                                                $auth_id = [Auth::id()];
                                            }
                                            $headerChats = Auth::user()->lastChats(30);
                                            @endphp
                            @if($headerChats->count() != 0)
                            @foreach($headerChats as $chat)
                            @php
                                $isOwnConversation = in_array(Auth::id(), [$chat->lastMessage()->from_user, $chat->lastMessage()->to_user]);
                            @endphp
                            <li @if(!in_array($chat->lastMessage()->from_user, $auth_id))
                                    data-id="{{$chat->lastMessage()->userFrom()->id}}" data-from="{{$chat->lastMessage()->userTo()->id}}"
                                    @elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id())
                                    data-id="{{$chat->lastMessage()->userFrom()->id}}" data-from="{{$chat->lastMessage()->userTo()->id}}"
                                    @else
                                    data-id="{{$chat->lastMessage()->userTo()->id}}" data-from="{{$chat->lastMessage()->userFrom()->id}}"
                                    @endif class="mess__item @if(Auth::user()->isAdmin() && !$isOwnConversation) mess__item-managed @endif @if($chat->lastMessage()->status == 0 && !in_array($chat->lastMessage()->from_user, $auth_id)) message-unread @endif " onclick="chat_open(this,event);">
                                <div class="author-thumb">
                                    <img src="/storage/images/@if(!in_array($chat->lastMessage()->from_user, $auth_id)){{$chat->lastMessage()->userFrom()->profile_image()}}@elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id()){{$chat->lastMessage()->userFrom()->profile_image()}}@else{{$chat->lastMessage()->userTo()->profile_image()}}@endif" alt="author">
                                </div>
                                <div class="notification-event">
                                    <a href="#" class="h6 notification-friend">
                                        @if(Auth::user()->isAdmin())
                                    @if(!$isOwnConversation)
                                    <span class="badge badge-warning">{{ l('Cont gestionat') }}</span>
                                    @endif
                                    @if(!in_array($chat->lastMessage()->from_user, $auth_id))
                                    {{$chat->lastMessage()->userFrom()->name()}} - {{$chat->lastMessage()->userTo()->name()}}
                                    @elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id())
                                    {{$chat->lastMessage()->userFrom()->name()}} - {{$chat->lastMessage()->userTo()->name()}}
                                    @else
                                    {{$chat->lastMessage()->userTo()->name()}} - {{$chat->lastMessage()->userFrom()->name()}}
                                    @endif
                                    @else
                                        @if($chat->lastMessage()->from_user != Auth::id())
                                    {{$chat->lastMessage()->userFrom()->name()}}
                                    @else
                                    {{$chat->lastMessage()->userTo()->name()}}
                                    @endif
                                    @endif
                                    </a>
                                    <span class="chat-message-item">@if(in_array($chat->lastMessage()->from_user, $auth_id)) You: @elseif($chat->lastMessage()->from_user == Auth::id() && $chat->lastMessage()->to_user != Auth::id()) You: @endif {{translate($chat->lastMessage()->message, Auth::user()->lang)}}</span>
                                </div>
                                <span class="notification-icon">
                                    <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                                </span>
                                <div class="more">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                                </div>
                            </li>
                            @endforeach
                            @else
                                <span class="no-req">{{l("No conversation initiated")}}</span>
                            @endif
                        </ul>
                        <div id="chat_load_threshold_top"></div>
                    </div>

                </div>
            </div>