@extends('admin.components.layout')
@section('content')
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
                            <h3 class="title-3 m-b-30"><i class="fas fa-envelope-open-text"></i> Email Outreach</h3>

                            {{-- Accounts (mailboxes) --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Conturi de email (postafii de trimitere)</strong></div>
                                <div class="card-body">
                                    @if($accounts->count())
                                        <table class="table table-sm">
                                            <thead><tr><td>Nume</td><td>Email</td><td>SMTP</td><td>Limita/zi</td><td>Campanii</td><td></td></tr></thead>
                                            <tbody>
                                                @foreach($accounts as $account)
                                                    <tr>
                                                        <td>{{ $account->label }}</td>
                                                        <td>{{ $account->from_email }}</td>
                                                        <td>{{ $account->smtp_host }}:{{ $account->smtp_port }}</td>
                                                        <td>{{ $account->daily_send_limit }}</td>
                                                        <td>{{ $account->campaigns_count }}</td>
                                                        <td><a href="/admin/email-outreach/accounts/{{ $account->id }}/edit" class="btn btn-sm btn-outline-secondary">Editeaza</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted">Niciun cont adaugat inca.</p>
                                    @endif

                                    <a href="#add-account" data-toggle="collapse" class="btn btn-sm btn-secondary">+ Adauga cont nou</a>
                                    <div id="add-account" class="collapse mt-3">
                                        <form method="post" action="/admin/email-outreach/accounts">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label>Nume (doar pentru identificare)</label>
                                                    <input type="text" name="label" class="form-control" placeholder="ex: trovamequi.me" required>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>Adresa de email</label>
                                                    <input type="email" name="from_email" class="form-control" placeholder="reklame@domeniu.com" required>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>Nume afisat (optional)</label>
                                                    <input type="text" name="from_name" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3 form-group">
                                                    <label>SMTP Host</label>
                                                    <input type="text" name="smtp_host" class="form-control" value="smtp.gmail.com" required>
                                                </div>
                                                <div class="col-md-2 form-group">
                                                    <label>Port</label>
                                                    <input type="number" name="smtp_port" class="form-control" value="587" required>
                                                </div>
                                                <div class="col-md-2 form-group">
                                                    <label>Criptare</label>
                                                    <select name="smtp_encryption" class="form-control">
                                                        <option value="tls">TLS</option>
                                                        <option value="ssl">SSL</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5 form-group">
                                                    <label>Utilizator SMTP (adresa de email)</label>
                                                    <input type="text" name="smtp_username" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label>App Password (SMTP)</label>
                                                    <input type="password" name="smtp_password" class="form-control" autocomplete="new-password" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Limita de trimiteri / zi</label>
                                                    <input type="number" name="daily_send_limit" class="form-control" value="200" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3 form-group">
                                                    <label>IMAP Host</label>
                                                    <input type="text" name="imap_host" class="form-control" value="imap.gmail.com" required>
                                                </div>
                                                <div class="col-md-2 form-group">
                                                    <label>Port</label>
                                                    <input type="number" name="imap_port" class="form-control" value="993" required>
                                                </div>
                                                <div class="col-md-3 form-group">
                                                    <label>Utilizator IMAP (adresa de email)</label>
                                                    <input type="text" name="imap_username" class="form-control" required>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>App Password (IMAP) - de obicei acelasi ca SMTP</label>
                                                    <input type="password" name="imap_password" class="form-control" autocomplete="new-password" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Cheie OpenAI (optional, doar pentru traducerea automata a mesajelor)</label>
                                                <input type="password" name="openai_api_key" class="form-control" autocomplete="new-password">
                                                <small class="form-text text-muted">Daca lipseste, mesajele se trimit exact cum sunt scrise, fara traducere.</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Salveaza cont</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Campaigns --}}
                            <div class="card mb-4">
                                <div class="card-header"><strong>Campanii</strong></div>
                                <div class="card-body">
                                    @if($campaigns->count())
                                        <table class="table table-sm">
                                            <thead><tr><td>Nume</td><td>Cont</td><td>In asteptare</td><td>Noi</td><td>Au raspuns</td><td>Abandonate</td><td></td></tr></thead>
                                            <tbody>
                                                @foreach($campaigns as $campaign)
                                                    <tr>
                                                        <td>{{ $campaign->name }}</td>
                                                        <td>{{ optional($campaign->account)->label }}</td>
                                                        <td><span class="badge badge-info">{{ $campaign->waiting_count }}</span></td>
                                                        <td><span class="badge badge-secondary">{{ $campaign->pending_count }}</span></td>
                                                        <td><span class="badge badge-success">{{ $campaign->replied_count }}</span></td>
                                                        <td><span class="badge badge-warning">{{ $campaign->abandoned_count }}</span></td>
                                                        <td><a href="/admin/email-outreach/campaigns/{{ $campaign->id }}" class="btn btn-sm btn-primary">Deschide</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted">Nicio campanie creata inca.</p>
                                    @endif

                                    @if($accounts->count())
                                        <a href="#add-campaign" data-toggle="collapse" class="btn btn-sm btn-secondary">+ Campanie noua</a>
                                        <div id="add-campaign" class="collapse mt-3">
                                            <form method="post" action="/admin/email-outreach/campaigns">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-4 form-group">
                                                        <label>Nume campanie</label>
                                                        <input type="text" name="name" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4 form-group">
                                                        <label>Cont de trimitere</label>
                                                        <select name="account_id" class="form-control" required>
                                                            @foreach($accounts as $account)
                                                                <option value="{{ $account->id }}">{{ $account->label }} ({{ $account->from_email }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 form-group">
                                                        <label>Limba mesajelor</label>
                                                        <input type="text" name="base_language" class="form-control" value="ro" maxlength="10" required>
                                                    </div>
                                                    <div class="col-md-2 form-group">
                                                        <label>Abandon dupa (zile)</label>
                                                        <input type="number" name="abandon_after_days" class="form-control" value="100" required>
                                                    </div>
                                                </div>
                                                <div class="form-group form-check">
                                                    <input type="checkbox" name="restart_after_abandon" value="1" class="form-check-input" id="restart_after_abandon" checked>
                                                    <label class="form-check-label" for="restart_after_abandon">Reincepe secventa dupa perioada de abandon</label>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Creeaza campanie</button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-muted small">Adauga mai intai un cont de email, ca sa poti crea o campanie.</p>
                                    @endif
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
