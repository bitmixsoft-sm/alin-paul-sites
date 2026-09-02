<div id="ai-avatar-chat-app"
     data-chat-endpoint="{{ route('ai_chat_send') }}"
     data-video-status-endpoint="{{ route('ai_chat_video_status') }}"
     data-video-session-start="{{ route('ai_video_session_start') }}"
     data-video-session-end="{{ route('ai_video_session_end') }}">
    <div class="row">
        <div id="avatar-column" class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><strong>AI Avatar</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="profile-id">Choose profile</label>
                        <select id="profile-id" class="form-control">
                            <option value="">Select an avatar</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile['id'] }}">{{ $profile['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info small mb-0">
                        Free users have a limited number of messages. You will see an upgrade prompt when the limit is reached.
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><strong>Avatar Stream</strong></div>
                <div class="card-body">
                    <div id="avatar-live-container" style="display:none;">
                        <iframe id="avatar-live-frame" allow="camera; microphone; autoplay; display-capture; picture-in-picture" style="width:100%; min-height:480px; border-radius:8px; border:1px solid #ddd;"></iframe>
                        <div style="margin-top:8px; display:flex; gap:8px; align-items:center;">
                            <span class="badge badge-success">Live session active</span>
                            <button id="fullscreen-live-session" class="btn btn-sm btn-outline-secondary">⛶ Fullscreen</button>
                            <button id="end-live-session" class="btn btn-sm btn-danger">End Live Session</button>
                        </div>
                    </div>
                    <div id="avatar-clip-container">
                        @if($aiSetting->avatarVideoEnabled())
                            <video id="avatar-video" controls playsinline style="width:100%; border-radius: 8px;"></video>
                            <audio id="avatar-audio" controls style="width:100%; margin-top: 10px;"></audio>
                        @endif
                        <div id="video-status" class="small text-muted mt-2">Waiting for response...</div>
                        <button id="start-live-session" class="btn btn-sm btn-outline-success mt-1" style="width:100%;">Start Live Video Session</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="conversation-column" class="col-md-8">
            <div class="card">
                <div class="card-header"><strong>Conversation</strong></div>
                <div class="card-body">
                    <div id="chat-log" style="height: 420px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background: #f9fafb;"></div>
                    <div id="upgrade-banner" class="alert alert-warning mt-3" style="display:none;"></div>
                    <div class="input-group mt-3">
                        <input id="chat-input" type="text" class="form-control" maxlength="5000" placeholder="Type your message...">
                        <span class="input-group-btn">
                            <button id="send-message" class="btn btn-primary" type="button" onclick="if(window.__aiAvatarChatSend){window.__aiAvatarChatSend();}else{alert('Chat script is not ready. Please refresh with Ctrl+F5.');}">Send</button>
                        </span>
                    </div>
                    <div class="small text-muted mt-2">Voice input button can be wired to browser SpeechRecognition in the next step.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        function dbg(msg) { console.log('[AI Chat]', msg); }

        function addMsg(role, text) {
            var log = document.getElementById('chat-log');
            if (!log) { return; }
            var el = document.createElement('div');
            el.className = 'mb-2';
            el.innerHTML = '<strong>' + role + ':</strong> ' + String(text);
            log.appendChild(el);
            log.scrollTop = log.scrollHeight;
        }

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }

        async function pollVideoStatus(videoId, vidEl, vidStatus) {
            var root = document.getElementById('ai-avatar-chat-app');
            var endpoint = root && root.dataset ? root.dataset.videoStatusEndpoint : null;
            if (!endpoint || !videoId) { return; }

            var maxAttempts = 24;
            for (var i = 0; i < maxAttempts; i++) {
                try {
                    var resp = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ video_id: videoId })
                    });

                    var data = {};
                    try { data = await resp.json(); } catch (e) { data = {}; }

                    if (vidStatus) {
                        vidStatus.textContent = 'Status: ' + (data.status || 'pending');
                    }

                    if (data.video_url && vidEl) {
                        vidEl.src = data.video_url;
                        vidEl.play().catch(function(){});
                        return;
                    }

                    if (data.status === 'failed' || data.status === 'error') {
                        return;
                    }
                } catch (e) {
                    dbg('video poll exception: ' + e.message);
                }

                await new Promise(function(resolve) { setTimeout(resolve, 2500); });
            }
        }

        var conversationId = null;
        var activeSessionId = null;
        var activeProvider = null;

        async function startLiveSession() {
            var root = document.getElementById('ai-avatar-chat-app');
            var endpoint = root && root.dataset ? root.dataset.videoSessionStart : null;
            var sel = document.getElementById('profile-id');
            var startBtn = document.getElementById('start-live-session');

            if (!endpoint) { alert('Live session endpoint not configured.'); return; }
            var profileId = Number((sel || {}).value || 0);
            if (!profileId) { alert('Please choose an AI profile first.'); return; }

            if (startBtn) { startBtn.disabled = true; startBtn.textContent = 'Starting…'; }

            try {
                var resp = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ ai_profile_id: profileId, conversation_id: conversationId })
                });
                var data = {};
                try { data = await resp.json(); } catch (e) {}

                var roomUrl = data.room_url || data.conversation_url;

                if (!resp.ok || !roomUrl) {
                    alert('Failed to start live session: ' + (data.message || 'Unknown error'));
                    return;
                }

                activeSessionId = data.session_id || data.tavus_conversation_id || null;
                activeProvider = data.provider || null;
                var frame = document.getElementById('avatar-live-frame');
                if (frame) frame.src = roomUrl;

                document.getElementById('avatar-clip-container').style.display = 'none';
                document.getElementById('avatar-live-container').style.display = 'block';

                var avatarCol = document.getElementById('avatar-column');
                var convCol = document.getElementById('conversation-column');
                if (avatarCol) { avatarCol.classList.remove('col-md-4'); avatarCol.classList.add('col-md-8'); }
                if (convCol) { convCol.classList.remove('col-md-8'); convCol.classList.add('col-md-4'); }
            } catch (err) {
                alert('Network error: ' + err.message);
            } finally {
                if (startBtn) { startBtn.disabled = false; startBtn.textContent = 'Start Live Video Session'; }
            }
        }

        function fullscreenLiveSession() {
            var frame = document.getElementById('avatar-live-frame');
            if (!frame) { return; }
            if (frame.requestFullscreen) { frame.requestFullscreen(); }
            else if (frame.webkitRequestFullscreen) { frame.webkitRequestFullscreen(); }
            else if (frame.msRequestFullscreen) { frame.msRequestFullscreen(); }
        }

        async function endLiveSession() {
            var root = document.getElementById('ai-avatar-chat-app');
            var endpoint = root && root.dataset ? root.dataset.videoSessionEnd : null;

            document.getElementById('avatar-live-container').style.display = 'none';
            document.getElementById('avatar-clip-container').style.display = 'block';

            var avatarCol = document.getElementById('avatar-column');
            var convCol = document.getElementById('conversation-column');
            if (avatarCol) { avatarCol.classList.remove('col-md-8'); avatarCol.classList.add('col-md-4'); }
            if (convCol) { convCol.classList.remove('col-md-4'); convCol.classList.add('col-md-8'); }

            var frame = document.getElementById('avatar-live-frame');
            if (frame) frame.src = '';

            if (endpoint && activeSessionId) {
                try {
                    await fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                        body: JSON.stringify({ session_id: activeSessionId, tavus_conversation_id: activeSessionId, provider: activeProvider || undefined })
                    });
                } catch (e) {}
            }
            activeSessionId = null;
            activeProvider = null;
        }

        async function sendMessage() {
            dbg('sendMessage() called');

            var endpoint   = (document.getElementById('ai-avatar-chat-app') || {}).dataset
                             ? document.getElementById('ai-avatar-chat-app').dataset.chatEndpoint
                             : null;
            var sel        = document.getElementById('profile-id');
            var inp        = document.getElementById('chat-input');
            var btn        = document.getElementById('send-message');
            var banner     = document.getElementById('upgrade-banner');
            var vidStatus  = document.getElementById('video-status');
            var audEl      = document.getElementById('avatar-audio');
            var vidEl      = document.getElementById('avatar-video');

            dbg('endpoint=' + endpoint + ' sel=' + (sel ? sel.value : 'NULL') + ' inp=' + (inp ? inp.value : 'NULL'));

            if (!endpoint) { dbg('ERROR: endpoint missing'); return; }
            if (!sel)      { dbg('ERROR: profile select not found'); return; }
            if (!inp)      { dbg('ERROR: input not found'); return; }

            var profileId = Number(sel.value || 0);
            var message   = (inp.value || '').trim();

            dbg('profileId=' + profileId + ' message="' + message + '"');

            if (!profileId) {
                alert('Please choose an AI profile first.');
                return;
            }
            if (!message) {
                dbg('empty message — aborting');
                return;
            }

            if (banner) banner.style.display = 'none';
            addMsg('You', message);
            inp.value = '';
            if (btn) btn.disabled = true;

            dbg('fetching ' + endpoint);

            try {
                var resp = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ai_profile_id: profileId,
                        message: message,
                        conversation_id: conversationId,
                        input_channel: 'text'
                    })
                });

                dbg('HTTP ' + resp.status);

                var data;
                try { data = await resp.json(); } catch (e) { data = { message: 'Bad JSON from server.' }; }

                if (!resp.ok) {
                    if (resp.status === 402 && data.show_payment_modal) {
                        if (banner) { banner.style.display = 'block'; banner.innerText = data.message || 'Upgrade to continue.'; }
                        return;
                    }
                    addMsg('System', data.message || ('Error ' + resp.status));
                    return;
                }

                conversationId = data.conversation_id;
                addMsg('Avatar', data.assistant_text || '(no text)');

                if (data.audio_url && audEl) { audEl.src = data.audio_url; audEl.play().catch(function(){}); }
                if (data.video_url && vidEl) { vidEl.src = data.video_url; vidEl.play().catch(function(){}); }
                if (vidStatus) vidStatus.textContent = 'Status: ' + (data.video_status || 'pending');

                var videoId = null;
                if (data.meta && data.meta.video) {
                    if (data.meta.video.data && data.meta.video.data.video_id) {
                        videoId = data.meta.video.data.video_id;
                    } else if (data.meta.video.id) {
                        videoId = data.meta.video.id;
                    }
                }
                if (!data.video_url && videoId) {
                    pollVideoStatus(videoId, vidEl, vidStatus);
                }

            } catch (err) {
                dbg('fetch exception: ' + err.message);
                addMsg('System', 'Network error: ' + err.message);
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        window.__aiAvatarChatSend = sendMessage;

        document.addEventListener('DOMContentLoaded', function () {
            dbg('DOM ready — binding Send button');
            var btn     = document.getElementById('send-message');
            var inp     = document.getElementById('chat-input');
            var vs      = document.getElementById('video-status');
            var liveBtn = document.getElementById('start-live-session');
            var endBtn  = document.getElementById('end-live-session');
            var fsBtn   = document.getElementById('fullscreen-live-session');

            if (btn) { btn.addEventListener('click', sendMessage); dbg('click bound to #send-message'); }
            else { dbg('WARNING: #send-message not found at DOMContentLoaded'); }
            if (liveBtn) { liveBtn.addEventListener('click', startLiveSession); }
            if (endBtn)  { endBtn.addEventListener('click', endLiveSession); }
            if (fsBtn)   { fsBtn.addEventListener('click', fullscreenLiveSession); }

            if (inp) { inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); sendMessage(); } }); }
            if (vs) vs.textContent = 'Chat ready — select a profile and type a message.';
        });

        // Also bind immediately in case DOM is already ready
        var btnNow = document.getElementById('send-message');
        if (btnNow && !btnNow.dataset.aiBound) {
            btnNow.dataset.aiBound = '1';
            btnNow.addEventListener('click', sendMessage);
            dbg('click bound immediately (DOM already ready)');
        }
    })();
    </script>
</div>
