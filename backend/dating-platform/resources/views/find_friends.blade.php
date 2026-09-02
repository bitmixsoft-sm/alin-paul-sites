@extends('layouts.layout')
@section('content')
    <!-- ... end Responsive Header-BP -->
    @php
        $show_roulette = 0;
    @endphp
    <div class="header-spacer"></div>

    <!-- Main Content Groups -->

    <div class="container">
        <h1 class="header-middle-center">{{ l('Matches for you') }}</h1>
        <hr>

        @if(Auth::check() && Auth::user()->isAdmin() && count($aiInbox ?? []))
            <div class="ai-inbox-panel mb-3">
                <h4 class="header-middle-center">{{ l('AI Inbox') }} <small class="text-muted">({{ l('managed accounts') }})</small></h4>
                <div class="list-group">
                    @foreach($aiInbox as $item)
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                           onclick="ai_inbox_open({{ $item['ai_profile_id'] }}, @js($item['ai_profile_name']), @js($item['ai_profile_image']), {{ $item['user_id'] }}); return false;">
                            <span>
                                <strong>{{ $item['ai_profile_name'] }}</strong>
                                <span class="text-muted">&harr; {{ $item['user_name'] }}</span>
                                @if($item['preview'])
                                    <br><small class="text-muted">{{ $item['preview'] }}</small>
                                @endif
                            </span>
                            @if($item['updated_at'])
                                <small class="text-muted">{{ \Carbon\Carbon::parse($item['updated_at'])->diffForHumans() }}</small>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            <hr>
        @endif

        @isset($aiProfiles)
            @if ($aiProfiles->count() != 0)
                {{-- <h4 class="header-middle-center">{{ l('AI Companions') }}</h4> --}}
                <div id="ai_friends_results" class="row">
                    @foreach ($aiProfiles as $ai)
                        <div class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 find_friends_item">
                            <div class="ui-block"
                                style="@if ($ai->imageUrl()) background: #222 url('{{ $ai->imageUrl() }}') no-repeat center center; background-size: contain;@endif  height:415px;">
                                <span class="find_friends_status span-online">{{ l('Online') }}</span>
                                <div class="friend-item friend-groups">
                                    <div class="friend-item-content">
                                        <div class="friend-avatar">
                                            <div class="author-thumb find-friends-item"></div>
                                        </div>
                                        <div class="friend-actions">
                                            <div class="author-content">
                                                <span class="h5 author-name">{{ $ai->name }}</span>
                                            </div>
                                            <div class="control-block-button @guest justify-content-center @endguest">
                                                <a href="#" class="  btn btn-control bg-blue" onclick="return false;"
                                                    data-toggle="tooltip" data-placement="top"
                                                    data-original-title="{{ l('See Profile') }}">
                                                    {{ l('See Profile') }}
                                                </a>
                                                @auth
                                                    <a href="#" class="btn btn-control bg-purple"
                                                        onclick="ai_chat_open({{ $ai->id }}, @js($ai->name), @js($ai->imageUrl())); return false;"
                                                        data-toggle="tooltip" data-placement="top"
                                                        data-original-title="{{ l('Start chatting') }}">
                                                        {{ l('Chat') }}
                                                    </a>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr>
            @endif
        @endisset

        <div id="find_friends_results" class="row">
            @if ($users->count() != 0)
                @foreach ($users as $user)
                    <div data-user-id="{{$user->id}}" class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 find_friends_item">
                        <div @if (!$loop->first || !$show_roulette) class="ui-block" @endif data-mh="friend-groups-item"
                            style="@if ($user->images->count() > 0) background: url('/storage/images/{{ $user->images->take(1)[0]->name }}');@endif  height:415px;" >
                            @if ($user->status == 'online' || $user->gender == 'female')
                                <span class="find_friends_status span-online">Online</span>
                            @else
                                <span class="find_friends_status span-offline">Offline</span>
                            @endif
                            @if ($loop->first && auth()->check() && $show_roulette)
                                @include('components.roulette_spinner', [
                                    'width' => '295px',
                                    'height' => '295px',
                                ])
                            @else
                                <!-- Friend Item -->
                                <div class="friend-item friend-groups">

                                    <div class="friend-item-content">

                                        <div class="friend-avatar">

                                            <div class="author-thumb find-friends-item">
                                                @if ($user->images->count() == 0)
                                                    <a href="/profile/{{ $user->username }}"><img
                                                            src="/storage/images/{{ $user->profile_image() }}"
                                                            alt="{{ $user->name() }}"></a>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- @auth
                                            <ul class="friends-harmonic">
                                                @foreach ($user->commonFriendsRefactored()->take(5) as $common)
                                                <li data-toggle="tooltip" data-placement="top" title="" data-original-title="{{$common->name()}}">
                                                    <a href="/profile/{{$common->username}}">
                                                        <img src="/storage/images/{{$common->profile_image()}}" alt="{{$common->name()}}">
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>
                                        @endauth --}}
                                        <div class="friend-actions">
                                            <div class="author-content">
                                                <a href="/profile/{{ $user->username }}"
                                                    class="h5 author-name">{{ $user->name() }} @if ($user->age() != 0)
                                                        , {{ $user->age() }}
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="control-block-button @guest justify-content-center @endguest">
                                                <a href="/profile/{{ $user->username }}" class="  btn btn-control bg-blue"
                                                    data-toggle="tooltip" data-placement="top"
                                                    data-original-title="{{ l('See Profile') }}">
                                                    {{ l('See Profile') }}
                                                </a>
                                                @auth
                                                    <a href="#" data-id="{{ $user->id }}"
                                                        onclick="chat_open(this,event);" class="btn btn-control bg-purple"
                                                        data-toggle="tooltip" data-placement="top"
                                                        data-original-title="{{ l('Start chatting') }}">
                                                        {{ l('Chat') }}
                                                    </a>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="find-friends-no-result"><span>{{ l('No results') }}</span></div>
            @endif

        </div>
        <a id="load_more_find_friends" href="#" class="btn btn-control btn-more">
            <svg class="olymp-three-dots-icon">
                <use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use>
            </svg>
        </a>
    </div>

    <!-- ... end Main Content Groups -->



    <a class="back-to-top" href="#">
        <img src="/svg-icons/back-to-top.svg" alt="arrow" class="back-icon">
    </a>
    @php
        if (Request::get('age')) {
            $age = array_map('intval', explode(';', Request::get('age')));
        } else {
            $age = [18, 40];
        }
    @endphp
    <script type="text/javascript">
        var slider_min = {{ $age[0] }};
        var slider_max = {{ $age[1] }};
    </script>

    @auth
        @include('components.ai_chat_popup')
    @endauth
