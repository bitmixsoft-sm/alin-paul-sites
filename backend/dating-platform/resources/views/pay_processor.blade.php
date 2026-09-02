@extends('layouts.layout')
@section('content')
@auth
<div class="header-spacer"></div>
    


<div class="container">
    <div class="row">

        <!-- Main Content -->
        <main class="col col-xl-12 order-xl-2 col-lg-12 order-lg-1 col-md-12 col-sm-12 col-12">
        <div class="generic_price_table ">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!--PRICE HEADING START-->
                        <div class="price-heading clearfix">
                            <h1>{{l("Select the Payment Service Provider")}}</h1>
                        </div>
                        <!--//PRICE HEADING END-->
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
        
                @if ($PP_active == 'yes' || $CCB_active == 'yes' || $CP_active == 'yes')
                <form action="{{ route('new_payment') }}" method="get" id="pp_select">
                    @csrf
                    <input type="hidden" name="pack_id" value="{{ Request::get('pack_id') }}">
                    <div class="form-group label-floating is-select m-auto w-50">
                    <label class="control-label">{{l("Payment Service Provider")}}</label>
                    <select name="pay_processor" class="form-control ppselect">
                    @if ($PP_active == 'yes')
                    <option value="paypal">{{ l("Paypal") }}</option>
                    @endif
                    @if ($CCB_active == 'yes')
                    <option value="ccbill">{{ l("CCBill") }}</option>
                    @endif
                    @if ($CP_active == 'yes')
                    <option value="centralpay" class="p-5">{{ l("Centralpay") }}</option>
                    @endif
                    </select>
                    </div>

                    <div class="form-group m-auto w-50 pt-3">
                    <input type="submit" name="purchase" class="btn btn-primary btn-md-2" value="{{l("Purchase")}}">
                    </div>
                </form>
                @endif
                @php
                    /*
                @endphp
                <div class="text-center">
                    <a href="https://customer.centralpay.net/home/ad8d9e92-2b77-479f-9497-a110d65008dc" target="_blank"><img src="/img/Centralpay.png" alt=""></a>      
                </div>
                @php
                    */
                @endphp
                </div>
            </div>                    
        </div>
        </main>
    </div>
</div>

@endauth
@endsection