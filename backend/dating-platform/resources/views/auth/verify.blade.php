@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ l('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ l('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ l('Before proceeding, please check your email for a verification link.') }}
                    {{ l('If you did not receive the email') }}, <a href="{{ route('verification.resend') }}">{{ l('click here to request another') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
