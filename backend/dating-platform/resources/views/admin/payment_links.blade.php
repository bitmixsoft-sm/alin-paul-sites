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
                            <h3 class="title-3 m-b-30"><i class="fas fa-link"></i> Link de plata pentru client</h3>

                            <p class="text-muted">
                                Generati un link de plata legat de contul clientului (nu de contul de admin), apoi
                                trimiteti-l clientului. Clientul trebuie sa aiba deja cont pe site. Linkul este valabil
                                {{ $validDays }} zile; la deschidere, clientul este dus direct la plata CCBill.
                            </p>

                            @if(session('link_error'))
                                <div class="alert alert-danger" role="alert">{{ session('link_error') }}</div>
                            @endif

                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="post" action="/admin/payment-links">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-5 form-group">
                                                <label>Emailul clientului (cel din cont)</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-md-5 form-group">
                                                <label>Pachet</label>
                                                <select name="pack_id" class="form-control" required>
                                                    @foreach($packs as $pack)
                                                        <option value="{{ $pack->id }}" @if(old('pack_id') == $pack->id) selected @endif>
                                                            {{ $pack->name }} - {{ $pack->price }} {{ $pack->currency }} ({{ $pack->type }}, {{ $pack->duration }} zile)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 form-group d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary">Genereaza link</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if(session('link'))
                                @php $target = session('link_target'); @endphp
                                <div class="card mb-4">
                                    <div class="card-header"><strong>Link generat</strong></div>
                                    <div class="card-body">
                                        <p>
                                            Client: <strong>{{ $target['name'] }}</strong> ({{ $target['email'] }})<br>
                                            Pachet: <strong>{{ $target['pack'] }}</strong> - {{ $target['price'] }} {{ $target['currency'] }}
                                        </p>
                                        <div class="input-group">
                                            <input type="text" id="payment-link-output" class="form-control" value="{{ session('link') }}" readonly onclick="this.select();">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-secondary" onclick="var i=document.getElementById('payment-link-output');i.select();document.execCommand('copy');this.textContent='Copiat!';">Copiaza</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Pretul final (reduceri incluse) se calculeaza cand clientul deschide linkul.</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
