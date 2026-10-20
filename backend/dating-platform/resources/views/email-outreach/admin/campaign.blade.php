@extends('admin.components.layout')
@section('content')
@include('email-outreach.admin._button-fix')
<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-envelope-open-text"></i> {{ $campaign->name }}
                                <a href="/admin/email-outreach" class="btn btn-sm btn-secondary float-right">&larr; Inapoi</a>
                                <form method="post" action="/admin/email-outreach/campaigns/{{ $campaign->id }}/run-now" class="float-right mr-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Trimite acum</button>
                                </form>
                            </h3>

                            {{-- Stats --}}
                            <div class="row mb-4">
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['total'] }}</strong><br><small>Total</small></div></div>
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['sent'] }}</strong><br><small>Trimise</small></div></div>
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['opened'] }}</strong><br><small>Deschise</small></div></div>
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['waiting'] }}</strong><br><small>In asteptare</small></div></div>
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['replied'] }}</strong><br><small>Au raspuns</small></div></div>
                                <div class="col"><div class="card text-center p-2"><strong>{{ $stats['abandoned'] }}</strong><br><small>Abandonate</small></div></div>
                            </div>

                            {{-- Phrases / messages --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Fraze (max. 7, trimise in ordine)</strong></div>
                                <div class="card-body">
                                    @for($step = 1; $step <= 7; $step++)
                                        @php $msg = $campaign->messages->firstWhere('step_order', $step); @endphp
                                        <form method="post" action="/admin/email-outreach/campaigns/{{ $campaign->id }}/messages" enctype="multipart/form-data" class="form-group border-bottom pb-3 mb-3">
                                            @csrf
                                            <input type="hidden" name="step_order" value="{{ $step }}">
                                            <label><strong>Fraza {{ $step }}</strong></label>
                                            <textarea name="body" class="form-control mb-2" rows="2" placeholder="Lasa gol daca nu vrei sa folosesti aceasta fraza">{{ $msg->body ?? '' }}</textarea>
                                            <div class="d-flex align-items-center">
                                                <input type="file" name="image" accept="image/*" class="mr-3">
                                                @if($msg && $msg->image_path)
                                                    <small class="text-muted mr-3">Imagine atasata deja.</small>
                                                @endif
                                                <button type="submit" class="btn btn-sm btn-primary">Salveaza fraza {{ $step }}</button>
                                            </div>
                                        </form>
                                    @endfor
                                    <small class="text-muted">Poti folosi {{ '{nume}' }} in text - se inlocuieste cu prenumele destinatarului, daca este cunoscut.</small>
                                </div>
                            </div>

                            {{-- Import list --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Adauga lista de email-uri</strong></div>
                                <div class="card-body">
                                    <form method="post" action="/admin/email-outreach/campaigns/{{ $campaign->id }}/recipients">
                                        @csrf
                                        <textarea name="emails" class="form-control mb-2" rows="6" placeholder="un email pe linie, sau separate prin virgula"></textarea>
                                        <button type="submit" class="btn btn-primary">Adauga in campanie</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Recipients --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Destinatari</strong></div>
                                <div class="card-body">
                                    {{-- Client's question (2026-10-20): "a list of 100-500+ addresses, do I
                                         have to delete them one by one?" - two bulk options below.
                                         bulk-delete-form has no fields of its own; the per-row checkboxes
                                         live inside the table (form="bulk-delete-form" ties them to it by
                                         id, since a <form> can't physically wrap a <table>'s cells AND also
                                         contain the separate per-row single-delete <form>s below without
                                         illegally nesting forms). --}}
                                    <form id="bulk-delete-form" method="post" action="/admin/email-outreach/campaigns/{{ $campaign->id }}/recipients/bulk-delete">
                                        @csrf
                                    </form>
                                    <form method="post" action="/admin/email-outreach/campaigns/{{ $campaign->id }}/recipients/delete-all"
                                          onclick="return confirm('Sigur vrei sa stergi TOATA lista ({{ $stats['total'] }} destinatari) din aceasta campanie? Aceasta actiune nu poate fi anulata.');"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger mb-2">Sterge toata lista ({{ $stats['total'] }})</button>
                                    </form>
                                    <button type="submit" form="bulk-delete-form" class="btn btn-sm btn-danger mb-2"
                                            onclick="return confirm('Sigur vrei sa stergi destinatarii bifati?');">Sterge selectatii</button>

                                    <table class="table table-sm">
                                        <thead><tr>
                                            <td><input type="checkbox" onclick="document.querySelectorAll('.recipient-check').forEach(function(c){c.checked=this.checked;}, this)"></td>
                                            <td>Email</td><td>Status</td><td>Pas</td><td>Ultima trimitere</td><td>Ultima reactie</td><td></td>
                                        </tr></thead>
                                        <tbody>
                                            @foreach($recipients as $recipient)
                                                <tr>
                                                    <td><input type="checkbox" class="recipient-check" name="recipient_ids[]" value="{{ $recipient->id }}" form="bulk-delete-form"></td>
                                                    <td>{{ $recipient->email }}</td>
                                                    <td>
                                                        @php
                                                            $badgeClass = [
                                                                'pending' => 'secondary', 'waiting' => 'info', 'replied' => 'success',
                                                                'manual' => 'primary', 'abandoned' => 'warning', 'bounced' => 'danger',
                                                            ][$recipient->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }}">{{ $recipient->status }}</span>
                                                    </td>
                                                    <td>{{ $recipient->current_step }}/7</td>
                                                    <td>{{ optional($recipient->last_sent_at)->format('d/m/Y H:i') }}</td>
                                                    <td>{{ optional($recipient->last_engaged_at)->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if($recipient->status === 'replied')
                                                            <a href="#reply-{{ $recipient->id }}" data-toggle="collapse" class="btn btn-sm btn-primary">Raspunde</a>
                                                            <form method="post" action="/admin/email-outreach/recipients/{{ $recipient->id }}/continue" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-secondary">Continua automat</button>
                                                            </form>
                                                        @elseif(in_array($recipient->status, ['waiting', 'pending']))
                                                            <form method="post" action="/admin/email-outreach/recipients/{{ $recipient->id }}/take-over" class="d-inline">
                                                                @csrf
                                                                {{-- btn-info (solid), not btn-outline-primary - outline buttons render
                                                                     with invisible text on this admin theme (storage/app/assets/css/
                                                                     style.css, loaded on admin pages too, overrides button text to
                                                                     white regardless of background - only visible on :hover, when
                                                                     Bootstrap's own outline-button hover state fills in a colored
                                                                     background). Solid buttons (already used everywhere else in
                                                                     this view) don't have that problem. --}}
                                                                <button type="submit" class="btn btn-sm btn-info">Preia manual</button>
                                                            </form>
                                                        @endif
                                                        @if($recipient->status === 'manual')
                                                            <a href="#reply-{{ $recipient->id }}" data-toggle="collapse" class="btn btn-sm btn-primary">Scrie</a>
                                                        @endif
                                                        <form method="post" action="/admin/email-outreach/recipients/{{ $recipient->id }}/delete" class="d-inline"
                                                              onclick="return confirm('Sigur vrei sa stergi {{ $recipient->email }} din aceasta campanie? Nu va mai primi niciun email.');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">Sterge</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @if(in_array($recipient->status, ['replied', 'manual']))
                                                    <tr id="reply-{{ $recipient->id }}" class="collapse">
                                                        <td colspan="7">
                                                            <form method="post" action="/admin/email-outreach/recipients/{{ $recipient->id }}/reply">
                                                                @csrf
                                                                <textarea name="body" class="form-control mb-2" rows="3" placeholder="Raspunsul tau catre {{ $recipient->email }}" required></textarea>
                                                                <button type="submit" class="btn btn-sm btn-primary">Trimite raspuns</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $recipients->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