@endsection

@auth
    @section('scripts')
    <style>
        .ai-typing-dots { display: inline-flex; align-items: center; gap: 3px; padding: 2px 0; }
        .ai-typing-dots span {
            width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .4;
            animation: ai-typing-blink 1.2s infinite ease-in-out;
        }
        .ai-typing-dots span:nth-child(2) { animation-delay: .2s; }
        .ai-typing-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes ai-typing-blink {
            0%, 80%, 100% { opacity: .25; }
            40% { opacity: 1; }
        }
    </style>
    <script>
    (function () {
        // When true, the user's own camera turns on automatically as soon as a live
        // video session starts. When false, it stays off until the user clicks the
        // camera icon themselves.
        var AI_LIVE_AUTO_START_CAMERA = true;

        // When true, reopening an AI chat popup re-loads and displays the earlier
        // messages of the user's last conversation with that profile. When false,
        // the popup always starts empty, as before.
        var AI_LOAD_CHAT_HISTORY = true;

        // When true, the AI's reply is held back for a bit and a "typing..." indicator
        // is shown first, so the response doesn't just pop up instantly - especially
        // noticeable (and AI-revealing) for long replies. When false, replies appear
        // as soon as they arrive, as before.
        var AI_SIMULATE_TYPING = true;
        var AI_TYPING_MS_PER_CHARACTER = 40;
        var AI_TYPING_MIN_DELAY_MS = 800;
        var AI_TYPING_MAX_DELAY_MS = 35000;

        function aiTypingDelayFor(text) {
            var estimate = (text || '').length * AI_TYPING_MS_PER_CHARACTER;
            return Math.min(Math.max(estimate, AI_TYPING_MIN_DELAY_MS), AI_TYPING_MAX_DELAY_MS);
        }

        function aiShowTypingIndicator(popup) {
            var list = popup.querySelector('.chat-message-field');
            if (!list) { return function () {}; }

            var li = document.createElement('li');
            li.className = 'ai-typing-indicator';

            var thumb = document.createElement('div');
            thumb.className = 'author-thumb';
            var img = document.createElement('img');
            img.src = popup.dataset.aiImage || '/storage/images/default.png';
            thumb.appendChild(img);

            var eventDiv = document.createElement('div');
            eventDiv.className = 'notification-event';
            var msgSpan = document.createElement('span');
            msgSpan.className = 'chat-message-item ai-typing-dots';
            msgSpan.innerHTML = '<span></span><span></span><span></span>';
            eventDiv.appendChild(msgSpan);

            li.appendChild(thumb);
            li.appendChild(eventDiv);
            list.appendChild(li);

            var scrollBox = popup.querySelector('.mCustomScrollbar');
            if (scrollBox) { scrollBox.scrollTop = scrollBox.scrollHeight; }

            return function () {
                if (li.parentNode) { li.parentNode.removeChild(li); }
            };
        }

        var aiConversations = {};
        var aiActiveSessions = {};

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }

        function aiAppendMessage(popup, isMe, text, at) {
            var list = popup.querySelector('.chat-message-field');
            if (!list) { return; }

            var li = document.createElement('li');
            if (isMe) { li.className = 'me'; }

            var thumb = document.createElement('div');
            thumb.className = 'author-thumb';
            var img = document.createElement('img');
            img.src = isMe ? (popup.dataset.userAvatar || '/storage/images/default.png') : (popup.dataset.aiImage || '/storage/images/default.png');
            thumb.appendChild(img);

            var eventDiv = document.createElement('div');
            eventDiv.className = 'notification-event';
            var msgSpan = document.createElement('span');
            msgSpan.className = 'chat-message-item';
            msgSpan.textContent = text;
            eventDiv.appendChild(msgSpan);

            var dateSpan = document.createElement('span');
            dateSpan.className = 'notification-date';
            var time = document.createElement('time');
            time.className = 'entry-date updated';
            time.textContent = (at ? new Date(at) : new Date()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            dateSpan.appendChild(time);
            eventDiv.appendChild(dateSpan);

            li.appendChild(thumb);
            li.appendChild(eventDiv);
            list.appendChild(li);

            var scrollBox = popup.querySelector('.mCustomScrollbar');
            if (scrollBox) { scrollBox.scrollTop = scrollBox.scrollHeight; }
        }

        function aiLoadHistory(popup, profileId) {
            if (!AI_LOAD_CHAT_HISTORY) { return; }

            var url = popup.dataset.historyEndpoint + '?ai_profile_id=' + encodeURIComponent(profileId);
            if (popup.dataset.from) {
                url += '&from=' + encodeURIComponent(popup.dataset.from);
            }

            fetch(url, {
                headers: { 'Accept': 'application/json' }
            }).then(function (resp) {
                return resp.json();
            }).then(function (data) {
                aiConversations[profileId] = data.conversation_id || null;
                (data.messages || []).forEach(function (message) {
                    aiAppendMessage(popup, message.role === 'user', message.content, message.created_at);
                });
            }).catch(function (err) {
                console.error('AI chat history load failed', err);
            });
        }

        window.ai_chat_open = function (profileId, name, imageUrl) {
            var dataId = 'ai-' + profileId;
            var existing = document.querySelector('.chats .popup-chat[data-id="' + dataId + '"]');
            if (existing) {
                $(existing).removeClass('popup-chat-toggle')
                    .stop(true)
                    .animate({ bottom: '0px' }, 100).animate({ bottom: '-15px' }, 100)
                    .animate({ bottom: '0px' }, 100).animate({ bottom: '-15px' }, 100);
                return;
            }

            var template = document.getElementById('ai-popup-template');
            var chatsBox = document.querySelector('.chats');
            if (!template || !chatsBox || typeof window.chats === 'undefined') { return; }

            var sec_mg = window.chats > 0 ? parseInt($('.chats .popup-chat').first().css('right')) : window.chat_mg;

            var popup = template.cloneNode(true);
            popup.removeAttribute('id');
            popup.classList.add('popup-chat');
            popup.style.display = 'block';
            popup.setAttribute('data-id', dataId);
            popup.setAttribute('data-ai-profile-id', profileId);
            popup.dataset.aiImage = imageUrl || '';
            popup.querySelector('.title').textContent = name;
            var closeIcon = popup.querySelector('.js-chat-close');
            if (closeIcon) { closeIcon.setAttribute('data-id', dataId); }

            $(chatsBox).prepend(popup);
            aiLoadHistory(popup, profileId);

            window.chats += 1;
            if (window.chats > 1) {
                window.chat_mg = sec_mg + 325;
            }
            $(popup).css('right', window.chat_mg + 'px');

            aiConversations[profileId] = aiConversations[profileId] || null;
        };

        // Admin-only: lets an admin/editor act as one of their managed accounts when
        // chatting with an AI profile, mirroring change_user() for the real user-to-user
        // chat popup (storage/app/assets/js/dating.js). Switching accounts reloads that
        // account's own conversation history with this AI profile from scratch.
        window.ai_change_user = function (t) {
            var popup = $(t).closest('.popup-chat')[0];
            if (!popup) { return; }
            var profileId = popup.getAttribute('data-ai-profile-id');

            popup.dataset.from = $(t).val();

            var list = popup.querySelector('.chat-message-field');
            if (list) { list.innerHTML = ''; }

            aiConversations[profileId] = null;
            aiLoadHistory(popup, profileId);
        };

        // Admin-only: opens (or focuses) the AI chat popup for the given AI profile already
        // switched to the given managed account ("AI Inbox" list above the page). Reuses
        // ai_chat_open()/ai_change_user() rather than duplicating their logic.
        window.ai_inbox_open = function (profileId, name, imageUrl, fromUserId) {
            ai_chat_open(profileId, name, imageUrl);

            var popup = document.querySelector('.chats .popup-chat[data-id="ai-' + profileId + '"]');
            if (!popup) { return; }

            var select = popup.querySelector('#admin_account');
            if (select && fromUserId) {
                select.value = String(fromUserId);
                ai_change_user(select);
            }
        };

        window.ai_send_msg = function (e, t, isBtn) {
            var code = e ? (e.keyCode ? e.keyCode : e.which) : 13;
            if (code !== 13 && !isBtn) { return; }

            if (isBtn) {
                t = $(t).closest('.chatform').find('textarea')[0];
            }
            if (e && e.preventDefault) { e.preventDefault(); }

            var text = ($(t).val() || '').trim();
            if (!text) { return; }

            var popup = $(t).closest('.popup-chat')[0];
            if (!popup) { return; }
            var profileId = popup.getAttribute('data-ai-profile-id');

            $(t).val('');
            aiAppendMessage(popup, true, text);

            var stopTyping = AI_SIMULATE_TYPING ? aiShowTypingIndicator(popup) : function () {};

            fetch(popup.dataset.chatEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ai_profile_id: profileId,
                    message: text,
                    conversation_id: aiConversations[profileId] || null,
                    input_channel: 'text',
                    from: popup.dataset.from || 'no'
                })
            }).then(function (resp) {
                return resp.json().catch(function () { return {}; }).then(function (data) {
                    return { resp: resp, data: data };
                });
            }).then(function (result) {
                var resp = result.resp, data = result.data;
                if (!resp.ok) {
                    stopTyping();
                    aiAppendMessage(popup, false, data.message || ('Error ' + resp.status));
                    return;
                }
                aiConversations[profileId] = data.conversation_id;
                var assistantText = data.assistant_text || '...';
                var delay = AI_SIMULATE_TYPING ? aiTypingDelayFor(assistantText) : 0;

                setTimeout(function () {
                    stopTyping();
                    aiAppendMessage(popup, false, assistantText);
                }, delay);
            }).catch(function (err) {
                stopTyping();
                aiAppendMessage(popup, false, 'Network error: ' + err.message);
            });
        };

        var aiActiveCallPopup = null;

        // Renders a Daily.co call's tracks as plain <video>/<audio> elements into `container`,
        // bypassing Daily's own Prebuilt UI entirely (no header/toolbar chrome - just the raw
        // video). The remote (AI) video fills the container; the local participant's own camera
        // - if turned on - is rendered as a small corner picture-in-picture. The local mic is
        // never played back locally (that would just echo the user's own voice).
        function createDailyRenderer(container, blurAmount, audioMuted) {
            var mediaElements = {};

            function elementKey(participantId, kind) { return participantId + ':' + kind; }

            function ensureElement(participantId, kind, isLocal) {
                var key = elementKey(participantId, kind);
                if (mediaElements[key]) { return mediaElements[key]; }
                var el = document.createElement(kind === 'video' ? 'video' : 'audio');
                el.autoplay = true;
                el.playsInline = true;
                if (kind === 'video') {
                    el.className = isLocal ? 'ai-daily-local-video' : 'ai-daily-remote-video';
                    if (!isLocal && blurAmount > 0) {
                        el.classList.add('ai-video-blurred');
                        el.style.filter = 'blur(' + blurAmount + 'px)';
                    }
                } else if (!isLocal && audioMuted) {
                    // Video keeps playing (and lip-syncing) as normal — only the AI's voice
                    // is silenced client-side.
                    el.muted = true;
                }
                container.appendChild(el);
                mediaElements[key] = el;
                return el;
            }

            function attachTrack(ev) {
                if (!ev.track) { return; }
                var isLocal = !!(ev.participant && ev.participant.local);
                if (isLocal && ev.track.kind === 'audio') { return; }
                var participantId = ev.participant ? ev.participant.session_id : 'remote';
                var el = ensureElement(participantId, ev.track.kind, isLocal);
                el.srcObject = new MediaStream([ev.track]);
                el.play().catch(function () {});
            }

            function detachTrack(participantId, kind) {
                var key = elementKey(participantId, kind);
                var el = mediaElements[key];
                if (el) {
                    el.srcObject = null;
                    if (el.parentNode) { el.parentNode.removeChild(el); }
                    delete mediaElements[key];
                }
            }

            function detachParticipant(participantId) {
                ['video', 'audio'].forEach(function (kind) { detachTrack(participantId, kind); });
            }

            function destroy() {
                Object.keys(mediaElements).forEach(function (key) {
                    var el = mediaElements[key];
                    el.srcObject = null;
                    if (el.parentNode) { el.parentNode.removeChild(el); }
                });
                mediaElements = {};
            }

            return {
                attachTrack: attachTrack,
                detachTrack: detachTrack,
                detachParticipant: detachParticipant,
                destroy: destroy
            };
        }

        function createCallTimer(container) {
            var el = document.createElement('div');
            el.className = 'ai-call-timer';
            el.textContent = '00:00';
            container.appendChild(el);

            var startedAt = Date.now();
            var intervalId = setInterval(function () {
                var elapsed = Math.floor((Date.now() - startedAt) / 1000);
                var m = Math.floor(elapsed / 60);
                var s = elapsed % 60;
                el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }, 1000);

            return {
                destroy: function () {
                    clearInterval(intervalId);
                    if (el.parentNode) { el.parentNode.removeChild(el); }
                }
            };
        }

        function startDailyCall(container, roomUrl, blurAmount, audioMuted) {
            container.innerHTML = '';
            var renderer = createDailyRenderer(container, blurAmount, audioMuted);
            var timer = createCallTimer(container);
            var callObject = window.DailyIframe.createCallObject();

            callObject.on('track-started', renderer.attachTrack);
            callObject.on('track-stopped', function (ev) {
                if (!ev.track) { return; }
                var participantId = ev.participant ? ev.participant.session_id : 'remote';
                renderer.detachTrack(participantId, ev.track.kind);
            });
            callObject.on('participant-left', function (ev) {
                if (ev.participant) { renderer.detachParticipant(ev.participant.session_id); }
            });

            callObject.join({ url: roomUrl, startVideoOff: !AI_LIVE_AUTO_START_CAMERA }).then(function () {
                if (AI_LIVE_AUTO_START_CAMERA) {
                    // join()'s startVideoOff option doesn't reliably turn the camera on by
                    // itself in every browser - explicitly (re)requesting it the same way
                    // the manual toggle button does is what actually starts the track.
                    callObject.setLocalVideo(true);
                }
            }).catch(function (err) {
                console.error('Daily join failed', err);
            });

            return { callObject: callObject, renderer: renderer, timer: timer, camOn: AI_LIVE_AUTO_START_CAMERA, micOn: true };
        }

        function stopDailyCall(handle) {
            if (!handle) { return; }
            if (handle.timer) { handle.timer.destroy(); }
            if (handle.renderer) { handle.renderer.destroy(); }
            if (handle.callObject) {
                handle.callObject.leave().catch(function () {}).then(function () {
                    return handle.callObject.destroy();
                }).catch(function () {});
            }
        }

        // .ui-block-title icons follow an existing green/red convention already defined in
        // style.css (fa-video/fa-microphone/fa-phone = on/green, their -slash variants = off/red)
        // - swapping between the two classes is all that's needed to reflect state visually.
        function setIconGlyph(iconEl, onClass, offClass, isOn) {
            if (!iconEl) { return; }
            iconEl.classList.toggle(onClass, isOn);
            iconEl.classList.toggle(offClass, !isOn);
        }

        function toggleLocalCamera(handle, iconEl) {
            if (!handle || !handle.callObject) { return; }
            handle.camOn = !handle.camOn;
            handle.callObject.setLocalVideo(handle.camOn);
            setIconGlyph(iconEl, 'fa-video', 'fa-video-slash', handle.camOn);
            if (iconEl) { iconEl.title = handle.camOn ? 'Turn off camera' : 'Turn on camera'; }
        }

        function toggleLocalMic(handle, iconEl) {
            if (!handle || !handle.callObject) { return; }
            handle.micOn = !handle.micOn;
            handle.callObject.setLocalAudio(handle.micOn);
            setIconGlyph(iconEl, 'fa-microphone', 'fa-microphone-slash', handle.micOn);
            if (iconEl) { iconEl.title = handle.micOn ? 'Mute microphone' : 'Unmute microphone'; }
        }

        // Populates the chat popup's own header .video-controls slot (next to the msgtoggle
        // eye icon) with mic/camera icons for the active live session - matching how a normal
        // video-call header looks, instead of hiding them inside the video area itself.
        function populateVideoControls(popup, handle, fullscreenTarget) {
            var container = popup.querySelector('.video-controls');
            if (!container) { return; }
            container.innerHTML = '';

            var micIcon = document.createElement('i');
            micIcon.className = 'fas fa-microphone';
            micIcon.title = 'Mute microphone';
            micIcon.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleLocalMic(handle, micIcon);
            });

            var camIcon = document.createElement('i');
            camIcon.className = handle.camOn ? 'fas fa-video' : 'fas fa-video-slash';
            camIcon.title = handle.camOn ? 'Turn off camera' : 'Turn on camera';
            camIcon.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleLocalCamera(handle, camIcon);
            });

            container.appendChild(micIcon);
            container.appendChild(camIcon);

            if (fullscreenTarget) {
                var fsIcon = document.createElement('i');
                fsIcon.className = 'fas fa-window-maximize';
                fsIcon.title = 'Fullscreen';
                fsIcon.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleElementFullscreen(fullscreenTarget);
                });
                container.appendChild(fsIcon);
            }
        }

        function clearVideoControls(popup) {
            var container = popup.querySelector('.video-controls');
            if (container) { container.innerHTML = ''; }
        }

        function setCallIconActive(iconEl, active) {
            if (!iconEl) { return; }
            iconEl.classList.remove('fa-spinner', 'fa-spin');
            iconEl.classList.toggle('fa-phone', !active);
            iconEl.classList.toggle('fa-phone-slash', active);
        }

        function requestElementFullscreen(el) {
            if (!el) { return; }
            if (el.requestFullscreen) { el.requestFullscreen(); }
            else if (el.webkitRequestFullscreen) { el.webkitRequestFullscreen(); }
            else if (el.msRequestFullscreen) { el.msRequestFullscreen(); }
        }

        function exitFullscreenIfActive() {
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                if (document.exitFullscreen) { document.exitFullscreen().catch(function () {}); }
                else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
            }
        }

        function toggleElementFullscreen(el) {
            var current = document.fullscreenElement || document.webkitFullscreenElement;
            if (current) {
                exitFullscreenIfActive();
            } else {
                requestElementFullscreen(el);
            }
        }

        function initAiLiveWindowDrag() {
            var win = document.getElementById('ai-live-window');
            var header = document.getElementById('ai-live-window-header');
            var closeBtn = document.getElementById('ai-live-window-close');
            var fullscreenBtn = document.getElementById('ai-live-window-fullscreen');
            if (!win || !header || win.dataset.dragBound) { return; }
            win.dataset.dragBound = '1';

            closeBtn.addEventListener('click', function () {
                if (aiActiveCallPopup) {
                    var profileId = aiActiveCallPopup.getAttribute('data-ai-profile-id');
                    if (aiActiveSessions[profileId]) { endAiLiveSession(aiActiveCallPopup, profileId); }
                }
            });

            fullscreenBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleElementFullscreen(win);
            });

            var dragging = false, startX = 0, startY = 0, startLeft = 0, startTop = 0;

            function pointerPos(e) {
                if (e.touches && e.touches[0]) { return { x: e.touches[0].clientX, y: e.touches[0].clientY }; }
                return { x: e.clientX, y: e.clientY };
            }

            function dragStart(e) {
                dragging = true;
                var p = pointerPos(e);
                startX = p.x;
                startY = p.y;
                var rect = win.getBoundingClientRect();
                startLeft = rect.left;
                startTop = rect.top;
                win.style.right = 'auto';
                win.style.left = startLeft + 'px';
                win.style.top = startTop + 'px';
            }

            function dragMove(e) {
                if (!dragging) { return; }
                var p = pointerPos(e);
                var newLeft = startLeft + (p.x - startX);
                var newTop = startTop + (p.y - startY);
                var maxLeft = window.innerWidth - win.offsetWidth;
                var maxTop = window.innerHeight - win.offsetHeight;
                newLeft = Math.min(Math.max(newLeft, 0), Math.max(maxLeft, 0));
                newTop = Math.min(Math.max(newTop, 0), Math.max(maxTop, 0));
                win.style.left = newLeft + 'px';
                win.style.top = newTop + 'px';
                e.preventDefault();
            }

            function dragEnd() { dragging = false; }

            header.addEventListener('mousedown', dragStart);
            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
            header.addEventListener('touchstart', dragStart, { passive: true });
            document.addEventListener('touchmove', dragMove, { passive: false });
            document.addEventListener('touchend', dragEnd);
        }

        window.ai_toggle_live = function (t, event) {
            if (event) { event.stopPropagation(); }
            if (t.classList.contains('fa-spinner')) { return; }

            var popup = $(t).closest('.popup-chat')[0];
            if (!popup) { return; }

            var profileId = popup.getAttribute('data-ai-profile-id');
            if (aiActiveSessions[profileId]) {
                endAiLiveSession(popup, profileId);
            } else {
                t.classList.remove('fa-phone');
                t.classList.add('fa-spinner', 'fa-spin');
                startAiLiveSession(popup, profileId, t);
            }
        };

        window.ai_close_popup = function (t) {
            var popup = $(t).closest('.popup-chat')[0];
            if (popup) {
                var profileId = popup.getAttribute('data-ai-profile-id');
                if (profileId && aiActiveSessions[profileId]) {
                    endAiLiveSession(popup, profileId);
                }
            }
            close_chat(t);
        };

        function startAiLiveSession(popup, profileId, iconEl) {
            fetch(popup.dataset.videoSessionStart, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ ai_profile_id: profileId, conversation_id: aiConversations[profileId] || null })
            }).then(function (resp) {
                return resp.json().catch(function () { return {}; }).then(function (data) {
                    return { resp: resp, data: data };
                });
            }).then(function (result) {
                var resp = result.resp, data = result.data;
                var roomUrl = data.room_url || data.conversation_url;
                if (!resp.ok || !roomUrl) {
                    setCallIconActive(iconEl, false);
                    alert('Failed to start live session: ' + (data.message || 'Unknown error'));
                    return;
                }
                setCallIconActive(iconEl, true);
                var layout = popup.dataset.videoLayout === 'docked' ? 'docked' : 'floating';
                var blurAmount = popup.dataset.videoBlur === '1' ? (parseInt(popup.dataset.videoBlurAmount, 10) || 20) : 0;
                var audioMuted = popup.dataset.audioMuted === '1';
                aiActiveSessions[profileId] = { sessionId: data.session_id || data.tavus_conversation_id || null, provider: data.provider || null, layout: layout };

                if (layout === 'docked') {
                    var ubgvideo = popup.querySelector('.ubgvideo');
                    ubgvideo.innerHTML = '';

                    var videoSlot = document.createElement('div');
                    videoSlot.className = 'ai-docked-video-slot';

                    ubgvideo.appendChild(videoSlot);
                    ubgvideo.classList.add('ai-docked-active');
                    popup.classList.add('ai-docked-open');

                    ubgvideo._dailyCallHandle = startDailyCall(videoSlot, roomUrl, blurAmount, audioMuted);
                    populateVideoControls(popup, ubgvideo._dailyCallHandle, ubgvideo);
                } else {
                    aiActiveCallPopup = popup;
                    initAiLiveWindowDrag();
                    var win = document.getElementById('ai-live-window');
                    document.getElementById('ai-live-modal-title').textContent = popup.querySelector('.title').textContent;
                    var frameContainer = document.getElementById('ai-live-modal-frame');
                    win.style.display = 'flex';
                    frameContainer._dailyCallHandle = startDailyCall(frameContainer, roomUrl, blurAmount, audioMuted);
                    populateVideoControls(popup, frameContainer._dailyCallHandle);
                }
            }).catch(function (err) {
                setCallIconActive(iconEl, false);
                alert('Network error: ' + err.message);
            });
        }

        function endAiLiveSession(popup, profileId) {
            var session = aiActiveSessions[profileId];

            exitFullscreenIfActive();
            clearVideoControls(popup);
            setCallIconActive(popup.querySelector('.videocallicon i'), false);

            if (session && session.layout === 'docked') {
                var ubgvideo = popup.querySelector('.ubgvideo');
                stopDailyCall(ubgvideo._dailyCallHandle);
                ubgvideo._dailyCallHandle = null;
                ubgvideo.innerHTML = '';
                ubgvideo.classList.remove('ai-docked-active');
                popup.classList.remove('ai-docked-open');
            } else {
                var frameContainer = document.getElementById('ai-live-modal-frame');
                stopDailyCall(frameContainer._dailyCallHandle);
                frameContainer._dailyCallHandle = null;
                frameContainer.innerHTML = '';
                document.getElementById('ai-live-window').style.display = 'none';
                if (aiActiveCallPopup === popup) { aiActiveCallPopup = null; }
            }

            if (session && session.sessionId) {
                fetch(popup.dataset.videoSessionEnd, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ session_id: session.sessionId, tavus_conversation_id: session.sessionId, provider: session.provider || undefined })
                }).catch(function () {});
            }
            delete aiActiveSessions[profileId];
        }
    })();
    </script>
    @endsection
@endauth
