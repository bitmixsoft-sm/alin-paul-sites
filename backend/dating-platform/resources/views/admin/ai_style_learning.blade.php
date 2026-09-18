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
                                {{-- Plain vanilla-JS overlay, not a Bootstrap .modal - this admin theme's CSS
                                     doesn't fully support Bootstrap's modal/list styling (reported live: no
                                     scrolling, list numbers rendered as tiny superscripts, the footer button
                                     stretched to a huge blank rectangle) - a hand-styled overlay sidesteps all
                                     of that instead of fighting the theme's incomplete Bootstrap CSS. --}}
                                <button type="button" class="au-btn au-btn--blue" style="padding:0 14px; font-size:12px; vertical-align:middle;" onclick="document.getElementById('style-learning-help-overlay').style.display='flex';">
                                    <i class="fas fa-question-circle"></i> Ajutor
                                </button>
                            </h3>
                            {{-- .user-data doesn't add its own horizontal padding (the tables/cards below get
                                 away with it because Bootstrap's .card/.table already carry their own) - a
                                 plain <p> ran edge-to-edge against the white card, reported live. --}}
                            <p class="text-muted" style="padding: 0 15px;">
                                Permite ca raspunsurile automate AI ale unui profil feminin (<a href="/admin/ai-settings">AI Settings</a> → "Real chat AI auto-reply")
                                sa invete din propriul istoric de mesaje, in unul din doua moduri, sau sa copiaza ce a invatat deja un alt profil:
                                <strong>Stil (ton)</strong> - AI-ul se inspira din abordare/ton, dar formuleaza mereu liber, propozitii noi;
                                <strong>Fraze exacte</strong> - AI-ul refoloseste, aproape cuvant cu cuvant, mesaje reale care au functionat bine inainte.
                                Apasa <strong>"Ajutor"</strong> de mai sus pentru o explicatie completa, pas cu pas.
                            </p>

                            {{-- Full walkthrough for the admin/client - same text sent to the client explaining
                                 this feature, kept here so it's always available in-page instead of only in a
                                 chat/email that gets lost. Hand-styled overlay (see the "Ajutor" button above for
                                 why, not a Bootstrap .modal) - display:none by default, toggled via plain
                                 element.style.display, closed by the X, the "Am inteles" button, or clicking the
                                 dark backdrop itself (but not clicks inside the white card, so selecting/copying
                                 the text doesn't accidentally close it). --}}
                            {{-- top/left/right/bottom set explicitly, not the "inset: 0" shorthand - reported
                                 live: with inset, this rendered at its normal in-page position (right below the
                                 paragraph above, under the fixed admin topbar) instead of covering the full
                                 viewport from the very top - some effective rendering path here doesn't apply
                                 inset the same way as the four longhands. --}}
                            {{-- Reported live (2026-09-18): headings rendered SMALLER than the body text below
                                 them, numbered lists rendered as barely-visible tiny superscripts (this admin
                                 theme's native <ol>/list-style:decimal support is broken - already known, see
                                 the note above on why this overlay exists at all instead of a Bootstrap
                                 .modal). Fixed with an explicit, self-contained style block (sized relative to
                                 nothing the theme controls) plus custom numbered/bulleted lists built from CSS
                                 counters and ::before pseudo-elements instead of relying on the theme's <ol>/
                                 <ul> rendering at all. --}}
                            <style>
                                #style-learning-help-overlay .help-content { font-size: 16px; line-height: 1.7; color: #2a2a2a; }
                                #style-learning-help-overlay .help-content h4 { font-size: 23px; font-weight: 700; margin: 0 0 18px; color: #1a1a1a; }
                                #style-learning-help-overlay .help-content h6 { font-size: 18px; font-weight: 700; margin: 26px 0 10px; color: #1a1a1a; }
                                #style-learning-help-overlay .help-content p { font-size: 16px; margin: 0 0 14px; }
                                #style-learning-help-overlay .help-content strong { font-weight: 700; }
                                #style-learning-help-overlay .help-content .help-note { font-size: 14px; color: #777; }
                                #style-learning-help-overlay .help-steps { list-style: none; counter-reset: help-step; padding: 0; margin: 0 0 16px; }
                                #style-learning-help-overlay .help-steps > li { position: relative; counter-increment: help-step; padding: 0 0 0 40px; margin-bottom: 14px; min-height: 26px; }
                                #style-learning-help-overlay .help-steps > li::before {
                                    content: counter(help-step);
                                    position: absolute; left: 0; top: 0;
                                    width: 26px; height: 26px; border-radius: 50%;
                                    background: #4a6cf7; color: #fff;
                                    font-size: 14px; font-weight: 700;
                                    display: flex; align-items: center; justify-content: center;
                                }
                                #style-learning-help-overlay .help-bullets { list-style: none; padding: 0; margin: 0 0 14px; }
                                #style-learning-help-overlay .help-bullets > li { position: relative; padding: 0 0 0 22px; margin-bottom: 12px; }
                                #style-learning-help-overlay .help-bullets > li::before { content: "\2022"; position: absolute; left: 2px; top: 0; font-size: 20px; line-height: 1.2; color: #4a6cf7; }
                            </style>
                            <div id="style-learning-help-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.6); z-index:999999; align-items:flex-start; justify-content:center; padding:40px 15px; overflow-y:auto;" onclick="if(event.target===this){this.style.display='none';}">
                                <div class="help-content" style="background:#fff; border-radius:6px; max-width:800px; width:100%; padding:30px 34px; position:relative;">
                                    <button type="button" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:24px; line-height:1; cursor:pointer; color: #fff;background: #dc3545; border-radius: 10%;padding: 5px 10px;" onclick="document.getElementById('style-learning-help-overlay').style.display='none';">&times;</button>
                                    <h4>Cum functioneaza "AI Style Learning"</h4>
                                    <div>
                                            <p>
                                                Raspunsurile automate AI ale profilurilor feminine (cand un barbat scrie unui profil si AI-ul
                                                raspunde in numele ei) foloseau pana acum doar un text "Persona AI" scris manual de admin.
                                                Aceasta functie permite ca AI-ul sa <strong>invete</strong> din conversatiile anterioare ale unui
                                                profil (sau ale celui mai performant profil), in <strong>doua moduri diferite</strong>, pentru a
                                                raspunde intr-un stil similar, cu o abordare la fel de convingatoare.
                                            </p>

                                            <h6>Cele doua moduri de invatare</h6>
                                            <ul class="help-bullets">
                                                <li>
                                                    <strong>Stil (ton)</strong> - AI-ul invata doar tonul si abordarea generala (cat de calduros
                                                    vorbeste, cum flirteaza, cum directioneaza conversatia spre abonament) - dar formuleaza mereu
                                                    propozitii noi, libere. Nu repeta niciodata cuvant cu cuvant ceva dintr-o conversatie reala.
                                                </li>
                                                <li>
                                                    <strong>Fraze exacte</strong> - AI-ul primeste o selectie de mesaje reale, exacte, care au
                                                    functionat foarte bine in trecut, si le refoloseste aproape cuvant cu cuvant atunci cand se
                                                    potrivesc cu momentul conversatiei - in loc sa formuleze ceva nou, foloseste direct fraze deja
                                                    dovedite ca sunt eficiente.
                                                </li>
                                            </ul>
                                            <p>Adminul poate alege liber, pentru fiecare profil in parte, care dintre cele doua moduri sa fie activ.</p>

                                            <h6>Cat de multa conversatie citeste sistemul</h6>
                                            <p>
                                                Nu se trimite catre AI intreg istoricul unui profil (poate fi de ani de zile) - ar fi prea mult
                                                pentru o singura cerere. Se ia mereu <strong>partea cea mai recenta</strong> care incape: cel mult
                                                ultimele <strong>4000 de mesaje</strong> (ambele parti ale conversatiilor, nu doar mesajele
                                                profilului), taiat apoi la cel mult <strong>40.000 de caractere</strong> - oricare limita se atinge
                                                prima. Aceasta se aplica identic si la invatarea din propriile conversatii, si la invatarea de pe
                                                un site extern.
                                            </p>
                                            <p class="help-note">
                                                Aceste doua limite (4000 mesaje / 40.000 caractere) sunt un setaj tehnic in codul aplicatiei
                                                (fisierul <code>ProfileTranscriptBuilder.php</code>), nu ceva reglabil din aceasta pagina - daca la
                                                un moment dat vrei sa inveti din mai multa sau mai putina conversatie, anunta dezvoltatorul sa
                                                modifice aceasta valoare.
                                            </p>

                                            <h6>Cei 4 pasi pe care ii poate face adminul</h6>
                                            <ol class="help-steps">
                                                <li>
                                                    <strong>Tabelul "Profilurile cu cele mai multe conversii"</strong> - arata automat care profil
                                                    feminin a adus cele mai multe plati/abonamente in ultimele 7 zile. Ajuta la decizia al cui
                                                    stil/frazele cui merita "invatate" de sistem.
                                                </li>
                                                <li>
                                                    <strong>Tabelul "Toate profilurile"</strong> - aici exista butoane pentru fiecare profil:
                                                    <strong>"Invata stil"</strong> (genereaza un ghid de ton/abordare din conversatiile proprii),
                                                    <strong>"Invata fraze"</strong> (extrage cele mai eficiente mesaje reale, exacte, din
                                                    conversatiile proprii)@if(!empty($externalSites)), si <strong>"Site extern"</strong> (invata
                                                    dintr-un profil de pe un alt site - {{ implode(', ', $externalSites) }} - introducand
                                                    username-ul profilului de acolo)@endif. Se poate apasa oricare dintre optiuni (dar doar
                                                    ultima folosita ramane activa pentru profilul respectiv). Odata invatat ceva, apare si un
                                                    buton <strong>"Vezi ce a invatat"</strong> - arata exact textul ghidului de stil sau lista
                                                    completa de fraze salvate, ca sa se stie mereu ce foloseste efectiv AI-ul, nu doar ca "ceva"
                                                    a fost invatat.
                                                </li>
                                                <li>
                                                    <strong>"Aplica ce a invatat un profil altor profiluri"</strong> - se selecteaza profilul sursa
                                                    (impreuna cu modul lui, stil sau fraze), se bifeaza profilurile tinta, si se apasa "Aplica" -
                                                    astfel ce a invatat un profil poate fi transferat catre alte profiluri.
                                                </li>
                                                <li>
                                                    <strong>"Previzualizare / testeaza raspunsul unui profil"</strong> - se selecteaza un profil,
                                                    se scrie un mesaj de test, si sistemul arata alaturi ce ar raspunde cu ce a invatat si fara -
                                                    astfel se vede imediat daca se schimba ceva si daca modul ales da rezultatul dorit.
                                                </li>
                                            </ol>

                                            @if(!empty($externalSites))
                                                <h6>Invatarea de pe un alt site ("Site extern")</h6>
                                                <p>
                                                    Site-uri precum {{ implode(', ', $externalSites) }} folosesc exact acelasi sistem ca si
                                                    trovamequi.me, doar pe alt domeniu - de aceea un profil de-al nostru poate invata direct din
                                                    conversatiile unui profil de-acolo, nu doar din propriile conversatii de pe acest site.
                                                </p>
                                                <ol class="help-steps">
                                                    <li>
                                                        In tabelul "Toate profilurile", la profilul care ar trebui sa invete ceva, apasa butonul
                                                        mov <strong>"Site extern"</strong> - se deschide un rand nou, chiar sub profilul respectiv.
                                                    </li>
                                                    <li>
                                                        Alege din prima lista site-ul de pe care vrei sa invete (ex: {{ implode(', ', $externalSites) }}).
                                                    </li>
                                                    <li>
                                                        Da click in campul "cauta username..." - daca nu stii niciun nume, apar direct primele
                                                        <strong>15 profile</strong> de-acolo (nu lista completa - doar cateva, de unde sa incepi),
                                                        de unde poti sa navighezi; sau incepe sa scrii numele/username-ul dorit pentru a filtra
                                                        lista la profilul cautat (tot cel mult 15 rezultate deodata). <strong>Click pe rezultatul
                                                        dorit</strong> pentru a-l selecta exact (nu trebuie stiut/scris manual un ID, doar numele).
                                                    </li>
                                                    <li>
                                                        Alege modul (<strong>Stil</strong> sau <strong>Fraze exacte</strong>, la fel ca la invatarea
                                                        din propriile conversatii) si apasa <strong>"Invata de acolo"</strong>.
                                                    </li>
                                                    <li>
                                                        In coloana "Ce a invatat" va aparea o notita <em>"(preluat de pe {{ implode(', ', $externalSites) }} / username)"</em>,
                                                        ca sa se stie mereu de unde vine ce a invatat profilul respectiv.
                                                    </li>
                                                </ol>
                                                <p class="help-note">
                                                    Daca in viitor mai apare un alt site nou din care vrei sa se invete (nu doar cele listate mai
                                                    sus), acesta trebuie configurat tehnic mai intai (acces la baza lui de date) - anunta
                                                    dezvoltatorul cand apare aceasta nevoie.
                                                </p>
                                            @endif

                                            <h6>Flux de lucru recomandat</h6>
                                            <ol class="help-steps">
                                                <li>Verifica clasamentul → alege profilul cu cele mai bune rezultate.</li>
                                                <li>Incearca mai intai "Invata stil" pentru acel profil, testeaza in Previzualizare.</li>
                                                <li>Daca doresti un rezultat mai apropiat de conversatiile reale, incearca si "Invata fraze" pe acelasi profil, si compara din nou in Previzualizare.</li>
                                                <li>Alege modul care suna mai bine, apoi aplica-l si la alte profiluri din sectiunea "Aplica ce a invatat un profil altor profiluri".</li>
                                            </ol>
                                    </div>
                                    <div style="text-align:right; margin-top:20px;">
                                        <button type="button" class="au-btn au-btn--blue" style="padding:0 20px; font-size:13px;" onclick="document.getElementById('style-learning-help-overlay').style.display='none';">Am inteles</button>
                                    </div>
                                </div>
                            </div>

                            {{-- "Vezi ce a invatat" (client's follow-up, 2026-09-18: "honnan tudja az admin mit
                                 szedett be") - shows the actual saved style guide text or phrase list for one
                                 profile, copied in from that profile's hidden <template> (see the table below) by
                                 showLearningDetail(). Same hand-styled-overlay pattern as the help overlay above,
                                 for the same reason (this admin theme's Bootstrap .modal support is broken). --}}
                            <div id="learning-detail-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.6); z-index:999999; align-items:flex-start; justify-content:center; padding:40px 15px; overflow-y:auto;" onclick="if(event.target===this){this.style.display='none';}">
                                <div class="help-content" style="background:#fff; border-radius:6px; max-width:700px; width:100%; padding:30px 34px; position:relative;">
                                    <button type="button" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:24px; line-height:1; cursor:pointer; color: #fff;background: #dc3545; border-radius: 10%;padding: 5px 10px;" onclick="document.getElementById('learning-detail-overlay').style.display='none';">&times;</button>
                                    <h4>Ce a invatat acest profil</h4>
                                    <div id="learning-detail-body"></div>
                                    <div style="text-align:right; margin-top:20px;">
                                        <button type="button" class="au-btn" style="padding:0 20px; font-size:13px; background:#6c757d;" onclick="document.getElementById('learning-detail-overlay').style.display='none';">Inchide</button>
                                        {{-- Answers the OTHER half of the client's question ("mukodik e valjoban") -
                                             points straight at the Previzualizare tool further down the page instead
                                             of duplicating a live test inside this overlay too. --}}
                                        <button type="button" class="au-btn au-btn--blue test-in-preview-btn" style="padding:0 20px; font-size:13px;" onclick="document.getElementById('learning-detail-overlay').style.display='none'; document.getElementById('preview_user_id').scrollIntoView({behavior:'smooth', block:'center'});">Testeaza in Previzualizare</button>
                                    </div>
                                </div>
                            </div>

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
                                                @if($hasStyle || $hasPhrases)
                                                    {{-- Not rendered/visible on its own - <template> content is inert
                                                         until JS copies it into #learning-detail-body (showLearningDetail()
                                                         below). One per profile, built server-side from the already-loaded
                                                         $profiles collection, so opening this never needs its own AJAX call. --}}
                                                    <template id="learning-detail-{{ $profile->id }}">
                                                        <p><strong>Profil:</strong> {{ $profile->name() }}</p>
                                                        <p><strong>Mod:</strong> {{ $hasPhrases ? 'Fraze exacte' : 'Stil (ton)' }}</p>
                                                        <p><strong>Sursa:</strong>
                                                            @if(!empty($snapshot['source_external_label']))
                                                                {{ $snapshot['source_external_label'] }} (site extern)
                                                            @elseif(!empty($snapshot['source_user_id']) && $snapshot['source_user_id'] != $profile->id)
                                                                profilul #{{ $snapshot['source_user_id'] }}
                                                            @else
                                                                propriile conversatii
                                                            @endif
                                                        </p>
                                                        @if(!empty($snapshot['updated_at']))
                                                            <p><strong>Actualizat:</strong> {{ \Illuminate\Support\Carbon::parse($snapshot['updated_at'])->format('d.m.Y H:i') }}</p>
                                                        @endif
                                                        @if($hasStyle)
                                                            <p><strong>Ghidul de stil salvat:</strong></p>
                                                            <div style="white-space:pre-wrap; background:#f5f5f5; border-radius:4px; padding:12px; font-size:14px;">{{ $snapshot['style_guide'] }}</div>
                                                        @else
                                                            <p><strong>Frazele salvate ({{ count($snapshot['phrase_examples']) }}):</strong></p>
                                                            <ol style="padding-left:20px; font-size:14px;">
                                                                @foreach($snapshot['phrase_examples'] as $phrase)
                                                                    <li style="margin-bottom:6px;">{{ $phrase }}</li>
                                                                @endforeach
                                                            </ol>
                                                        @endif
                                                    </template>
                                                @endif
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
                                                        @if(($hasStyle || $hasPhrases) && !empty($snapshot['source_external_label']))
                                                            <small class="text-muted">(preluat de pe {{ $snapshot['source_external_label'] }})</small>
                                                        @elseif(($hasStyle || $hasPhrases) && !empty($snapshot['source_user_id']) && $snapshot['source_user_id'] != $profile->id)
                                                            <small class="text-muted">(preluat de la #{{ $snapshot['source_user_id'] }})</small>
                                                        @endif
                                                        @if($hasStyle || $hasPhrases)
                                                            {{-- Client's follow-up (2026-09-18): "honnan tudja az admin mit
                                                                 szedett be" - up to now the badge/source note above was the
                                                                 only feedback; this shows the ACTUAL saved content (the style
                                                                 guide text, or the full phrase list), not just that something
                                                                 non-empty exists. Content is pre-rendered into a hidden
                                                                 <template> below (no extra AJAX call needed - $profiles
                                                                 already carries learning_snapshot), and copied into the shared
                                                                 overlay on click - see showLearningDetail() below. --}}
                                                            <br>
                                                            {{-- Same "no color modifier class = invisible until :hover" theme bug
                                                                 as .btn-outline-* elsewhere on this page (see the Sterge/Site
                                                                 extern buttons) - an explicit background fixes it here too. --}}
                                                            <button type="button" class="au-btn" style="padding:0 10px; font-size:11px; margin-top:4px; background:#17a2b8; color:#fff;" onclick="showLearningDetail({{ $profile->id }})">
                                                                Vezi ce a invatat
                                                            </button>
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
                                                        @if(!empty($externalSites))
                                                            {{-- "Site extern" here is a small popover-style row shown/hidden via JS
                                                                 (below), not its own modal - keeps each row self-contained instead
                                                                 of one shared form at the bottom of the page that would need to
                                                                 track which profile is the current target. --}}
                                                            <button type="button" class="au-btn" style="padding:0 14px; font-size:12px; background:#6f42c1;" onclick="toggleExternalRow({{ $profile->id }})">
                                                                Site extern
                                                            </button>
                                                        @endif
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
                                                @if(!empty($externalSites))
                                                    {{-- Hidden by default (see toggleExternalRow() below) - a whole extra
                                                         table row per profile so the site/username/mode inputs have real
                                                         room, instead of squeezing them into the already-tight action
                                                         cell above. --}}
                                                    <tr id="external-row-{{ $profile->id }}" style="display:none; background:#f8f7fc;">
                                                        <td colspan="3" style="padding:18px 22px;">
                                                            <form action="{{ route('admin_style_learning_distill_external', $profile->id) }}" method="POST" onsubmit="return confirm('Inveti {{ $profile->name() }} din istoricul acelui profil de pe site-ul extern selectat? Aceasta apeleaza OpenAI o data.');">
                                                                @csrf
                                                                {{-- Client's follow-up (2026-09-18): the fields looked mismatched in
                                                                     size with no indication of what each one was for - fixed labels
                                                                     above every field plus consistent explicit widths, instead of
                                                                     leaving Bootstrap's <select>/<input> default sizing to fend for
                                                                     itself. --}}
                                                                <div style="font-size:11px; font-weight:700; color:#6f42c1; text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                                                                    Invata din conversatiile unui profil de pe un alt site
                                                                </div>
                                                                <div class="form-row align-items-end" style="row-gap:12px;">
                                                                    <div class="col-auto" style="width:160px;">
                                                                        <label style="font-size:11px; color:#777; margin-bottom:4px; display:block;">Site</label>
                                                                        <select name="connection" class="form-control form-control-sm" style="height: auto; width:100%;" required>
                                                                            @foreach($externalSites as $connectionName => $label)
                                                                                <option value="{{ $connectionName }}">{{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-auto" style="position:relative; width:260px;">
                                                                        <label style="font-size:11px; color:#777; margin-bottom:4px; display:block;">Profil (username) de pe acel site</label>
                                                                        {{-- Live search instead of a blind text field - usernames aren't
                                                                             listed anywhere for an external site the way local profiles
                                                                             get a proper <select> below, and a typo here would silently
                                                                             match nothing. See searchExternalUsers() below. --}}
                                                                        <input type="text" name="username" class="form-control form-control-sm external-username-input" autocomplete="off" placeholder="cauta username..." style="width:100%;" required>
                                                                        <div class="external-username-results" style="display:none; position:absolute; top:100%; left:0; z-index:20; background:#fff; border:1px solid #ccc; border-radius:4px; max-height:180px; overflow-y:auto; width:100%; box-shadow:0 2px 6px rgba(0,0,0,.15);"></div>
                                                                    </div>
                                                                    <div class="col-auto" style="width:150px;">
                                                                        <label style="font-size:11px; color:#777; margin-bottom:4px; display:block;">Mod de invatare</label>
                                                                        <select name="mode" class="form-control form-control-sm" style="height: auto; width:100%;" required>
                                                                            <option value="style">Stil (ton)</option>
                                                                            <option value="phrases">Fraze exacte</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-auto">
                                                                        <button type="submit" class="au-btn au-btn--blue" style="padding:0 16px; line-height:31px; font-size:12px;">Invata de acolo</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif
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
// Moves the help overlay to be a direct child of <body> instead of staying nested inside
// .page-container/.main-content/etc - reported live: even at z-index: 999999, the fixed admin
// topbar (.header-desktop, z-index: 3) still painted on top of it. That only happens if some
// ancestor between this element and <body> creates its own stacking context ranked below the
// topbar's - in which case no z-index on a descendant, however high, can ever escape it. Since
// nothing in this admin theme's CSS looked deliberately built for that (no ancestor has its own
// z-index/transform/opacity set), relocating the element in the DOM sidesteps the mystery
// entirely instead of chasing exactly which ancestor is responsible.
var helpOverlay = document.getElementById('style-learning-help-overlay');
if (helpOverlay) {
    document.body.appendChild(helpOverlay);
}

var learningDetailOverlay = document.getElementById('learning-detail-overlay');
if (learningDetailOverlay) {
    document.body.appendChild(learningDetailOverlay);
}

// "Vezi ce a invatat" (client's follow-up, 2026-09-18) - copies the profile's pre-rendered
// <template> (server-side, in the table above - no AJAX call needed) into the shared overlay's
// body and shows it. Also remembers which profile this was for, so the overlay's own
// "Testeaza in Previzualizare" button can preselect the same profile there instead of leaving
// the admin to find it again in that dropdown.
function showLearningDetail(profileId) {
    var template = document.getElementById('learning-detail-' + profileId);
    var body = document.getElementById('learning-detail-body');
    if (!template || !body) { return; }

    body.innerHTML = template.innerHTML;
    document.getElementById('learning-detail-overlay').dataset.profileId = profileId;
    document.getElementById('learning-detail-overlay').style.display = 'flex';
}

document.querySelectorAll('#learning-detail-overlay .test-in-preview-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var profileId = document.getElementById('learning-detail-overlay').dataset.profileId;
        var previewSelect = document.getElementById('preview_user_id');
        if (profileId && previewSelect) {
            previewSelect.value = profileId;
        }
    });
});

// Shows/hides the per-profile "Site extern" row (see the table above) - a simple show/hide
// toggle rather than tracking open/closed state, since re-clicking the button while the row is
// already open should just close it again.
function toggleExternalRow(profileId) {
    var row = document.getElementById('external-row-' + profileId);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}

// Live username search for the "Site extern" rows (searchExternalUsers() in the controller) -
// debounced so it doesn't fire an AJAX request on every single keystroke, and scoped with
// event delegation (one listener for the whole page) since these input rows are hidden/shown
// dynamically rather than all present from the start.
(function () {
    var debounceTimers = new WeakMap();

    function runExternalSearch(input, query) {
        var resultsBox = input.parentElement.querySelector('.external-username-results');
        var connectionSelect = input.closest('form').querySelector('[name="connection"]');
        var url = '{{ route('admin_style_learning_external_search') }}?connection='
            + encodeURIComponent(connectionSelect.value) + '&q=' + encodeURIComponent(query);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                var results = data.results || [];
                resultsBox.innerHTML = '';

                if (results.length === 0) {
                    resultsBox.innerHTML = '<div style="padding:6px 10px; color:#888; font-size:12px;">Niciun rezultat</div>';
                } else {
                    results.forEach(function (row) {
                        var item = document.createElement('div');
                        item.style.cssText = 'padding:6px 10px; cursor:pointer; font-size:13px;';
                        item.textContent = row.username + ' (' + (row.firstname || '') + ' ' + (row.lastname || '') + ')';
                        item.addEventListener('mouseenter', function () { item.style.background = '#f0f0f0'; });
                        item.addEventListener('mouseleave', function () { item.style.background = ''; });
                        item.addEventListener('click', function () {
                            input.value = row.username;
                            resultsBox.style.display = 'none';
                        });
                        resultsBox.appendChild(item);
                    });
                }

                resultsBox.style.display = 'block';
            })
            .catch(function () {
                resultsBox.style.display = 'none';
            });
    }

    document.addEventListener('input', function (event) {
        if (!event.target.classList.contains('external-username-input')) { return; }

        var input = event.target;
        var query = input.value.trim();

        clearTimeout(debounceTimers.get(input));

        if (query.length < 2) {
            input.parentElement.querySelector('.external-username-results').style.display = 'none';
            return;
        }

        debounceTimers.set(input, setTimeout(function () { runExternalSearch(input, query); }, 300));
    });

    // Client's follow-up (2026-09-18): "what if the admin doesn't know what names even exist
    // there?" - clicking into an EMPTY field now shows an initial browsable list (first 15
    // alphabetically - see searchExternalUsers()'s $term === '' case) instead of requiring the
    // admin to already know at least 2 letters of a real name before this is any use at all.
    // No debounce here - it's one immediate lookup on focus, not a keystroke-driven stream.
    document.addEventListener('focusin', function (event) {
        if (!event.target.classList.contains('external-username-input')) { return; }

        var input = event.target;
        if (input.value.trim() === '') {
            runExternalSearch(input, '');
        }
    });

    // Clicking anywhere outside a results box closes it - otherwise it stays open forever once
    // opened, covering whatever's rendered below it in the table.
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('external-username-input')) { return; }

        document.querySelectorAll('.external-username-results').forEach(function (box) {
            if (!box.contains(event.target)) {
                box.style.display = 'none';
            }
        });
    });
})();

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
