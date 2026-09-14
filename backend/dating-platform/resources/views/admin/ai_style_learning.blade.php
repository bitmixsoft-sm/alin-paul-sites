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

                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            {{-- Read by the preview tool's fetch() call below - this template has no other
                                 AJAX request, so no meta[name=csrf-token] exists in the admin layout to rely
                                 on; a plain hidden input matches how dating.js reads it elsewhere in the app
                                 ($('input[name="_token"]').val()). --}}
                            <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-graduation-cap"></i> AI Style Learning
                            </h3>
                            <p class="text-muted">
                                Let a female profile's AI auto-reply (<a href="/admin/ai-settings">AI Settings</a> → "Real chat AI auto-reply")
                                learn its conversational style from her own message history, or copy an already-learned style onto other profiles.
                                This only affects tone/approach - never literal sentences from anyone's real conversation.
                            </p>

                            {{-- Ranking: which profile has actually converted clients the best, per the client's
                                 request - "the profile that brought the biggest profit convincing clients to
                                 subscribe". Attribution is a heuristic (last profile a buyer messaged before
                                 paying, in the 7 days before) - see ProfileConversionRankingService for the
                                 full reasoning. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Top-converting profiles (last 7 days of purchases)</strong></div>
                                <div class="card-body p-0">
                                    @if(empty($ranked))
                                        <p class="text-muted p-3 mb-0">No attributable conversions found yet in the lookback window.</p>
                                    @else
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Profile</th>
                                                    <th>Conversions</th>
                                                    <th>Revenue</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ranked as $i => $row)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $row['name'] }}</td>
                                                        <td>{{ $row['conversions'] }}</td>
                                                        <td>{{ number_format($row['revenue'], 2) }}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-primary use-as-source-btn"
                                                                    data-user-id="{{ $row['user_id'] }}" data-user-name="{{ $row['name'] }}">
                                                                Use as source
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>

                            {{-- Apply a distilled style guide (from the field below, pre-filled by "Use as
                                 source" above, or picked manually) onto one or more other profiles. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Apply a learned style to other profiles</strong></div>
                                <div class="card-body">
                                    <form action="{{ route('admin_style_learning_apply') }}" method="POST">
                                        @csrf
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Source profile</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="source_user_id" id="source_user_id" class="form-control" required>
                                                    <option value="">-- Select a profile with a learned style --</option>
                                                    @foreach($profiles as $profile)
                                                        @if(!empty($profile->learning_snapshot['style_guide'] ?? null))
                                                            <option value="{{ $profile->id }}">{{ $profile->name() }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Apply to</label></div>
                                            <div class="col-12 col-md-9" style="max-height: 220px; overflow-y: auto;">
                                                @foreach($profiles as $profile)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="target_user_ids[]" value="{{ $profile->id }}" id="target_{{ $profile->id }}">
                                                        <label class="form-check-label" for="target_{{ $profile->id }}">{{ $profile->name() }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Apply style</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Every female profile - learn from her own history, or clear whatever's set. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>All profiles</strong></div>
                                <div class="card-body p-0">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Profile</th>
                                                <th>Style guide</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($profiles as $profile)
                                                @php $hasStyle = !empty($profile->learning_snapshot['style_guide'] ?? null); @endphp
                                                <tr>
                                                    <td>{{ $profile->name() }}</td>
                                                    <td>
                                                        @if($hasStyle)
                                                            <span class="badge badge-success">Set</span>
                                                            @if(!empty($profile->learning_snapshot['style_guide_source_user_id']))
                                                                <small class="text-muted">(from #{{ $profile->learning_snapshot['style_guide_source_user_id'] }})</small>
                                                            @endif
                                                        @else
                                                            <span class="badge badge-secondary">None</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        <form action="{{ route('admin_style_learning_distill', $profile->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Learn a style from {{ $profile->name() }}\'s own conversation history? This calls OpenAI once.');">
                                                                Learn from her own history
                                                            </button>
                                                        </form>
                                                        @if($hasStyle)
                                                            <form action="{{ route('admin_style_learning_clear', $profile->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove the style guide from {{ $profile->name() }}?');">
                                                                    Clear
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Testing level 1 from the conversation with the user (2026-09-14): compare the
                                 SAME test message's reply with and without the profile's saved style guide,
                                 side by side - confirms it's actually wired up without needing a real chat. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Preview / test a profile's reply</strong></div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3"><label class="form-control-label">Profile</label></div>
                                        <div class="col-12 col-md-9">
                                            <select id="preview_user_id" class="form-control">
                                                <option value="">-- Select a profile --</option>
                                                @foreach($profiles as $profile)
                                                    <option value="{{ $profile->id }}">{{ $profile->name() }}{{ !empty($profile->learning_snapshot['style_guide'] ?? null) ? ' (has style)' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-3"><label class="form-control-label">Test message</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="preview_message" class="form-control" placeholder="e.g. Hey, how are you today?">
                                        </div>
                                    </div>
                                    <button type="button" id="preview_btn" class="btn btn-primary">Compare replies</button>
                                    <div id="preview_result" class="mt-3" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Without style guide:</strong>
                                                <p id="preview_without" class="border rounded p-2 mt-1"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>With style guide:</strong>
                                                <p id="preview_with" class="border rounded p-2 mt-1"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="preview_error" class="text-danger mt-2" style="display:none;"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.use-as-source-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var select = document.getElementById('source_user_id');
        select.value = btn.dataset.userId;
        if (select.value !== btn.dataset.userId) {
            // The ranked profile has no distilled style guide yet (not in the dropdown's
            // options), so nothing to apply - direct the admin to learn one first instead of
            // silently doing nothing.
            alert(btn.dataset.userName + ' does not have a distilled style guide yet - use "Learn from her own history" on her row below first.');
            return;
        }
        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

document.getElementById('preview_btn').addEventListener('click', function () {
    var userId = document.getElementById('preview_user_id').value;
    var message = document.getElementById('preview_message').value.trim();
    var errorEl = document.getElementById('preview_error');
    var resultEl = document.getElementById('preview_result');
    errorEl.style.display = 'none';
    resultEl.style.display = 'none';

    if (!userId || !message) {
        errorEl.textContent = 'Pick a profile and type a test message first.';
        errorEl.style.display = 'block';
        return;
    }

    fetch('{{ route('admin_style_learning_preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.getElementById('csrf_token').value,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, message: message }),
    })
        .then(function (resp) { return resp.json().then(function (data) { return { ok: resp.ok, data: data }; }); })
        .then(function (result) {
            if (!result.ok) {
                errorEl.textContent = result.data.error || result.data.message || 'Request failed.';
                errorEl.style.display = 'block';
                return;
            }
            document.getElementById('preview_without').textContent = result.data.without_style;
            document.getElementById('preview_with').textContent = result.data.has_style_guide
                ? result.data.with_style
                : '(this profile has no style guide set - identical to the left)';
            resultEl.style.display = 'block';
        })
        .catch(function (err) {
            errorEl.textContent = 'Network error: ' + err.message;
            errorEl.style.display = 'block';
        });
});
</script>
@endsection
