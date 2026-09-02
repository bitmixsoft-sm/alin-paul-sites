@extends('layouts.layout')
@section('content')
@auth

<style type="text/css" title="">
table td {font-size:18px;color:#fff;}
 h1, h5 {color:#fff !important;}
.wire-details {color:#ff0000;font-size:16px;padding:20px;}

.generic_price_table2{padding: 30px 0;}
.generic_price_table2 {
    background: #d6336c;
    background: linear-gradient(90deg, #d6336c 0%, #c539b6 35%, #e64980 100%);
    color:#fff;
        border-radius: 7px;
    }
</style>

<div class="container">
    <div class="row">

        <!-- Main Content -->
        <main class="col col-xl-12 order-xl-2 col-lg-12 order-lg-1 col-md-12 col-sm-12 col-12">
        <div class="generic_price_table">
        <div class="generic_price_table2">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!--PRICE HEADING START-->
                        <div class="price-heading clearfix">
                            <h1>{{l("Wire transfer payment details")}}</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                <div class="table-responsive">
                <table class="table">
                <tr>
                    <td class="font-weight-bold border-right w-50">Package name</td>
                    <td>{{$pack->name}}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-right w-50">Price</td>
                    <td>
                        @if(Auth::user()->getDiscountByPack($pack->id))
                        @php
                        $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
                        $pack->new_price = ((100-$discount_value)/100)*$pack->price;
                        @endphp
                            {{number_format($pack->new_price, 3, '.', ',')}}
                        @else
                            {{$pack->price}}
                        @endif
                         EUR</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
            </table>
            </div></div></div>
            
            <div class="row">
            <div class="col-md-3"></div>
                <div class="col-md-6 text-center"><h5 class="wire-details">{{l('You have to make wire transfer payment to the following bank account')}}</h5></div>
            </div>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                <div class="table-responsive">
                <table class="table">
                <tr>
                    <td class="font-weight-bold border-right w-50">{{l('Account Name:')}}</td>
                    <td>{{optional(\App\Settings::where('name', "Titular cont")->first())->value}}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-right w-50">{{l('Bank Name:')}}</td>
                    <td>{{optional(\App\Settings::where('name', "Denumire Banca")->first())->value}}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-right w-50">{{l('Account Number / IBAN:')}}</td>
                    <td>{{optional(\App\Settings::where('name', "Cod IBAN")->first())->value}}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-right w-50">{{l('Routing SWIFT Code:')}}</td>
                    <td>{{optional(\App\Settings::where('name', "Cod SWIFT")->first())->value}}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        On the payment order, please mention the package ordered: <strong class="text-success">{{$pack->name}}</strong><br />
                        As soon as your payment is confirmed, an administrator will assign you the package with the ordered services.
                    </td>
                </tr>
                </table>
                </div>

                
                </div>
            </div>                    
        </div>
        </div>
        </main>
    </div>
</div>
@endauth
@endsection

@section('scripts')

@endsection