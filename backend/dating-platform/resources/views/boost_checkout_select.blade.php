@extends('layouts.layout')
@section('content')
@auth

{{-- Only shown when more than one non-Stripe payment provider is active at once
     (BoostController::checkout()) - with just one, checkout() skips straight to
     PaymentsController::newpayment() without this stop. Deliberately minimal (a plain link
     list, not the packages.blade.php "Select Payment method" modal/JS) since this page has
     nothing else on it to layer a modal over. --}}
<style type="text/css">
.boost-checkout-select .generic_price_table2{padding: 30px 0;}
.boost-checkout-select .generic_price_table2 {
    background: #d6336c;
    background: linear-gradient(90deg, #d6336c 0%, #c539b6 35%, #e64980 100%);
    color:#fff;
    border-radius: 7px;
}
.boost-checkout-select h1 {color:#fff !important;}
.boost-checkout-select .payment-method-link {
    display: block;
    margin: 12px auto;
    max-width: 320px;
    padding: 14px 20px;
    background: #fff;
    color: #333;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
    text-decoration: none;
}
.boost-checkout-select .payment-method-link:hover {
    background: #f2f2f2;
}
</style>

<div class="container boost-checkout-select">
    <div class="row">
        <main class="col col-xl-12 order-xl-2 col-lg-12 order-lg-1 col-md-12 col-sm-12 col-12">
            <div class="generic_price_table">
                <div class="generic_price_table2">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="price-heading clearfix">
                                    <h1>{{ l('Select Payment method') }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            @foreach($activeProviders as $providerSetting)
                                <a class="payment-method-link" href="{{ route('new_payment', ['pack_id' => $pack->id, 'payment_method' => $providerSetting]) }}">
                                    {{ str_replace('_', ' ', str_replace('_ACTIVE', '', ucwords(strtolower($providerSetting)))) }}
                                    &nbsp;({{ $price }} {{ env('CENTRALPAY_CURRENCY') ?: 'EUR' }})
                                </a>
                            @endforeach
                        </div>
                        <div class="col-md-3"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endauth
@endsection
