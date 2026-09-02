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

                                            // Optional: interleave the AI Companion conversations into this same dropdown,
                                            // sorted by recency alongside real chats - see MERGE_AI_INBOX in .env
                                            // (config/services.php). Off by default, in which case $headerRows below is
                                            // just $headerChats wrapped one-for-one and nothing about this dropdown's
                                            // behavior changes from before this feature existed.
                                            $mergeAiInbox = Auth::user()->isAdmin() && config('services.merge_ai_inbox');
                                            $headerRows = [];
                                            foreach ($headerChats as $chat) {
                                                $headerRows[] = ['type' => 'real', 'updated_at' => $chat->updated_at, 'chat' => $chat];
                                            }
                                            if ($mergeAiInbox) {
                                                try {
                                                    $aiHeaderRows = \App\Conversation::query()
                                                        ->whereIn('user_id', Auth::user()->getAccountIds())
                                                        ->where('message_count', '>', 0)
                                                        ->with(['user:id,firstname,lastname,username', 'aiProfile:id,name,static_image_path'])
                                                        ->orderByDesc('updated_at')
                                                        ->limit(30)
                                                        ->get()
                                                        ->filter(fn ($c) => $c->user !== null && $c->aiProfile !== null);
                                                    foreach ($aiHeaderRows as $conversation) {
                                                        $headerRows[] = ['type' => 'ai', 'updated_at' => $conversation->updated_at, 'conversation' => $conversation];
                                                    }
                                                } catch (\Throwable $e) {
                                                    // Leave $headerRows as just the real chats - a broken AI query shouldn't
                                                    // take down the header dropdown every page relies on.
                                                }
                                                usort($headerRows, fn ($a, $b) => $b['updated_at'] <=> $a['updated_at']);
                                                $headerRows = array_slice($headerRows, 0, 30);
                                            }
                                            @endphp
                            @if(count($headerRows) != 0)
                            @foreach($headerRows as $headerRow)
                            @if($headerRow['type'] === 'ai')
                                @php $conversation = $headerRow['conversation']; @endphp
                                {{-- AI Companion chat JS (ai_inbox_open/ai_chat_open) only exists inline on the
                                     Find Friends page, not site-wide like chat_open() - so from any other page
                                     this just navigates there and auto-opens it (see the small script at the
                                     bottom of find_friends.blade.php that reads these query params). --}}
                                <li class="mess__item mess__item-managed">
                                    <a href="/find-friends?open_ai_profile={{ $conversation->ai_profile_id }}&open_ai_user={{ $conversation->user_id }}&open_ai_name={{ urlencode($conversation->aiProfile->name) }}&open_ai_image={{ urlencode($conversation->aiProfile->imageUrl() ?? '') }}" style="display:contents;">
                                        <div class="author-thumb">
                                            <img src="{{ $conversation->aiProfile->imageUrl() ?? '/storage/images/default.png' }}" alt="author">
                                        </div>
                                        <div class="notification-event">
                                            <span class="h6 notification-friend">
                                                <span class="badge badge-warning">{{ l('Cont gestionat') }}</span>
                                                {{ $conversation->aiProfile->name }} - {{ $conversation->user->name() }}
                                            </span>
                                            <span class="chat-message-item">{{ mb_substr((string) data_get($conversation->conversion_context, 'last_assistant_preview', ''), 0, 80) }}</span>
                                        </div>
                                        <span class="notification-icon">
                                            <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                                        </span>
                                    </a>
                                </li>
                            @else
                                @php
                                    $chat = $headerRow['chat'];
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
                            @endif
                            @endforeach
                            @else
                                <span class="no-req">{{l("No conversation initiated")}}</span>
                            @endif
                        </ul>
                        <div id="chat_load_threshold_top"></div>
                    </div>

                </div>
            </div>