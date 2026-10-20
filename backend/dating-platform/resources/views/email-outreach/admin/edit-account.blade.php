@extends('admin.components.layout')
@section('content')
@include('email-outreach.admin._button-fix')
<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-envelope-open-text"></i> Editeaza cont: {{ $account->label }}
                                <a href="/admin/email-outreach" class="btn btn-sm btn-secondary float-right">&larr; Inapoi</a>
                            </h3>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="post" action="/admin/email-outreach/accounts/{{ $account->id }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>Nume (doar pentru identificare)</label>
                                                <input type="text" name="label" class="form-control" value="{{ $account->label }}" required>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Adresa de email</label>
                                                <input type="email" name="from_email" class="form-control" value="{{ $account->from_email }}" required>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Nume afisat (optional)</label>
                                                <input type="text" name="from_name" class="form-control" value="{{ $account->from_name }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label>SMTP Host</label>
                                                <input type="text" name="smtp_host" class="form-control" value="{{ $account->smtp_host }}" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label>Port</label>
                                                <input type="number" name="smtp_port" class="form-control" value="{{ $account->smtp_port }}" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label>Criptare</label>
                                                <select name="smtp_encryption" class="form-control">
                                                    <option value="tls" @if($account->smtp_encryption === 'tls') selected @endif>TLS</option>
                                                    <option value="ssl" @if($account->smtp_encryption === 'ssl') selected @endif>SSL</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5 form-group">
                                                <label>Utilizator SMTP (adresa de email)</label>
                                                <input type="text" name="smtp_username" class="form-control" value="{{ $account->smtp_username }}" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>App Password (SMTP)</label>
                                                <input type="password" name="smtp_password" class="form-control" autocomplete="new-password" placeholder="●●●●●●●● (salvat - lasa gol ca sa pastrezi parola actuala)">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>Limita de trimiteri / zi</label>
                                                <input type="number" name="daily_send_limit" class="form-control" value="{{ $account->daily_send_limit }}" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label>IMAP Host</label>
                                                <input type="text" name="imap_host" class="form-control" value="{{ $account->imap_host }}" required>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label>Port</label>
                                                <input type="number" name="imap_port" class="form-control" value="{{ $account->imap_port }}" required>
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Utilizator IMAP (adresa de email)</label>
                                                <input type="text" name="imap_username" class="form-control" value="{{ $account->imap_username }}" required>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>App Password (IMAP)</label>
                                                <input type="password" name="imap_password" class="form-control" autocomplete="new-password" placeholder="●●●●●●●● (salvat - lasa gol ca sa pastrezi parola actuala)">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Cheie OpenAI (optional, doar pentru traducerea automata a mesajelor)</label>
                                            <input type="password" name="openai_api_key" class="form-control" autocomplete="new-password" placeholder="{{ $account->openai_api_key ? '●●●●●●●● (salvata - lasa gol ca sa pastrezi cheia actuala)' : 'Introdu cheia OpenAI' }}">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Salveaza modificarile</button>
                                    </form>
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
