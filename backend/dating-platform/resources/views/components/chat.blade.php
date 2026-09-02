@php
    // Fetched directly here (rather than passed in from a controller) since this component
    // is @included site-wide from the base layout, not rendered by one specific controller.
    $realAiSetting = \App\AISetting::current();
    $realAiVideoPrivacy = $realAiSetting->resolveVideoPrivacyForUser(Auth::user());
@endphp
<div class="chats">
<div id="popup-chat" class="ui-block popup-chat"
     data-real-ai-video-session-start="{{ route('chat_ai_video_session_start') }}"
     data-real-ai-video-session-end="{{ route('chat_ai_video_session_end') }}"
     data-real-ai-video-layout="{{ $realAiSetting->liveVideoLayout() }}"
     data-real-ai-video-blur-amount="{{ $realAiVideoPrivacy['amountPx'] }}"
     data-real-ai-audio-muted="{{ $realAiVideoPrivacy['audioMuted'] ? '1' : '0' }}">
                            <div class="ui-block-title" onclick="toggle_chat(this);">
                                <div class="status"><span class="icon-status online"></span></div>
                                <h6 class="title"></h6>
                                    <div class="msgtoggle"><i class="fas fa-eye-slash" onclick="show_hide_messages(this, event);"></i></div>
                                    <div class="video-controls"></div>

                                <div class="more">
                                    <span class="realaicallicon" style="display:none;"><i class="fas fa-video" onclick="real_ai_toggle_live(this, event);"></i></span>
                                    <span class="videocallicon"><i class="fas fa-phone" onclick="send_call(this);"></i></span>
                                    <svg class="olymp-little-delete js-chat-close" onClick="real_ai_close_popup(this);"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-little-delete"></use></svg>
                                </div>
                            </div>

                            @if(Auth::user()->isAdmin())
                            <select id="admin_account" onChange="change_user(this);">
                                <option value="{{Auth::id()}}">{{Auth::user()->name()}}</option>
                                @php
                                    $adminAccounts = Auth::user()->getAccounts();
                                    $adminAccounts = $adminAccounts->load('getUserRelationship');
                                @endphp
                                @foreach($adminAccounts as $acc)
                                    @if($acc->getUserRelationship)
                                        <option value="{{$acc->getUserRelationship->id}}">{{$acc->getUserRelationship->name()}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div id="ai-pause-toggle-row" style="text-align:center;">
                                <button type="button" id="ai-pause-toggle" class="btn btn-sm" style="display:none;" onclick="toggle_ai(this);"></button>
                            </div>
                            @php

                              $langs = App\Lang::get();

                            @endphp
                            <div class="row m-0">
                            <div class="col-xs-6 w-50">
                            <select class="lang_for_user" onChange="change_user_lang(this);">
                                @foreach($langs as $lang)
                                <option value="{{$lang->code}}">{{$lang->name}}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="col-xs-6 w-50">
                            <select class="auto_translate">
                                <option selected value="yes">Traducere activa</option>
                                <option value="no">Traducere dezactivata</option>
                            </select>
                            </div>
                            </div>
                            @endif

                            <div class="ubgvideo"></div>


                            <div onscroll="load_messages(this);" class="mCustomScrollbar ps ps--theme_default ps--active-y" data-mcs-theme="dark" data-ps-id="22d8324a-2107-44b7-ac86-3396f06ca414">

                                <ul class="notification-list chat-message chat-message-field">

                                </ul>

                            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__scrollbar-y-rail" style="top: 0px; height: 350px; right: 0px;"><div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 255px;"></div></div></div>

                            <form class="chatform">

                                @if(Auth::user()->isAdmin())
                                    <div id="ai-paused-banner" style="display:none;">{{ l('AI paused — you are replying manually') }}</div>
                                @endif

                                <div class="form-group label-floating is-empty">
                                    <label class="control-label">{{l("Press enter to post...")}}</label>
                                    <textarea class="form-control chatbox" placeholder="" onfocus="seen_message(this);" onkeypress="send_msg(event, this);"></textarea>
                                    <div class="add-options-message">
                                        <i class="fas fa-paper-plane" onclick='send_msg(event, this, true);'></i>
                                    </div>
                                <span class="material-input"></span></div>

                            </form>



                        </div>

                    </div>
                    <input id="auth_id" type="hidden" value="{{Auth::id()}}">


                    <!-- Modal -->
<div class="modal fade" id="videochat" tabindex="-1" role="dialog" aria-labelledby="videochatLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div id="video_container" class="modal-body">
        <div class="calling_now">
            <img id="profile_img_call" src="/storage/images/default.png">
            <span class="caller_name">Client</span>
            <span class="call_state">dialing...</span>
        </div>
       <video id="myvideo" muted></video>
       <video id="peerVideo"></video>
       <span id="call_timer">0:00</span>
       @php
        /*
       @endphp
       <button id="mute_mic" type="button" onclick="mute_mic();"><i class="fas fa-microphone"></i></button>
       <button id="end_call" type="button" onclick="close_call();"><i class="fas fa-phone-slash"></i></button>
       <button id="remove_video" type="button" onclick="remove_video();"><i class="fas fa-video"></i></button>
       @php
        */
       @endphp
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="is_calling" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div id="caller_details" class="modal-body">
        <div class="caller_details">
            <img src="">
            <span class="caller_name">Client</span>
            <span class="call_state">is calling...</span>
        </div>
        <div class="call_options">
            <ul>
                <li><button id="answer_call"><i class="fas fa-phone-volume"></i></button></li>
                <li><button id="refuse_call"><i class="fas fa-phone-slash"></i></button></li>
            </ul>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Live AI video call for real (non-catalog) female profiles, site-wide (this component is
     included on every page via layouts/layout.blade.php). Fully separate markup/IDs/JS from
     the AI Companions live video window (#ai-live-window in ai_chat_popup.blade.php) so the
     two features never collide - they can even be open on the same page (find_friends) at
     the same time. Rendering approach (raw <video> elements off the Daily.co call object,
     no Daily Prebuilt UI/chrome) mirrors that existing window exactly. --}}
