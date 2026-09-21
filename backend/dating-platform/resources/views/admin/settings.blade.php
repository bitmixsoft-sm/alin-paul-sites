@extends('admin.components.layout')
@section('content')
        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="user-data m-b-30">
                                    <div class="row">
                                        <h3 class="title-3 m-b-30"><i class="fas fa-globe"></i>{{$on_page}}</h3>
                                    </div>
                                        <div class="container">
                                            <form action="/admin/settings/save" method="post" enctype="multipart/form-data" class="form-horizontal">
                                                @csrf
                                                {{-- Grouped by category (SettingsController::index()) instead of one long flat
                                                     list - reported live as genuinely hard to find things in, especially as
                                                     more paid-feature settings (Boost's, and similar features after it) get
                                                     added. "General" (every never-categorized row - the vast majority) is
                                                     sorted last so the named, intentional groups stand out at the top. This
                                                     is purely visual - every field still posts by its settings.id name, same
                                                     as before, so SettingsController::store() needed no changes. --}}
                                                @foreach($groupedSettings as $category => $group)
                                                    <div class="card mb-4">
                                                        <div class="card-header"><strong>{{ $category }}</strong></div>
                                                        <div class="card-body">
                                                            @foreach($group as $setting)
                                                            @php
                                                                // Naming convention (parallel to the *_ENABLED/*_ACTIVE enabler switches
                                                                // above): a <PREFIX>_PRICE_MODE select with options "credits"/"money"
                                                                // (currently only BOOST_PRICE_MODE) picks which ONE of its sibling
                                                                // <PREFIX>_COST_CREDITS / <PREFIX>_PRICE_AMOUNT fields is actually relevant -
                                                                // the other one is dead, ignored input while that mode is off, so it's
                                                                // hidden instead of just sitting there looking equally valid. Read by the
                                                                // script at the bottom of this file. Any future paid feature that follows
                                                                // this same 3-setting naming pattern gets this for free.
                                                                $priceModeFieldValue = str_ends_with($setting->name, '_COST_CREDITS') ? 'credits'
                                                                    : (str_ends_with($setting->name, '_PRICE_AMOUNT') ? 'money' : null);
                                                            @endphp
                                                            <div class="row form-group"
                                                                @if(strpos($setting->type, 'select|') !== false && str_ends_with($setting->name, '_PRICE_MODE'))
                                                                    data-price-mode-select="1"
                                                                @elseif($priceModeFieldValue)
                                                                    data-price-mode-field="{{ $priceModeFieldValue }}"
                                                                @endif
                                                            >
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">{{$setting->id}}. {{$setting->name}}</label>
                                                                </div>
                                                                @if($setting->type == 'image')
                                                                <div class="col col-sm-6">
                                                                    <input type="file" name="{{$setting->id}}" value="{{$setting->value}}" class="form-control" accept="image/png, image/jpeg">
                                                                </div>
                                                                @elseif($setting->type == '' || $setting->type == 'text')
                                                                <div class="col col-sm-6">
                                                                    <input type="text" name="{{$setting->id}}" value="{{$setting->value}}" class="form-control">
                                                                    {{-- BOOST_PRICE_AMOUNT has no currency in its own name/value - shown here
                                                                         instead so the admin can see, at a glance, what currency the number
                                                                         actually means. Plain EUR, not a per-provider lookup: every payment
                                                                         provider on this site (CentralPay, PayPal, CCBill, Packages) is
                                                                         EUR-only today - Packages' own "Pachete Adaugare" currency dropdown
                                                                         has no other option either - so there's nothing else it could say
                                                                         without implying a multi-currency setup that doesn't actually exist
                                                                         yet anywhere in the app. --}}
                                                                    @if($setting->name === 'BOOST_PRICE_AMOUNT')
                                                                        <small class="form-text text-muted">Moneda: <strong>EUR</strong></small>
                                                                    @endif
                                                                    {{-- Client's explicit request (2026-09-18): make sure raising these isn't
                                                                         read as a free "the bigger the better" lever - see
                                                                         ProfileTranscriptBuilder, which sends this much text to OpenAI on
                                                                         every single "Invata stil"/"Invata fraze"/"Site extern" click. --}}
                                                                    @if(in_array($setting->name, ['AI_STYLE_LEARNING_MAX_MESSAGES', 'AI_STYLE_LEARNING_MAX_CHARS']))
                                                                        <small class="form-text text-danger">
                                                                            Atentie: o valoare mai mare inseamna mai mult text trimis catre OpenAI
                                                                            la fiecare "Invata stil"/"Invata fraze" - asta costa (taxa API OpenAI)
                                                                            si incetineste raspunsul. Nu este un "cu cat mai mult, cu atat mai
                                                                            bine" gratuit.
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                                @elseif($setting->type == 'toggle' && (str_ends_with($setting->name, '_ENABLED') || str_ends_with($setting->name, '_ACTIVE')))
                                                                    {{-- A visual on/off switch instead of the plain Da/Nu dropdown other
                                                                         toggles below still use - scoped to *_ENABLED/*_ACTIVE rows only
                                                                         (the "master switch" for a whole paid-feature or payment-provider
                                                                         section, e.g. BOOST_FEATURE_ENABLED or STRIPE_ACTIVE), not every
                                                                         toggle site-wide, to keep this change small and low-risk. data-settings-enabler is read by the
                                                                         script at the bottom of this file, which grays out/disables every
                                                                         OTHER field in the same category card while this is off - so an
                                                                         admin can't be misled into thinking a field does something while
                                                                         its whole feature is switched off.
                                                                         Hidden input + checkbox sharing one name is the standard trick for
                                                                         a checkbox that reliably posts "no" when unchecked - a lone
                                                                         unchecked checkbox submits nothing at all, which
                                                                         SettingsController::store() (only touches keys actually present
                                                                         in the request) would otherwise silently treat as "leave
                                                                         unchanged" instead of "turn off". --}}
                                                                    <div class="col col-sm-6">
                                                                        <input type="hidden" name="{{$setting->id}}" value="no" data-settings-enabler-hidden="1">
                                                                        <label class="settings-switch">
                                                                            <input type="checkbox" name="{{$setting->id}}" value="yes" data-settings-enabler="1" @if($setting->value == 'yes') checked @endif>
                                                                            <span class="settings-switch-track"></span>
                                                                        </label>
                                                                    </div>
                                                                @elseif($setting->type == 'toggle')
                                                                <div class="col col-sm-6">
                                                                    <select name="{{$setting->id}}" class="form-control">
                                                                        <option @if($setting->value == 'yes') selected @endif value="yes">Da</option>
                                                                        <option @if($setting->value == 'no') selected @endif value="no">Nu</option>
                                                                    </select>
                                                                    {{-- Client's follow-up (2026-09-18): explain what this actually
                                                                         changes, not just its raw setting name - see
                                                                         ProfileTranscriptBuilder's docblock for the full reasoning
                                                                         behind the "Da" default. --}}
                                                                    @if($setting->name === 'AI_STYLE_LEARNING_INCLUDE_BOTH_PARTIES')
                                                                        <small class="form-text text-muted">
                                                                            <strong>Da</strong> (recomandat) - AI-ul vede intreaga conversatie
                                                                            (si ce a scris profilul, si ce a scris clientul) - ajuta la
                                                                            "Stil (ton)", ca sa inteleaga la ce a reactionat asa.
                                                                            <strong>Nu</strong> - AI-ul vede doar mesajele scrise de profil,
                                                                            fara raspunsurile clientului - poate fi mai curat pentru
                                                                            "Fraze exacte", dar se pierde contextul conversatiei.
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                                @elseif(strpos($setting->type, 'select|')!==false)
                                                                <div class="col col-sm-6">
                                                                    <select name="{{$setting->id}}" class="form-control"
                                                                        @if(str_ends_with($setting->name, '_PRICE_MODE')) data-price-mode-select-input="1" @endif>
                                                                        @foreach(explode("~", str_replace("select|", "", $setting->type)) as $line)
                                                                        <option @if($setting->value == $line) selected @endif value="{{$line}}">{{$line}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                @endif
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <hr>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Modifica
                                                        </button>
                                            </form>
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
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

<style>
/* Green/gray on-off switch for *_ENABLED settings - see the toggle branch above for why this
   is scoped to just those instead of every toggle on the page. */
.settings-switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
    vertical-align: middle;
}
.settings-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.settings-switch-track {
    position: absolute;
    inset: 0;
    background: #ccc;
    border-radius: 24px;
    cursor: pointer;
    transition: background-color .15s ease;
}
.settings-switch-track::before {
    content: "";
    position: absolute;
    left: 3px;
    top: 3px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform .15s ease;
}
.settings-switch input:checked + .settings-switch-track {
    background: #4caf50;
}
.settings-switch input:checked + .settings-switch-track::before {
    transform: translateX(22px);
}
</style>
<script>
// Disables (grays out, non-interactive) every other field in the same category card while
// its *_ENABLED switch (data-settings-enabler, see the blade section above) is off - so an
// admin can't be misled into thinking a field still does something while its whole feature is
// switched off. Purely visual on top of a real `disabled` attribute: a disabled field also
// doesn't get submitted at all, which is fine here - SettingsController::store() only updates
// settings whose id key is actually present in the request, so those fields just keep
// whatever value they already had in the database while the feature stays off.
document.querySelectorAll('[data-settings-enabler]').forEach(function (enabler) {
    var card = enabler.closest('.card');
    if (!card) { return; }

    function applyState() {
        var enabled = enabler.checked;
        card.querySelectorAll('input, select').forEach(function (field) {
            // Skips the enabler checkbox itself and its own paired hidden "no" input (the
            // reliable-unchecked-checkbox trick above) - disabling that hidden sibling would
            // stop the switch's OWN off-state from ever being submitted, the opposite of what
            // this is for.
            if (field === enabler || field.hasAttribute('data-settings-enabler-hidden')) { return; }
            field.disabled = !enabled;
        });
        card.querySelectorAll('.row.form-group').forEach(function (row) {
            if (row.contains(enabler)) { return; }
            row.style.opacity = enabled ? '1' : '.5';
        });
    }

    enabler.addEventListener('change', applyState);
    applyState();
});

// Hides (not just grays out - it's not "off", it's not applicable) whichever of a
// <PREFIX>_COST_CREDITS / <PREFIX>_PRICE_AMOUNT pair doesn't match the sibling
// <PREFIX>_PRICE_MODE select's current value - see the blade section above for the naming
// convention this relies on (currently just Boost's 3 settings, generic for future paid
// features that follow the same pattern).
document.querySelectorAll('[data-price-mode-select-input]').forEach(function (select) {
    var row = select.closest('[data-price-mode-select]');
    var card = select.closest('.card');
    if (!row || !card) { return; }

    function applyState() {
        var mode = select.value;
        card.querySelectorAll('[data-price-mode-field]').forEach(function (field) {
            // Plain `.hidden` isn't reliable here - Bootstrap's `.row{display:flex}` (same
            // specificity, later in the cascade) overrides the `[hidden]` UA style, so this
            // sets `display` directly instead, same as the opacity toggle above.
            field.style.display = field.getAttribute('data-price-mode-field') === mode ? '' : 'none';
        });
    }

    select.addEventListener('change', applyState);
    applyState();
});
</script>
@endsection
