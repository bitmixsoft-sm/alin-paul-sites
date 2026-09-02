<div id="ai-popup-template" class="ui-block" style="display:none;"
     data-user-avatar="{{ Auth::check() ? '/storage/images/' . Auth::user()->profile_image() : '' }}"
     data-video-layout="{{ $aiSetting->liveVideoLayout() }}"
     data-video-blur="{{ $aiSetting->liveVideoBlurEnabled() ? '1' : '0' }}"
     data-video-blur-amount="{{ $videoBlurAmount ?? $aiSetting->liveVideoBlurAmount() }}"
     data-audio-muted="{{ ($audioMuted ?? false) ? '1' : '0' }}"
     data-chat-endpoint="{{ route('ai_chat_send') }}"
     data-history-endpoint="{{ route('ai_chat_history') }}"
     data-video-session-start="{{ route('ai_video_session_start') }}"
     data-video-session-end="{{ route('ai_video_session_end') }}">
    <div class="ui-block-title" onclick="toggle_chat(this);">
        <div class="status"><span class="icon-status online"></span></div>
        <h6 class="title"></h6>
        <div class="msgtoggle"><i class="fas fa-eye-slash" onclick="show_hide_messages(this, event);"></i></div>
        <div class="video-controls"></div>
        <div class="more">
            <span class="videocallicon"><i class="fas fa-phone" onclick="ai_toggle_live(this, event);"></i></span>
            <svg class="olymp-little-delete js-chat-close" onClick="ai_close_popup(this);"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-little-delete"></use></svg>
        </div>
    </div>

    @if(Auth::check() && Auth::user()->isAdmin())
        <select id="admin_account" onChange="ai_change_user(this);">
            <option value="{{ Auth::id() }}">{{ Auth::user()->name() }}</option>
            @php
                $adminAccounts = Auth::user()->getAccounts();
                $adminAccounts = $adminAccounts->load('getUserRelationship');
            @endphp
            @foreach($adminAccounts as $acc)
                @if($acc->getUserRelationship)
                    <option value="{{ $acc->getUserRelationship->id }}">{{ $acc->getUserRelationship->name() }}</option>
                @endif
            @endforeach
        </select>
    @endif

    <div class="ubgvideo"></div>

    <div class="mCustomScrollbar ps ps--theme_default ps--active-y">
        <ul class="notification-list chat-message chat-message-field"></ul>
    </div>

    <form class="chatform">
        <div class="form-group label-floating is-empty">
            <label class="control-label">{{ l('Press enter to post...') }}</label>
            <textarea class="form-control chatbox" onkeypress="ai_send_msg(event, this);"></textarea>
            <div class="add-options-message">
                <i class="fas fa-paper-plane" onclick="ai_send_msg(event, this, true);"></i>
            </div>
            <span class="material-input"></span>
        </div>
    </form>
</div>

{{-- Live video (Simli/Tavus) is rendered directly from the underlying Daily.co call object
     (see startDailyCall() in find_friends.blade.php) instead of embedding Daily's own Prebuilt
     iframe UI, so none of Daily's own header/toolbar chrome (mute/camera/people/more/leave) is
     shown - only the raw video. In "floating" layout it gets its own small, freely-draggable
     window (not a blocking modal), so the chat popup stays visible and usable underneath it at
     the same time. Single shared window - only one AI call can be open at a time. In "docked"
     layout the video is embedded directly inside the chat popup's own .ubgvideo slot instead
     (see ai_docked_active handling in JS). --}}
<script src="https://unpkg.com/@daily-co/daily-js"></script>
<div id="ai-live-window">
    <div id="ai-live-window-header">
        <strong id="ai-live-modal-title"></strong>
        <span class="ai-live-window-actions">
            <span id="ai-live-window-fullscreen" title="{{ l('Fullscreen') }}">⛶</span>
            <span id="ai-live-window-close" title="{{ l('Close') }}">&times;</span>
        </span>
    </div>
    <div id="ai-live-modal-frame"></div>
</div>

<style>
    #ai-live-window {
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
    #ai-live-window:fullscreen,
    #ai-live-window:-webkit-full-screen {
        width: 100vw;
        height: 100vh;
        max-width: 100vw;
        max-height: 100vh;
        top: 0;
        left: 0;
        right: auto;
        border-radius: 0;
    }
    #ai-live-window-header {
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
    .ai-live-window-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    #ai-live-window-fullscreen,
    #ai-live-window-close {
        cursor: pointer;
        opacity: .9;
    }
    #ai-live-window-fullscreen {
        font-size: 25px;
    }
    #ai-live-window-close {
        font-size: 35px;
        line-height: 1;
    }
    #ai-live-modal-frame {
        flex: 1;
        width: 100%;
        display: block;
        position: relative;
    }
    #ai-live-modal-frame .ai-daily-remote-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    #ai-live-modal-frame .ai-daily-local-video {
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
    .ai-daily-remote-video.ai-video-blurred {
        filter: blur(20px);
    }
    .ai-call-timer {
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
        #ai-live-window {
            width: calc(100vw - 20px);
            height: 45vh;
            right: 10px;
            left: 10px;
            top: 70px;
        }
    }

    /* Docked layout: video fills the whole popup body as a full-bleed background layer: the
       message log and chatform float on top of it (transparent backgrounds, higher z-index)
       so the video shows through everywhere except the chat bubbles/input themselves - matching
       the plain (non-AI) popup-chat's own "video behind text" look. The header stays opaque and
       on top via its own z-index (see .popup-chat .ui-block-title{z-index:1} in style.css). */
    .popup-chat .ubgvideo.ai-docked-active {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background: #000;
        overflow: hidden;
        z-index: 0;
    }
    .popup-chat .ubgvideo.ai-docked-active:fullscreen,
    .popup-chat .ubgvideo.ai-docked-active:-webkit-full-screen {
        width: 100vw;
        height: 100vh;
    }
    .popup-chat.ai-docked-open .mCustomScrollbar {
        position: relative;
        z-index: 1;
        background: transparent !important;
        height: auto !important;
        min-height: 260px;
    }
    .popup-chat.ai-docked-open .chatform {
        position: relative;
        z-index: 1;
        background: transparent;
    }
    .popup-chat .ubgvideo.ai-docked-active .ai-docked-video-slot {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    /* .popup-chat video (see style.css) sets min-width/min-height:100%, top/left:50% and a
       centering transform on *every* video in the popup - these rules explicitly override all
       of those per property (not just width/position) so our own sizing/positioning wins
       regardless of that legacy rule's specificity vs. ours on non-conflicting properties. */
    .popup-chat .ubgvideo.ai-docked-active .ai-docked-video-slot .ai-daily-remote-video {
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
    .popup-chat .ubgvideo.ai-docked-active .ai-docked-video-slot .ai-daily-local-video {
        /* Anchored below the header (not the bottom) so the opaque chatform/input bar never
           overlaps and clips it. */
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

    .video-controls i.fas{
        font-size: large !important;
        margin: 0 3px !important;
    }
</style>
