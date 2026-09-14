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
                            {{-- .user-data doesn't add its own horizontal padding (the tables/cards below get
                                 away with it because Bootstrap's .card/.table already carry their own) - a
                                 plain <p> ran edge-to-edge against the white card, reported live. --}}
                            <p class="text-muted" style="padding: 0 15px;">
                                Permite ca raspunsurile automate AI ale unui profil feminin (<a href="/admin/ai-settings">AI Settings</a> → "Real chat AI auto-reply")
                                sa invete din propriul istoric de mesaje, in unul din doua moduri, sau sa copiaza ce a invatat deja un alt profil:
                                <strong>Stil (ton)</strong> - AI-ul se inspira din abordare/ton, dar formuleaza mereu liber, propozitii noi;
                                <strong>Fraze exacte</strong> - AI-ul refoloseste, aproape cuvant cu cuvant, mesaje reale care au functionat bine inainte.
                            </p>

                            {{-- Ranking: which profile has actually converted clients the best, per the client's
                                 request - "the profile that brought the biggest profit convincing clients to
                                 subscribe". Attribution is a heuristic (last profile a buyer messaged before
                                 paying, in the 7 days before) - see ProfileConversionRankingService for the
                                 full reasoning. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Profilurile cu cele mai multe conversii (ultimele 7 zile)</strong></div>
                                <div class="card-body p-0">
                                    @if(empty($ranked))
                                        <p class="text-muted p-3 mb-0">Nu au fost gasite conversii atribuibile in perioada analizata.</p>
                                    @else
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Profil</th>
                                                    <th>Conversii</th>
                                                    <th>Venit</th>
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
                                                            {{-- Bootstrap's btn-outline-* renders with invisible (white-on-white)
                                                                 text in this admin theme until :hover - reported live - so this
                                                                 (and the other action buttons on this page) use the theme's own
                                                                 au-btn classes instead, which are already proven to render
                                                                 correctly everywhere else in the admin. --}}
                                                            <button type="button" class="au-btn au-btn--blue use-as-source-btn"
                                                                    style="padding:0 14px; font-size:12px;"
                                                                    data-user-id="{{ $row['user_id'] }}" data-user-name="{{ $row['name'] }}">
                                                                Foloseste ca sursa
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>

                            {{-- Apply whatever the source profile currently has learned - one mode at a time,
                                 whichever her 'mode' key says is active (see AdminStyleLearningController::apply()) -
                                 onto one or more other profiles. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Aplica ce a invatat un profil altor profiluri</strong></div>
                                <div class="card-body">
                                    <form action="{{ route('admin_style_learning_apply') }}" method="POST">
                                        @csrf
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Profil sursa</label></div>
                                            <div class="col-12 col-md-9">
                                                <select name="source_user_id" id="source_user_id" class="form-control" required>
                                                    <option value="">-- Selecteaza un profil care a invatat ceva --</option>
                                                    @foreach($profiles as $profile)
                                                        @php
                                                            $snapshot = $profile->learning_snapshot ?? [];
                                                            $mode = $snapshot['mode'] ?? null;
                                                            $canBeSource = ($mode === 'style' && !empty($snapshot['style_guide']))
                                                                || ($mode === 'phrases' && !empty($snapshot['phrase_examples']));
                                                        @endphp
                                                        @if($canBeSource)
                                                            <option value="{{ $profile->id }}">{{ $profile->name() }} ({{ $mode === 'phrases' ? 'fraze' : 'stil' }})</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                {{-- This dropdown is EMPTY until at least one profile has learned something -
                                                     without this note, an admin seeing nothing here (before ever using
                                                     "Invata stil"/"Invata fraze" in the table below) has no way to know
                                                     why, or what to do about it. Reported live as a real point of
                                                     confusion. --}}
                                                <small class="form-text text-muted">
                                                    Aceasta lista este goala pana cand cel putin un profil a invatat ceva.
                                                    Mergi mai jos, la tabelul "Toate profilurile", si apasa
                                                    <strong>"Invata stil"</strong> sau <strong>"Invata fraze"</strong> pentru profilul dorit -
                                                    dupa aceea va aparea aici ca optiune de sursa.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Aplica la</label></div>
                                            <div class="col-12 col-md-9" style="max-height: 220px; overflow-y: auto;">
                                                @foreach($profiles as $profile)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="target_user_ids[]" value="{{ $profile->id }}" id="target_{{ $profile->id }}">
                                                        <label class="form-check-label" for="target_{{ $profile->id }}">{{ $profile->name() }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <button type="submit" class="au-btn au-btn--blue">Aplica</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Every female profile - learn from her own history (either mode), or clear
                                 whatever's set. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Toate profilurile</strong></div>
                                <div class="card-body p-0">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Profil</th>
                                                <th>Ce a invatat</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($profiles as $profile)
                                                @php
                                                    $snapshot = $profile->learning_snapshot ?? [];
                                                    $mode = $snapshot['mode'] ?? null;
                                                    $hasStyle = $mode === 'style' && !empty($snapshot['style_guide']);
                                                    $hasPhrases = $mode === 'phrases' && !empty($snapshot['phrase_examples']);
                                                @endphp
                                                <tr>
                                                    <td>{{ $profile->name() }}</td>
                                                    <td>
                                                        @if($hasStyle)
                                                            <span class="badge badge-success">Stil (ton)</span>
                                                        @elseif($hasPhrases)
                                                            <span class="badge badge-success">Fraze exacte ({{ count($snapshot['phrase_examples']) }})</span>
                                                        @else
                                                            <span class="badge badge-secondary">Nesetat</span>
                                                        @endif
                                                        @if(($hasStyle || $hasPhrases) && !empty($snapshot['source_user_id']) && $snapshot['source_user_id'] != $profile->id)
                                                            <small class="text-muted">(preluat de la #{{ $snapshot['source_user_id'] }})</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        <form action="{{ route('admin_style_learning_distill', $profile->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="mode" value="style">
                                                            <button type="submit" class="au-btn au-btn--blue" style="padding:0 14px; font-size:12px;" onclick="return confirm('Inveti stilul (tonul) din istoricul propriu de conversatii al lui {{ $profile->name() }}? Aceasta apeleaza OpenAI o data.');">
                                                                Invata stil
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin_style_learning_distill', $profile->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="mode" value="phrases">
                                                            <button type="submit" class="au-btn au-btn--green" style="padding:0 14px; font-size:12px;" onclick="return confirm('Extragi fraze exacte din istoricul propriu de conversatii al lui {{ $profile->name() }}? Aceasta apeleaza OpenAI o data.');">
                                                                Invata fraze
                                                            </button>
                                                        </form>
                                                        @if($hasStyle || $hasPhrases)
                                                            <form action="{{ route('admin_style_learning_clear', $profile->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="au-btn" style="padding:0 14px; font-size:12px; background:#dc3545;" onclick="return confirm('Elimini ce a invatat {{ $profile->name() }}?');">
                                                                    Sterge
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
                                 SAME test message's reply with and without the profile's saved learning,
                                 side by side - confirms it's actually wired up without needing a real chat. --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Previzualizare / testeaza raspunsul unui profil</strong></div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3"><label class="form-control-label">Profil</label></div>
                                        <div class="col-12 col-md-9">
                                            <select id="preview_user_id" class="form-control">
                                                <option value="">-- Selecteaza un profil --</option>
                                                @foreach($profiles as $profile)
                                                    @php
                                                        $snapshot = $profile->learning_snapshot ?? [];
                                                        $mode = $snapshot['mode'] ?? null;
                                                        $label = $mode === 'style' && !empty($snapshot['style_guide']) ? ' (stil)'
                                                            : ($mode === 'phrases' && !empty($snapshot['phrase_examples']) ? ' (fraze)' : '');
                                                    @endphp
                                                    <option value="{{ $profile->id }}">{{ $profile->name() }}{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-3"><label class="form-control-label">Mesaj de test</label></div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="preview_message" class="form-control" placeholder="ex: Salut, ce mai faci azi?">
                                        </div>
                                    </div>
                                    <button type="button" id="preview_btn" class="au-btn au-btn--blue">Compara raspunsurile</button>
                                    <div id="preview_result" class="mt-3" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Fara ce a invatat:</strong>
                                                <p id="preview_without" class="border rounded p-2 mt-1"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Cu ce a invatat:</strong>
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
            // The ranked profile hasn't learned anything yet (not in the dropdown's options),
            // so nothing to apply - direct the admin to learn one first instead of silently
            // doing nothing.
            alert(btn.dataset.userName + ' nu a invatat inca nimic - foloseste mai intai "Invata stil" sau "Invata fraze" pe randul ei de mai jos.');
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
        errorEl.textContent = 'Selecteaza un profil si scrie un mesaj de test.';
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
                errorEl.textContent = result.data.error || result.data.message || 'Cererea a esuat.';
                errorEl.style.display = 'block';
                return;
            }
            document.getElementById('preview_without').textContent = result.data.without_style;
            document.getElementById('preview_with').textContent = result.data.has_style_guide
                ? result.data.with_style
                : '(acest profil nu a invatat nimic - identic cu cel din stanga)';
            resultEl.style.display = 'block';
        })
        .catch(function (err) {
            errorEl.textContent = 'Eroare de retea: ' + err.message;
            errorEl.style.display = 'block';
        });
});
</script>
@endsection
