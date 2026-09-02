@extends('layouts.layout')
@section('content')
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
                            {{-- <h1>{{l("Select the Payment Service Provider")}}</h1> --}}
                        </div>
                        <!--//PRICE HEADING END-->
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                
                
                <div class="generic_content active clearfix">
                        
                        
                        <!--HEAD PRICE DETAIL START-->
                        <div class="generic_head_price clearfix">
                        
                            <!--HEAD CONTENT START-->
                            <div class="generic_head_content clearfix">
                            
                                <!--HEAD START-->
                                <div class="head_bg"></div>
                                <div class="head">
                                    <span>{{$pack->name}}</span>
                                </div>
                                <!--//HEAD END-->
                                
                            </div>
                            <!--//HEAD CONTENT END-->
                            
                            <!--PRICE START-->
                            <div class="generic_price_tag clearfix">
                                <span class="price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">{{$pack->price}}</span>
                                    {{-- <span class="cent">@if($pack->duration >= 30) {{l("/ month")}} @else {{l("/day")}} @endif</span> --}}

                                </span>
                            </div>
                            <!--//PRICE END-->
                            
                        </div>                            
                        <!--//HEAD PRICE DETAIL END-->
                        
                        <!--FEATURE LIST START-->
                        <div class="generic_feature_list">
                            <ul>
                                {{-- <li class="grey">{{l('Package')}} {{$pack->name}}</li> --}}
                                {{-- <li class="grey">{{l('Price')}} {{$pack->price}} {{$pack->currency}}</li> --}}
                                @if($pack->credits != 0)<li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>
                                @else
                                <li><span>{{l("Chat")}}</span> @if($pack->chat == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Likes")}}</span> @if($pack->likes == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Newsfeed")}}</span> @if($pack->newsfeed == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Images")}}</span> @if($pack->images == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Albums")}}</span> @if($pack->albums == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Friends")}}</span> @if($pack->friends == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                @endif
                                @if($pack->duration>0)<li><span>{{l("Available")}}</span> {{$pack->duration}} {{l("days")}}</li>@endif
                            </ul>
                        </div>
                        <!--//FEATURE LIST END-->
                        
                        <!--BUTTON START-->
                        <div class="generic_price_btn clearfix">
                            <form action="{{route('accepted_payment')}}" method="POST" class="spacing">     
                                @csrf
                                    <input name="interval" type="hidden" value="day" />         
                                      <input name="price" type="hidden" value="{{$pack->price*10}}" />         
                                      <input name="currency" type="hidden" value="{{$pack->currency}}" />           
                                      <input name="pack_id" type="hidden" value="{{$pack->id}}" />           
                                      <input name="orderid" type="hidden" value="{{$orderid}}" />           
                                    <script
                                      src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                            data-key="{{env('STRIPE_KEY')}}"
                                            data-name="{{$pack->name}}"
                                            data-email="{{Auth::user()->email}}"
                                            data-description="{{l('Purchanse')}} {{$pack->name}} {{l('plan')}} - {{$pack->price}} {{$pack->currency}}"
                                            data-panel-label="@if(Auth::user()->package() && Auth::user()->package()->id == $pack->id) {{l("Extend")}} @else {{l("Purchase")}} @endif"
                                            data-label="@if(Auth::user()->package() && Auth::user()->package()->id == $pack->id) {{l("Extend")}} @else {{l("Purchase")}} @endif"
                                            data-locale="auto">
                                    </script>
                                  </form>
                        </div>
                        <!--//BUTTON END-->
                        
                    </div>

                

                </div>
            </div>                    
        </div>
        </main>
    </div>
</div>
@endsection