<script src="https://unpkg.com/@daily-co/daily-js"></script>
<div id="real-ai-live-window">
    <div id="real-ai-live-window-header">
        <strong id="real-ai-live-modal-title"></strong>
        <span class="real-ai-live-window-actions">
            <span id="real-ai-live-window-fullscreen" title="{{ l('Fullscreen') }}">⛶</span>
            <span id="real-ai-live-window-close" title="{{ l('Close') }}">&times;</span>
        </span>
    </div>
    <div id="real-ai-live-modal-frame"></div>
</div>

<style>
    #real-ai-live-window {
        display: none;
        position: fixed;
        top: 90px;
        right: 400px;
        width: 360px;
        height: 300px;
        max-width: calc(100vw - 20px);
        max-height: calc(100vh - 20px);
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 25px rgba(0, 0, 0, .4);
        z-index: 2000;
        flex-direction: column;
    }
    #real-ai-live-window:fullscreen,
    #real-ai-live-window:-webkit-full-screen {
        width: 100vw;
        height: 100vh;
        max-width: 100vw;
        max-height: 100vh;
        top: 0;
        left: 0;
        right: auto;
        border-radius: 0;
    }
    #real-ai-live-window-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 12px;
        background: #6c3ce9;
        color: #fff;
        cursor: move;
        user-select: none;
        flex-shrink: 0;
    }
    .real-ai-live-window-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    #real-ai-live-window-fullscreen,
    #real-ai-live-window-close {
        cursor: pointer;
        opacity: .9;
    }
    #real-ai-live-window-fullscreen {
        font-size: 25px;
    }
    #real-ai-live-window-close {
        font-size: 35px;
        line-height: 1;
    }
    #real-ai-live-modal-frame {
        flex: 1;
        width: 100%;
        display: block;
        position: relative;
    }
    #real-ai-live-modal-frame .real-ai-daily-remote-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    #real-ai-live-modal-frame .real-ai-daily-local-video {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 25%;
        max-width: 110px;
        aspect-ratio: 3 / 4;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid rgba(255, 255, 255, .6);
        transform: scaleX(-1);
        z-index: 3;
        display: block;
    }
    .real-ai-daily-remote-video.real-ai-video-blurred {
        filter: blur(20px);
    }
    .real-ai-call-timer {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0, 0, 0, .55);
        color: #fff;
        font-size: 12px;
        line-height: 1;
        padding: 4px 8px;
        border-radius: 10px;
        font-variant-numeric: tabular-nums;
        z-index: 4;
        pointer-events: none;
    }
    @media screen and (max-width: 768px) {
        #real-ai-live-window {
            width: calc(100vw - 20px);
            height: 45vh;
            right: 10px;
            left: 10px;
            top: 70px;
        }
    }
    .popup-chat .realaicallicon i.fa-video {
        color: #6c3ce9;
    }

    /* Package-tier blur applied to the chat popup's own background photo (see
       ChatController::get()'s background_blur_amount / AISetting::resolveVideoPrivacyForUser()
       - same rule as the live AI video call). The photo lives on its own layer with a negative
       z-index so the blur filter (which would otherwise blur the whole element it's applied to,
       messages included) only ever touches the photo, never the message bubbles on top of it. */
    .popup-chat .mCustomScrollbar {
        position: relative;
    }
    .popup-chat .mCustomScrollbar .real-ai-bg-photo {
        position: absolute;
        inset: 0;
        z-index: -1;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: 50% 50%;
        pointer-events: none;
    }

    /* Docked layout (mirrors the AI Companions docked mode in ai_chat_popup.blade.php, but
       duplicated with its own class names since this component is loaded site-wide and can't
       rely on that other file's <style> block being present on the current page): video fills
       the whole popup body as a full-bleed background layer, message log/chatform float on
       top of it. */
    .popup-chat .ubgvideo.real-ai-docked-active {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background: #000;
        overflow: hidden;
        z-index: 0;
    }
    .popup-chat .ubgvideo.real-ai-docked-active:fullscreen,
    .popup-chat .ubgvideo.real-ai-docked-active:-webkit-full-screen {
        width: 100vw;
        height: 100vh;
    }
    .popup-chat.real-ai-docked-open .mCustomScrollbar {
        position: relative;
        z-index: 1;
        background: transparent !important;
        height: auto !important;
        min-height: 260px;
    }
    .popup-chat.real-ai-docked-open .chatform {
        position: relative;
        z-index: 1;
        background: transparent;
    }
    .popup-chat .ubgvideo.real-ai-docked-active .real-ai-docked-video-slot {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .popup-chat .ubgvideo.real-ai-docked-active .real-ai-docked-video-slot .real-ai-daily-remote-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        min-width: 100%;
        min-height: 100%;
        max-width: none;
        object-fit: cover;
        transform: none;
        display: block;
    }
    .popup-chat .ubgvideo.real-ai-docked-active .real-ai-docked-video-slot .real-ai-daily-local-video {
        position: absolute;
        top: 70px;
        left: auto;
        bottom: auto;
        right: 8px;
        width: 30%;
        max-width: 90px;
        min-width: 0;
        height: auto;
        min-height: 0;
        aspect-ratio: 3 / 4;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid rgba(255, 255, 255, .6);
        transform: scaleX(-1);
        z-index: 4;
        display: block;
    }
</style>
