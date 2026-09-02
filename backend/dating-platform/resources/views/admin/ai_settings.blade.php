@extends('admin.components.layout')
@section('content')
<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Settings were not saved:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-cogs"></i>AI Settings
                            </h3>

                            <div class="card mb-4">
                                <div class="card-header"><strong>Video Avatar Providers</strong></div>
                                <div class="card-body">
                                    <form action="/admin/ai-settings" method="POST" class="form-horizontal">
                                        @csrf

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Live Video Chat Provider</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_avatar_provider" class="form-control" required>
                                                    <option value="tavus_cvi" @if($aiSetting->liveAvatarProvider() === 'tavus_cvi') selected @endif>Tavus (CVI)</option>
                                                    <option value="simli" @if($aiSetting->liveAvatarProvider() === 'simli') selected @endif>Simli</option>
                                                </select>
                                                <small class="form-text text-muted">Powers the real-time "Start Live Video Session" call in the AI chat room. Only the matching Face ID field will be shown on the AI Profiles page.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Live Video Window Style</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_video_layout" class="form-control" required>
                                                    <option value="floating" @if($aiSetting->liveVideoLayout() === 'floating') selected @endif>Floating - draggable window</option>
                                                    <option value="docked" @if($aiSetting->liveVideoLayout() === 'docked') selected @endif>Docked - in chat popup (video in the background, text over the video)</option>
                                                </select>
                                                <small class="form-text text-muted"><b>Floating:</b> video opens in a separate window you can drag anywhere on screen. <b>Docked:</b> the video appears in the chat pop-up background, and the message log can be scrolled over the video.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Live Video Blur</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_video_blur_enabled" id="live-video-blur-enabled" class="form-control">
                                                    <option value="1" @if($aiSetting->liveVideoBlurEnabled()) selected @endif>Blurred</option>
                                                    <option value="0" @if(! $aiSetting->liveVideoBlurEnabled()) selected @endif>Clear</option>
                                                </select>
                                                <small class="form-text text-muted">When Blurred, the AI's live video during a "Start Live Video Session" call is shown out of focus for every user.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group" id="live-video-blur-amount-row" style="@if(! $aiSetting->liveVideoBlurEnabled()) display:none; @endif">
                                            <div class="col col-md-3"><label class="form-control-label">Default Blur Amount</label></div>
                                            <div class="col-12 col-md-9">
                                                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                                                    <div style="flex:1 1 260px; min-width:220px;">
                                                        <input type="range" name="live_video_blur_amount" id="live-video-blur-amount" class="form-control-range"
                                                               min="{{ \App\AISetting::MIN_LIVE_VIDEO_BLUR_AMOUNT }}" max="{{ \App\AISetting::MAX_LIVE_VIDEO_BLUR_AMOUNT }}"
                                                               value="{{ $aiSetting->liveVideoBlurAmount() }}">
                                                        <small class="form-text text-muted">Fallback blur strength (<span class="bold" style="font-size: medium"><span id="live-video-blur-amount-value">{{ $aiSetting->liveVideoBlurAmount() }}</span>%</span>), used only for users whose package has no blur % of its own set. <br>Per-package blur is configured on each package's edit page.</small>
                                                    </div>
                                                    <div style="flex:0 0 auto; text-align:center;">
                                                        <img id="live-video-blur-preview-img"
                                                             src="data:image/svg+xml;utf8,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220"><rect width="220" height="220" fill="#dbe4ee"/><circle cx="110" cy="86" r="48" fill="#8ca3b8"/><ellipse cx="110" cy="202" rx="77" ry="55" fill="#8ca3b8"/></svg>') }}"
                                                             data-max-blur-px="{{ \App\AISetting::maxBlurPx() }}"
                                                             style="width:220px; height:220px; border-radius:8px; border:1px solid #ddd; filter: blur({{ $aiSetting->liveVideoBlurAmountForPercent($aiSetting->liveVideoBlurAmount()) }}px);">
                                                        <small class="form-text text-muted d-block">Preview</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Live Video Audio</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_video_mute_enabled" id="live-video-mute-enabled" class="form-control">
                                                    <option value="1" @if($aiSetting->liveVideoMuteEnabled()) selected @endif>Mute feature active</option>
                                                    <option value="0" @if(! $aiSetting->liveVideoMuteEnabled()) selected @endif>Always let everyone hear the AI</option>
                                                </select>
                                                <small class="form-text text-muted">Master switch. When "Always let everyone hear the AI" is selected, nobody is ever muted, regardless of package. The AI's video keeps moving/talking either way — muting only silences the sound.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group" id="live-video-mute-default-row" style="@if(! $aiSetting->liveVideoMuteEnabled()) display:none; @endif">
                                            <div class="col col-md-3"><label class="form-control-label">Default Audio</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_video_mute_default" class="form-control">
                                                    <option value="1" @if($aiSetting->liveVideoMuteDefault()) selected @endif>Muted</option>
                                                    <option value="0" @if(! $aiSetting->liveVideoMuteDefault()) selected @endif>Sound on</option>
                                                </select>
                                                <small class="form-text text-muted">Fallback used only for users whose package has no audio setting of its own set. Per-package audio is configured on each package's edit page.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group" id="tavus-api-key-row" style="@if($aiSetting->liveAvatarProvider() === 'simli') display:none; @endif">
                                            <div class="col col-md-3"><label class="form-control-label">Tavus API Key</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="password" name="tavus_api_key" class="form-control" placeholder="{{ $aiSetting->tavus_api_key ? '•••••••• (saved — leave blank to keep)' : 'Enter Tavus API key' }}" autocomplete="new-password">
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Simli API Key</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="password" name="simli_api_key" class="form-control" placeholder="{{ $aiSetting->simli_api_key ? '•••••••• (saved — leave blank to keep)' : 'Enter Simli API key' }}" autocomplete="new-password">
                                                <small class="form-text text-muted">Required for the "Live AI Video Call" feature further below (real profiles) no matter what Live Avatar Provider is selected above — that provider choice only affects AI Companions. Also required here if Live Avatar Provider is set to Simli.</small>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Text AI Auto-Reply</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="text_ai_enabled" class="form-control">
                                                    <option value="1" @if($aiSetting->textAiEnabled()) selected @endif>Active</option>
                                                    <option value="0" @if(! $aiSetting->textAiEnabled()) selected @endif>Inactive</option>
                                                </select>
                                                <small class="form-text text-muted">Master switch for the AI auto-reply female profiles send to men in the real chat (<code>ChatBotController</code>). This is separate from the old "Activare Chat Bot" setting under Settings — that one only controls the legacy canned keyword-reply bot, not the AI.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Live AI Video Call</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="live_ai_video_enabled" class="form-control">
                                                    <option value="1" @if($aiSetting->liveAiVideoEnabled()) selected @endif>Active</option>
                                                    <option value="0" @if(! $aiSetting->liveAiVideoEnabled()) selected @endif>Inactive</option>
                                                </select>
                                                <small class="form-text text-muted">Lets men start a live AI video call with an AI-enabled real profile directly from the normal chat popup. <strong>Always uses Simli, regardless of the Live Avatar Provider selected above</strong> — Tavus isn't supported for real profiles since it needs a pre-trained video replica, which isn't possible for stock-photo profiles. Make sure the Simli API Key above is filled in even if Live Avatar Provider is set to Tavus. Off by default: even when Active, a profile only shows the call button once an admin has manually created a Simli Face for her and saved its Face ID on her edit page.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">OpenAI Status</label></div>
                                            <div class="col-12 col-md-9">
                                                @if($aiSetting->openaiConfigured())
                                                    <span class="badge badge-success">Configured</span>
                                                @else
                                                    <span class="badge badge-danger">Not configured</span>
                                                @endif
                                                <small class="form-text text-muted">Needs a working OpenAI API key below to actually generate AI replies — without one, profiles silently fall back to the old canned keyword-reply bot (if that's active), or nothing at all.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">OpenAI API Key</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="password" name="openai_api_key" class="form-control" placeholder="{{ $aiSetting->openai_api_key ? '•••••••• (saved — leave blank to keep)' : 'Enter OpenAI API key' }}" autocomplete="new-password">
                                                <small class="form-text text-muted">Get a key at <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com/api-keys</a>. Make sure billing/spending limits are configured there too.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">OpenAI Model</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="text" name="openai_model" class="form-control" value="{{ $aiSetting->openai_model }}" placeholder="gpt-4o (leave blank to use the server default)">
                                                <small class="form-text text-muted">E.g. <code>gpt-4o</code> or the cheaper <code>gpt-4o-mini</code>. Currently active: <strong>{{ $aiSetting->openaiModel() }}</strong>.</small>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">AI Reply Usage</label></div>
                                            <div class="col-12 col-md-9">
                                                <span class="badge badge-info">Today: {{ number_format($aiUsageToday) }}</span>
                                                <span class="badge badge-info">Last 7 days: {{ number_format($aiUsageThisWeek) }}</span>
                                                <small class="form-text text-muted">Number of real-chat messages actually answered by the AI (not the fallback keyword bot). Each one is a billed OpenAI API call.</small>
                                            </div>
                                        </div>

                                        <hr>
<?php
/*
?>
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Async Video Reply</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="avatar_video_enabled" id="avatar-video-enabled" class="form-control">
                                                    <option value="1" @if($aiSetting->avatarVideoEnabled()) selected @endif>Active</option>
                                                    <option value="0" @if(! $aiSetting->avatarVideoEnabled()) selected @endif>Inactive</option>
                                                </select>
                                                <small class="form-text text-muted">When Inactive, no talking-head video is rendered for regular (non-live) chat replies — only text/audio — and this section's fields are hidden.</small>
                                            </div>
                                        </div>

                                        <div id="avatar-video-settings" style="@if(! $aiSetting->avatarVideoEnabled()) display:none; @endif">
                                            <div class="row form-group">
                                                <div class="col col-md-3"><label class="form-control-label">Async Video Reply Provider</label></div>
                                                <div class="col-12 col-md-9">
                                                    <select name="avatar_video_provider" class="form-control">
                                                        <option value="did" @if($aiSetting->avatarVideoProvider() === 'did') selected @endif>D-ID</option>
                                                        <option value="heygen" @if($aiSetting->avatarVideoProvider() === 'heygen') selected @endif>HeyGen</option>
                                                        <option value="tavus" @if($aiSetting->avatarVideoProvider() === 'tavus') selected @endif>Tavus (batch)</option>
                                                    </select>
                                                    <small class="form-text text-muted">Powers the talking-head video rendered for regular (non-live) chat replies. Only the matching field will be shown on the AI Profiles page.</small>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <div class="col col-md-3"><label class="form-control-label">HeyGen API Key</label></div>
                                                <div class="col-12 col-md-9">
                                                    <input type="password" name="heygen_api_key" class="form-control" placeholder="{{ $aiSetting->heygen_api_key ? '•••••••• (saved — leave blank to keep)' : 'Enter HeyGen API key' }}" autocomplete="new-password">
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <div class="col col-md-3"><label class="form-control-label">D-ID API Key</label></div>
                                                <div class="col-12 col-md-9">
                                                    <input type="password" name="did_api_key" class="form-control" placeholder="{{ $aiSetting->did_api_key ? '•••••••• (saved — leave blank to keep)' : 'Enter D-ID API key' }}" autocomplete="new-password">
                                                </div>
                                            </div>
                                        </div>
<?php
*/
?>
                                        <div class="text-right">
                                            <button type="submit" class="au-btn au-btn-icon au-btn--blue">
                                                <i class="zmdi zmdi-check"></i>Save Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="copyright">
                            <p>Copyright © 2019 Modele De Site. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var providerSelect = document.querySelector('select[name="live_avatar_provider"]');
        var tavusRow = document.getElementById('tavus-api-key-row');
        if (!providerSelect || !tavusRow) { return; }

        // Simli API Key stays visible regardless of this selector - it's also required
        // independently by the "Live AI Video Call" feature for real profiles further below.
        providerSelect.addEventListener('change', function () {
            tavusRow.style.display = providerSelect.value === 'simli' ? 'none' : '';
        });
    })();

    (function () {
        var toggle = document.getElementById('avatar-video-enabled');
        var settings = document.getElementById('avatar-video-settings');
        if (!toggle || !settings) { return; }

        toggle.addEventListener('change', function () {
            settings.style.display = toggle.value === '1' ? '' : 'none';
        });
    })();

    (function () {
        var blurToggle = document.getElementById('live-video-blur-enabled');
        var blurAmountRow = document.getElementById('live-video-blur-amount-row');
        var blurAmount = document.getElementById('live-video-blur-amount');
        var blurAmountValue = document.getElementById('live-video-blur-amount-value');
        var blurPreviewImg = document.getElementById('live-video-blur-preview-img');
        if (!blurToggle || !blurAmountRow || !blurAmount || !blurAmountValue) { return; }

        blurToggle.addEventListener('change', function () {
            blurAmountRow.style.display = blurToggle.value === '1' ? '' : 'none';
        });

        blurAmount.addEventListener('input', function () {
            blurAmountValue.textContent = blurAmount.value;

            if (blurPreviewImg) {
                var maxPx = parseInt(blurPreviewImg.dataset.maxBlurPx, 10) || 40;
                var px = Math.round(maxPx * (parseInt(blurAmount.value, 10) || 0) / 100);
                blurPreviewImg.style.filter = 'blur(' + px + 'px)';
            }
        });
    })();

    (function () {
        var muteToggle = document.getElementById('live-video-mute-enabled');
        var muteDefaultRow = document.getElementById('live-video-mute-default-row');
        if (!muteToggle || !muteDefaultRow) { return; }

        muteToggle.addEventListener('change', function () {
            muteDefaultRow.style.display = muteToggle.value === '1' ? '' : 'none';
        });
    })();
</script>
@endsection
