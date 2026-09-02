@extends('layouts.layout')
@section('content')
@auth
<div class="header-spacer"></div>

{{$allow_unsubscribe = 0}}
<div class="container-fluid pkgpage">
    <div class="row">

        <!-- Main Content -->
                @php
                    $active_custom_pack = App\Settings::where('id', 13)->firstOrFail();
                    $active_only_custom_pack = App\Settings::where('id', 21)->firstOrFail();
                @endphp
        <main class="col col-xl-12 order-xl-2 col-lg-12 order-lg-1 col-md-12 col-sm-12 col-12">
            
            <div class="container-fluid pkg">
            <div class="row">
            <div class="col-md-12"><div class="price-heading clearfix">
            @if($allow_unsubscribe &&  App\Order::where(['user_id'=>Auth::user()->id, 'status'=>'Accepted'])->whereNotNull('subscription_id')->orderBy('id', 'desc')->limit(1)->count()) <a href="/payments/unsubscribe" class="btn btn-primary btn-md-2" onclick="return confirm('{{l("Are you sure you want to unsubscribe?")}}');FB_Checkout();">{{l("Unsubscribe")}}</a>@endif
            </div></div>
            </div>
            </div>
            
            @if($active_custom_pack->value == 'yes' && $active_only_custom_pack->value == 'yes')
            <div class="generic_price_table ">
                <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!--PRICE HEADING START-->
                    <div class="price-heading clearfix">
                        <h1>{{l("Create your Custom Package")}}</h1>
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
                                            <span>{{l("Custom Package")}}</span>
                                        </div>
                                        <!--//HEAD END-->
                                        
                                    </div>
                                    <!--//HEAD CONTENT END-->
                                    
                                    <!--PRICE START-->
                                    <div class="generic_price_tag clearfix">
                                        
                                        <span class="price">
                                            <form id="custom_price_from" method="GET" action="/payments">
                                                <span id="custom_price"><input type="number" min="3" step="1" name="value" placeholder="{{l('Enter how much you want to spend')}}"></span>
                                                <input type="hidden" name="pack_id" value="custom">
                                            </form>
                                        </span>
                                    </div>
                                    <!--//PRICE END-->
                                    
                                </div>                            
                                <!--//HEAD PRICE DETAIL END-->
                                
                                <!--FEATURE LIST START-->
                                <div class="generic_feature_list">
                                    <ul>
                                        {{-- <li><span>{{l("Credits")}}</span> <span id="custom_show_credits">0</span></li>
                                        <li><span>{{l("Likes")}}</span> <span id="custom_show_likes">{{l("No")}}</span></li>
                                        <li><span>{{l("Newsfeed")}}</span> <span id="custom_show_newsfeed">{{l("No")}}</span></li>
                                        <li><span>{{l("Images")}}</span> <span id="custom_show_images">{{l("No")}}</span></li>
                                        <li><span>{{l("Albums")}}</span> <span id="custom_show_albums">{{l("No")}}</span></li>
                                        <li><span>{{l("Friends")}}</span> <span id="custom_show_friends">{{l("No")}}</span></li>
                                        <li><span>{{l("Messages")}}</span> {{l("Unlimited")}}</li> --}}
                                        <li><span>{{l("Available")}}</span> <span id="custom_show_days">0</span> {{l("days")}}</li>
                                    </ul>
                                </div>
                                <!--//FEATURE LIST END-->
                                <div class="generic_price_btn clearfix">

                                    <a id="pay_custom_pack" href="/payments?pack_id=custom" onclick="FB_Checkout();">{{l("Purchase")}}</a>
                                </div>
                                
                            </div>
                        </div>
                        </div>
                        <div class="col-md-3"></div>
                    </div>
            @else
                <div class="generic_price_table">   
<section>
        @if(Auth::user()->hasDiscount())
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!--PRICE HEADING START-->
                    <div class="price-heading clearfix">
                        <h1>{{l("Discounts")}}</h1>
                    </div>
                    <!--//PRICE HEADING END-->
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="discount-countdown" data-count="{{Auth::user()->getDiscount()->ending_at}}">
                        <span>Hurry! This offer is available only:</span>
                        <span id="discount-countdown"></span>
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
        </div>
        <div class="container-fluid mg-bot-50">
            
            <!--BLOCK ROW START-->
            <div class="row">
                <div class="col-md-3">
                </div>
                @foreach(Auth::user()->getPackDiscounts() as $pack)
                @php
                $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
                $pack->new_price = ((100-$discount_value)/100)*$pack->price;
                @endphp
                @if($pack->type == 'credits')
                <div class="col-md-6">
                
                    <!--PRICE CONTENT START-->
                    <div class="generic_content active clearfix">
                        <div class="ribbon-wrapper-red">
                            <div class="ribbon-red">-{{$discount_value}}%</div>
                          </div>
                        
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
                                <span id="price-discounted" class="price discounted">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else
                                    @if($pack->duration >= 30) 
                                    {{number_format($pack->price/($pack->duration/30), 2, '.', ',')}} 
                                    @else
                                    {{number_format($pack->price/$pack->duration, 2, '.', ',')}} 
                                    @endif @endif</span>
                                    <span class="cent"></span>

                                </span>
                                <span class="price discounted new_price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->new_price/$pack->credits, 3, '.', ',')}} @else {{number_format($pack->new_price/$pack->duration, 2, '.', ',')}} @endif </span>
                                    <span class="cent">/ credit</span>

                                </span>
                            </div>
                            <!--//PRICE END-->
                            
                        </div>                            
                        <!--//HEAD PRICE DETAIL END-->
                        
                        <!--FEATURE LIST START-->
                        <div class="generic_feature_list">
                            <ul>
                                <li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>
                            </ul>
                        </div>
                        <!--//FEATURE LIST END-->
                        
                        <!--BUTTON START-->
                        <div class="generic_price_btn clearfix">
                            <a href="/payments?pack_id={{$pack->id}}" class="pack" data-pack="{{$pack->id}}" data-price="{{$pack->price}}" data-description="{{$pack->name}}" data-currency="{{$pack->currency}}" onclick="FB_Checkout();">{{l("Purchase")}}</a>
                        </div>
                        <!--//BUTTON END-->
                        
                    </div>
                    <!--//PRICE CONTENT END-->
                        
                </div>
                @else
                <div class="col-md-6">
                
                    <!--PRICE CONTENT START-->
                    <div class="generic_content active clearfix">
                        <div class="ribbon-wrapper-red">
                            <div class="ribbon-red">-{{$discount_value}}%</div>
                          </div>
                        
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
                                <span id="price-discounted" class="price discounted">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else 
                                    @if($pack->duration >= 30) 
                                    {{number_format($pack->price/($pack->duration/30), 2, '.', ',')}} 
                                    @else
                                    {{number_format($pack->price/$pack->duration, 2, '.', ',')}} 
                                    @endif @endif</span>
                                    <span class="cent"></span>
                                </span>
                                <span class="price discounted new_price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->new_price/$pack->credits, 3, '.', ',')}} @else
                                    @if($pack->duration >= 30) 
                                    {{number_format($pack->new_price/($pack->duration/30), 2, '.', ',')}} 
                                    @else
                                    {{number_format($pack->new_price/$pack->duration, 2, '.', ',')}} 
                                    @endif
                                @endif </span>
                                    <span class="cent">@if($pack->duration >= 30) {{l("/ month")}} @else {{l("/day")}} @endif</span>
                                </span>
                            </div>
                            <!--//PRICE END-->
                            
                        </div>                            
                        <!--//HEAD PRICE DETAIL END-->
                        
                        <!--FEATURE LIST START-->
                        <div class="generic_feature_list">
                            <ul>
                                {{-- @if($pack->credits != 0)<li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>@endif
                                <li><span>{{l("Chat")}}</span> @if($pack->chat == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Likes")}}</span> @if($pack->likes == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Newsfeed")}}</span> @if($pack->newsfeed == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Images")}}</span> @if($pack->images == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Albums")}}</span> @if($pack->albums == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Friends")}}</span> @if($pack->friends == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li> --}}
                                <li><span>{{l("Available")}}</span> {{$pack->duration}} {{l("days")}}</li>
                            </ul>
                        </div>
                        <!--//FEATURE LIST END-->
                        
                        <!--BUTTON START-->
                        <div class="generic_price_btn clearfix">
                            <a href="/payments?pack_id={{$pack->id}}" class="pack" data-pack="{{$pack->id}}" data-price="{{$pack->price}}" data-description="{{$pack->name}}" data-currency="{{$pack->currency}}"  onclick="FB_Checkout();">@if(Auth::user()->package() && Auth::user()->package()->id == $pack->id) {{l("Extend")}} @else {{l("Purchase")}} @endif</a>
                        </div>
                        <!--//BUTTON END-->
                        
                    </div>
                    <!--//PRICE CONTENT END-->
                        
                </div>
                @endif
                
                @endforeach
                <div class="col-md-3">
                </div>
            </div>  
            <!--//BLOCK ROW END-->
            
        </div>
        @endif
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!--PRICE HEADING START-->
                    <div class="price-heading clearfix">
                        <button id="referrals-button" data-toggle="modal" data-target="#referrals" class="btn btn-primary btn-md-2">{{l('Get a Free Subscription!')}}</button>
                    </div>
                </div>    
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!--PRICE HEADING START-->
                    <div class="price-heading clearfix">
                        <h1>{{l("Subscriptions")}}</h1>
                    </div>
                    <!--//PRICE HEADING END-->
                </div>
            </div>
        </div>
        <div class="container-fluid pkg">

            <div class="row">
                @if($allow_unsubscribe && Auth::user()->package() && \App\Pack::where(['id'=>Auth::user()->package()->id, 'name'=>'Custom Package'])->count() && \App\Order::where(['package_id'=>Auth::user()->package()->id, 'user_id'=>Auth::user()->id, 'status'=>'Accepted'])->orderBy('id', 'desc')->whereNotNull('subscription_id')->limit(1)->count()) <div class="col-md-12"><div class="price-heading clearfix"><a href="/payments/unsubscribe?pack_id={{Auth::user()->package()->id}}" onclick="return confirm('{{l("Are you sure you want to unsubscribe?")}}');FB_Checkout();" class="btn btn-primary btn-md-2">{{l("Unsubscribe Custom Package")}}</a></div></div>@
                @elseif($active_custom_pack->value == 'yes')
                    <button id="custom-pack-btn" data-toggle="modal" data-backdrop="false" data-target="#create-custom-package" class="btn btn-primary btn-md-2">{{l('Create a Custom Package')}} 
                    </button>
                @endif

            </div>
            
            <!--BLOCK ROW START-->
            <div class="row">
                <?php $ii = 1;?>
                @foreach($sub_packages as $pack)
                
                <div class="col-md-4">
                
                    <!--PRICE CONTENT START-->
                    <div class="generic_content @if($pack->featured == 1) active @endif clearfix pk1_<?php echo $ii;?>">
                        @php
                        $active_extend_discount = App\Settings::where('id', 11)->firstOrFail();
                        @endphp
                        @if(Auth::user()->getDiscountByPack($pack->id))
                        @php
                        $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
                        $pack->new_price = ((100-$discount_value)/100)*$pack->price;
                        @endphp
                            <div class="ribbon-wrapper-red">
                            <div class="ribbon-red">-{{$discount_value}}%</div>
                          </div>
                        @elseif($active_extend_discount->value == 'yes')

                            @if(Auth::user()->package() && Auth::user()->package()->id == $pack->id)
                                @php
                                $extend_discount = App\Settings::where('id', 12)->firstOrFail();
                                $extend_discount = $extend_discount->value;
                                $pack->new_price = ((100-$extend_discount)/100)*$pack->price;
                                @endphp
                                <div class="ribbon-wrapper-red">
                                    <div class="ribbon-red">-{{$extend_discount}}%</div>
                                </div>
                            @endif

                        @endif
                        
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
                            
                            @if($pack->type == "subscription")
                            <!--PRICE START-->
                            <div class="generic_price_tag clearfix">
                            @if(Auth::user()->getDiscountByPack($pack->id) || $active_extend_discount->value == 'yes' && Auth::user()->package() && Auth::user()->package()->id == $pack->id)
                                <span id="price-discounted" class="price discounted">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else {{number_format($pack->price/$pack->duration, 2, '.', ',')}} @endif</span>
                                    <span class="cent"></span>
                                </span>
                                <span class="price discounted new_price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->new_price/$pack->credits, 3, '.', ',')}} @else @if($pack->duration >= 30) 
                                    {{number_format($pack->new_price/($pack->duration/30), 2, '.', ',')}} 
                                    @else
                                    {{number_format($pack->new_price/$pack->duration, 2, '.', ',')}} 
                                    @endif @endif </span>
                                    <span class="cent">@if($pack->duration >= 30) {{l("/ month")}} @else {{l("/day")}} @endif</span>
                                </span>
                            @else   
                                <span class="price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->front_price != null) {{$pack->front_price}} @else @if($pack->type == 'credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else @if($pack->duration >= 30) 
                                    {{number_format($pack->price/($pack->duration/30), 2, '.', ',')}} 
                                    @else
                                    {{number_format($pack->price/$pack->duration, 2, '.', ',')}} 
                                    @endif @endif @endif</span>
                                    <span class="cent">@if($pack->duration >= 30) {{l("/ month")}} @else {{l("/day")}} @endif</span>

                                </span>
                            @endif
                            </div>
                            <!--//PRICE END-->
                            @endif
                            
                        </div>                            
                        <!--//HEAD PRICE DETAIL END-->
                        
                        <!--FEATURE LIST START-->
                        <div class="generic_feature_list">
                            <ul>
                                {{-- @if($pack->credits != 0)<li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>@endif
                                <li><span>{{l("Chat")}}</span> @if($pack->chat == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Likes")}}</span> @if($pack->likes == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Newsfeed")}}</span> @if($pack->newsfeed == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Images")}}</span> @if($pack->images == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Albums")}}</span> @if($pack->albums == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Friends")}}</span> @if($pack->friends == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                <li><span>{{l("Messages")}}</span> {{l("Unlimited")}}</li> --}}
                                <li><span>{{l("Available")}}</span> {{$pack->duration}} {{l("days")}}</li>
                            </ul>
                        </div>
                        <!--//FEATURE LIST END-->
                        
                        <!--BUTTON START-->
                        <div class="generic_price_btn clearfix">
                            @if($allow_unsubscribe && Auth::user()->package() && Auth::user()->package()->id == $pack->id && \App\Order::where(['package_id'=>Auth::user()->package()->id, 'user_id'=>Auth::user()->id, 'status'=>'Accepted'])->orderBy('id', 'desc')->whereNotNull('subscription_id')->limit(1)->count()) <a href="/payments/unsubscribe?pack_id={{$pack->id}}" onclick="return confirm('{{l("Are you sure you want to unsubscribe?")}}');FB_Checkout();">{{l("Unsubscribe")}}</a>
                            @else 
                            <a href="/payments?pack_id={{$pack->id}}" class="pack" data-pack="{{$pack->id}}" data-price="{{$pack->price}}" data-description="{{$pack->name}}" data-currency="{{$pack->currency}}" data-priceid="{{$pack->stripe_price_id}}" onclick="FB_Checkout();">@if(Auth::user()->package() && Auth::user()->package()->id == $pack->id) {{l("Extend")}} @else {{l("Purchase")}} @endif </a>
                            @endif
                        </div>
                        <!--//BUTTON END-->
                        
                    </div>
                    <!--//PRICE CONTENT END-->
                        
                </div>
                <?php $ii++;?>
                @endforeach
            </div>  
            <!--//BLOCK ROW END-->
            
        </div>
        <div class="container-fluid" style="margin-top: 50px;">
            <div class="row">
                <div class="col-md-12">
                    <!--PRICE HEADING START-->
                    <div class="price-heading clearfix">
                        <h1>{{l("Credits")}}</h1>
                    </div>
                    <!--//PRICE HEADING END-->
                </div>
            </div>
        </div>
        <div class="container-fluid pkg">
            
            <!--BLOCK ROW START-->
            <div class="row">
                <?php $iii = 1;?>
                @foreach($credit_packages as $pack)
                <div class="col-md-3">
                
                    <!--PRICE CONTENT START-->
                    <div class="generic_content @if($pack->featured == 1) active @endif clearfix pk2_<?php echo $iii;?>">
                        @if(Auth::user()->getDiscountByPack($pack->id))
                        @php
                        $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
                        $pack->new_price = ((100-$discount_value)/100)*$pack->price;
                        @endphp
                            <div class="ribbon-wrapper-red">
                            <div class="ribbon-red">-{{$discount_value}}%</div>
                          </div>
                        @endif
                        
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
                                @if(Auth::user()->getDiscountByPack($pack->id))
                                <span id="price-discounted" class="price discounted">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else {{number_format($pack->price/$pack->duration, 2, '.', ',')}} @endif</span>
                                    <span class="cent"></span>
                                </span>
                                <span class="price discounted new_price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->type == 'credits') {{number_format($pack->new_price/$pack->credits, 3, '.', ',')}} @else {{number_format($pack->new_price/$pack->duration, 2, '.', ',')}} @endif </span>
                                    <span class="cent">/ credit</span>
                                </span>
                            @else   
                                <span class="price">
                                    <span class="sign">@switch($pack->currency) @case('EUR') € @break @case('USD') $ @break @default @endswitch</span>
                                    <span class="currency">@if($pack->front_price != null && $pack->front_price!=0) {{$pack->front_price}} @else @if($pack->type == 'credits' || $pack->type == 'subscription-credits') {{number_format($pack->price/$pack->credits, 3, '.', ',')}} @else {{number_format($pack->price/$pack->duration, 2, '.', ',')}} @endif @endif</span>
                                    <span class="cent">/ credit</span>

                                </span>
                            @endif
                            </div>
                            <!--//PRICE END-->
                            
                        </div>                            
                        <!--//HEAD PRICE DETAIL END-->
                        
                        <!--FEATURE LIST START-->
                        <div class="generic_feature_list">
                            <ul>
                                <li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>
                                <li>@if($pack->type == "subscription-credits")<span style="font-size:10px;" class="text-muted">{{l("Available")}} {{$pack->duration}} {{l("days")}}</span>@else &nbsp; @endif</li>
                            </ul>
                        </div>
                        <!--//FEATURE LIST END-->
                        
                        <!--BUTTON START-->
                        <div class="generic_price_btn clearfix">
                            <a href="/payments?pack_id={{$pack->id}}" class="pack" data-pack="{{$pack->id}}" data-price="{{$pack->price}}" data-description="{{$pack->name}}" data-currency="{{$pack->currency}}" {!!$pack->type == 'subscription-credits' ? 'data-priceid="'.$pack->stripe_price_id.'"' : ''!!} onclick="FB_Checkout();">{{l("Purchase")}}</a>
                        </div>
                        <!--//BUTTON END-->
                        
                    </div>
                    <!--//PRICE CONTENT END-->
                        
                </div>
                <?php $iii++;?>
                @endforeach
            </div>  
            <!--//BLOCK ROW END-->
            
        </div>
    </section>
</div>
@endif

        <div class="modal fade" id="stripe-form" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
            <div class="modal-dialog window-popup create-photo-album" role="document">
                <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                        <h6 class="title">{{l("Checkout Confirmation")}}: <span id="payment_price"></span></h6>
                         
                        
                    </div>

                    <div class="modal-body">

                        <style>
                        #payment-form {line-height: 1.2rem; font-family: sans-serif; font-size:14px;}
                        #card-errors {color: orange;}
                        #stripe-form h6{font-size:20px;}
                        #card-field {background: #fff;}
                        #payment-errors{color:orange;width:100%;margin:auto;text-align: center;}
                        label{display: block;}
                        
                        </style>
                         
                         
                        <form id="payment-form" action="{{route('accepted_payment')}}" method="POST">
                            @csrf
                            <input type="hidden" name="pack_id" id="pack_id" value="" />
                            <input type="hidden" name="priceId" id="priceId" value="" />
                            <input type="hidden" name="custom_price_value" id="custom_price_value" value="" />
                            <div>
                                <label>{{l("Firstname")}}</label>
                                <input type="text" name="firstname" value="{{auth()->user()->firstname}}" id="firstname">
                            </div>
                            <div>
                                <label>{{l("Lastname")}}</label>
                                <input type="text" name="lastname" value="{{auth()->user()->lastname}}" id="lastname">
                            </div>
                            <div>
                                <label>{{l("Email")}}</label>
                                <input type="email" name="email" value="{{auth()->user()->email}}" id="email">
                            </div>
                            <div>
                                <label>{{l("Card number")}}</label><div id="card-field"></div>
                                <span id="card-errors"></span>
                            </div>
                            <div>
                                <div id="payment-errors">&nbsp;</div>
                                <input type="submit" class="btn btn-primary btn-lg" id="stripe_submit_button" value="{{l("Purchase")}}">
                            </div>
                        </form>
                    
                        
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="modal fade" id="order-conf" tabindex="-1" role="dialog" aria-labelledby="create-photo-album" aria-hidden="true">
            <div class="modal-dialog window-popup create-photo-album" role="document">
                <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                        @if(isset($_GET['payment']) && $_GET['payment'] == 'accepted')
                            <h6 class="title">{{l("Thank You for your purchase")}}</h6>
                        @else
                            <h6 class="title">{{l("Something went wrong!")}}</h6>
                        @endif
                    </div>

                    <div class="modal-body">

                        <div class="order-conf">
                            @if(isset($_GET['payment']) && $_GET['payment'] == 'accepted')
                                <div class="order-accepted"><span><i class="fas fa-check"></i></span></div>
                                <div class="order-message">
                                    @php
                                        $order = App\Order::where('id', $_GET['order'])->firstOrFail(); 
                                    @endphp
                                    
                                    <span>{{l("You have purchased a")}} {{$order->package()->name}} {{l("package!")}}</span>

                                </div>
                            @else
                                <div class="order-accepted"><span><i class="fas fa-times"></i></span></div>
                                <div class="order-message">
                                    
                                    <span>{{l("Your order did not go through. Please try again later.")}}</span>

                                </div>
                            @endif
                        </div>
                    @if(isset($_GET['payment']) && $_GET['payment'] == 'accepted')    
                        <a href="/newsfeed" class="btn btn-secondary btn-lg btn--half-width">{{l("Newsfeed")}}</a>
                        <a href="/find-friends" class="btn btn-primary btn-lg btn--half-width">{{l("Find Friends")}}</a>
                    @else
                        <a href="#" data-dismiss="modal" class="btn btn-secondary btn-lg btn--half-width">{{l("Cancel")}}</a>
                        <a href="#" data-dismiss="modal" class="btn btn-primary btn-lg btn--half-width">{{l("Packages")}}</a>
                    @endif
                        
                </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="create-custom-package" tabindex="-1" role="dialog" aria-labelledby="create-custom-package" aria-hidden="true">
            <div class="modal-dialog window-popup create-photo-album" role="document">
                <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                            <h6 class="title">{{l("Create your Custom Package")}}</h6>
                    </div>

                    <div class="modal-body">

                        <div class="custom-pack">
                            <div class="generic_price_table ">
                            <div class="generic_content active clearfix pk2_2">                                                        
                                <!--HEAD PRICE DETAIL START-->
                                <div class="generic_head_price clearfix">
                                
                                    <!--HEAD CONTENT START-->
                                    <div class="generic_head_content clearfix">
                                    
                                        <!--HEAD START-->
                                        <div class="head_bg"></div>
                                        <div class="head">
                                            <span>{{l("Custom Package")}}</span>
                                        </div>
                                        <!--//HEAD END-->
                                        
                                    </div>
                                    <!--//HEAD CONTENT END-->
                                    
                                    <!--PRICE START-->
                                    <div class="generic_price_tag clearfix">
                                        
                                        <span class="price">
                                            <form id="custom_price_from" method="GET" action="/payments">
                                                <span id="custom_price"><input type="number" min="3" step="1" name="value" placeholder="{{l('Enter how much you want to spend')}}"></span>
                                                <input type="hidden" name="pack_id" value="custom">
                                            </form>
                                        </span>
                                    </div>
                                    <!--//PRICE END-->
                                    
                                </div>                            
                                <!--//HEAD PRICE DETAIL END-->
                                
                                <!--FEATURE LIST START-->
                                <div class="generic_feature_list">
                                    <ul>
                                        {{-- <li><span>{{l("Credits")}}</span> <span id="custom_show_credits">0</span></li>
                                        <li><span>{{l("Likes")}}</span> <span id="custom_show_likes">{{l("No")}}</span></li>
                                        <li><span>{{l("Newsfeed")}}</span> <span id="custom_show_newsfeed">{{l("No")}}</span></li>
                                        <li><span>{{l("Images")}}</span> <span id="custom_show_images">{{l("No")}}</span></li>
                                        <li><span>{{l("Albums")}}</span> <span id="custom_show_albums">{{l("No")}}</span></li>
                                        <li><span>{{l("Friends")}}</span> <span id="custom_show_friends">{{l("No")}}</span></li>
                                        <li><span>{{l("Messages")}}</span> {{l("Unlimited")}}</li> --}}
                                        <li><span>{{l("Available")}}</span> <span id="custom_show_days">0</span> {{l("days")}}</li>
                                    </ul>
                                </div>
                                <!--//FEATURE LIST END-->
                                
                            </div>
                        </div>
                        </div>
                        <a href="#" data-dismiss="modal" class="btn btn-secondary btn-lg btn--half-width">{{l("Cancel")}}</a>
                        <a id="pay_custom_pack" href="/payments?pack_id=custom" class="btn btn-primary btn-lg btn--half-width">{{l("Purchase")}}</a>                       
                </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="referrals" tabindex="-1" role="dialog" aria-labelledby="referrals" aria-hidden="true">
            <div class="modal-dialog window-popup create-photo-album" role="document">
                <div class="modal-content">
                    <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                        <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
                    </a>

                    <div class="modal-header">
                            <h6 class="title">{{l("Invite a friend!")}}</h6>
                    </div>

                    <div class="modal-body">
                        <div class="ui-block-content">

                            
                            <!-- Personal Information Form  -->
                            
                            <form method="post" action="{{ route('referrals') }}">
                                @csrf
                                <div class="row">
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                    <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">{{l("Copy this link and send it to your friends")}}</label>
                                            <input name="firstname" class="form-control" placeholder="" type="text" value="{{App::make('url')->to('/referrals')}}/{{Auth::user()->username}}/{{md5(Auth::id().Auth::user()->email)}}">
                                        </div>                            
                                    </div>
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                    <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="or"></div>
                                    </div>
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                    <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">{{l("Enter your friends emails to send them an invite")}}</label>
                                            <input name="email" class="form-control" placeholder="" type="text" value="">
                                        </div>                  
                        
                                    </div>
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>    
                                    <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                                        <button type="submit" class="btn btn-primary btn-lg full-width">{{l("Invite")}}</button>
                                    </div>
                                    <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                    </div>
                                </div>
                            
                            </form>
                            
                            <!-- ... end Personal Information Form  -->
                        </div>
                        <div class="referral_stats">
                            <div class="row">
                                <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                </div>
                                <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                                    @if(isset($_GET['referrals']) && $_GET['referrals'] == 'no')
                                        <span class="text-danger">{{l("This user is already invited or is already registered!")}}</span>
                                    @endif
                                    @if(isset($_GET['referrals']) && $_GET['referrals'] == 'yes')
                                        <span class="text-success">{{l("User invited successfully!")}}</span>
                                    @endif
                                    <div class="row">
                                        @php
                                            $all_referrals = App\Referral::where('user', Auth::user()->email)->get();
                                            $registered_referrals = $all_referrals->where('status', 1)->count();
                                        @endphp
                                        <div class="col-md-6 col-sm-12 col-12">
                                            <p>{{l("Invited friends")}}: <span id="referral_invited">{{$all_referrals->count()}}</span></p>
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-12">
                                            <p>{{l("Registered friends")}}: <span id="referral_registered">{{$registered_referrals}}</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col col-lg-3 col-md-3 col-sm-12 col-12">
                                </div>    
                            </div>
                        </div>
                        <div class="generic_price_table ">
                            <div class="row">
                                @php
                                $x = 1;
                                @endphp
                                @foreach($sub_packages as $pack)
                    
                                    <div class="col-md-4">
                                    
                                        <!--PRICE CONTENT START-->
                                        <div class="generic_content active clearfix">
                                            <div class="ribbon-wrapper-red">
                                                <div class="ribbon-red">{{l("FREE")}}</div>
                                            </div>
                                            
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
                                                @php
                                                $req_claim = 5*$x;
                                                $has_to_claim = $all_referrals->where('status', 1)->where('claimed', 0)->count();
                                                @endphp
                                                <div class="generic_price_tag clearfix"> 
                                                    <span class="price">
                                                        @if($has_to_claim >= $req_claim)
                                                        <span id="referrals-price">{{l("Ready to collect!")}}</span>
                                                        @else
                                                        <span id="referrals-price">Invite {{$req_claim - $has_to_claim}} more friends</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <!--//PRICE END-->
                                                
                                            </div>                            
                                            <!--//HEAD PRICE DETAIL END-->
                                            
                                            <!--FEATURE LIST START-->
                                            <div class="generic_feature_list">
                                                <ul>
                                                    {{-- @if($pack->credits != 0)<li><span>{{number_format($pack->credits, 0, '.', ',')}}</span> {{l("Credits")}}</li>@endif
                                                    <li><span>{{l("Chat")}}</span> @if($pack->chat == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Likes")}}</span> @if($pack->likes == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Newsfeed")}}</span> @if($pack->newsfeed == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Images")}}</span> @if($pack->images == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Albums")}}</span> @if($pack->albums == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Friends")}}</span> @if($pack->friends == 'true') {{l("Yes")}} @else {{l("No")}} @endif</li>
                                                    <li><span>{{l("Messages")}}</span> {{l("Unlimited")}}</li> --}}
                                                    <li><span>{{l("Available")}}</span> {{$pack->duration}} {{l("days")}}</li>
                                                </ul>
                                            </div>
                                            <!--//FEATURE LIST END-->
                                            @if($has_to_claim >= $req_claim)
                                            <div class="generic_price_btn clearfix">
                                                <a href="/referrals/claim/{{$pack->id}}">{{l("Claim")}}</a>
                                            </div>
                                            @endif
                                            
                                        </div>
                                        <!--//PRICE CONTENT END-->
                                            
                                    </div>

                                    @php
                                    $x++;
                                    @endphp
                                    
                                    @endforeach

                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
        </div>

        </main>
@endauth
@endsection

@section('scripts')
    @if($active_custom_pack->value == 'yes')
    @php
    $credit_per_price = App\Settings::where('id', 14)->firstOrFail();
    $credit_per_price = $credit_per_price->value;
    $day_per_price = App\Settings::where('id', 15)->firstOrFail();
    $day_per_price = $day_per_price->value;
    $min_for_like = App\Settings::where('id', 16)->firstOrFail();
    $min_for_like = $min_for_like->value;
    $min_for_newsfeed = App\Settings::where('id', 17)->firstOrFail();
    $min_for_newsfeed = $min_for_newsfeed->value;
    $min_for_images = App\Settings::where('id', 18)->firstOrFail();
    $min_for_images = $min_for_images->value;
    $min_for_albums = App\Settings::where('id', 19)->firstOrFail();
    $min_for_albums = $min_for_albums->value;
    $min_for_friends = App\Settings::where('id', 20)->firstOrFail();
    $min_for_friends = $min_for_friends->value;
    @endphp
        <script type="text/javascript">
            var credit_per_price = JSON.parse("{{ json_encode($credit_per_price) }}".replace(/&quot;/g,'"'));
            var day_per_price = JSON.parse("{{ json_encode($day_per_price) }}".replace(/&quot;/g,'"'));
            var min_for_like = JSON.parse("{{ json_encode($min_for_like) }}".replace(/&quot;/g,'"'));
            var min_for_newsfeed = JSON.parse("{{ json_encode($min_for_newsfeed) }}".replace(/&quot;/g,'"'));
            var min_for_images = JSON.parse("{{ json_encode($min_for_images) }}".replace(/&quot;/g,'"'));
            var min_for_albums = JSON.parse("{{ json_encode($min_for_albums) }}".replace(/&quot;/g,'"'));
            var min_for_friends = JSON.parse("{{ json_encode($min_for_friends) }}".replace(/&quot;/g,'"'));
            var text_yes = '{{l("Yes")}}';
            var text_no = '{{l("No")}}';
            $( "#custom_price input" ).change(function() {
                $("#custom_price_value").val($( this ).val());
                $("#custom_show_credits").html(credit_per_price*($( this ).val()));
                $("#custom_show_days").html(day_per_price*($( this ).val()));
                if($( this ).val() < parseInt(min_for_like)){
                    $("#custom_show_likes").html(text_no);
                }else{
                    $("#custom_show_likes").html(text_yes);
                }
                if($( this ).val() < parseInt(min_for_newsfeed)){
                    $("#custom_show_newsfeed").html(text_no);
                }else{
                    $("#custom_show_newsfeed").html(text_yes);
                }
                if($( this ).val() < parseInt(min_for_images)){
                    $("#custom_show_images").html(text_no);
                }else{
                    $("#custom_show_images").html(text_yes);
                }
                if($( this ).val() < parseInt(min_for_albums)){
                    $("#custom_show_albums").html(text_no);
                }else{
                    $("#custom_show_albums").html(text_yes);
                }
                if($( this ).val() < parseInt(min_for_friends)){
                    $("#custom_show_friends").html(text_no);
                }else{
                    $("#custom_show_friends").html(text_yes);
                }
                $('#pay_custom_pack').attr('href', '/payments?pack_id=custom&value='+$( this ).val());
            });
        </script>
    @endif
    @isset($_GET['payment'])
        <script type="text/javascript">
            $("#order-conf").modal();
        </script>
    @endif
    @isset($_GET['referrals'])
        <script type="text/javascript">
            $("#referrals").modal();
        </script>
    @endif
    
    @isset($_GET['stripe-form'])
        <script type="text/javascript">
            // $("#stripe-form").modal();
        </script>
    @endif
    
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let custom_price = 0;
        const config = {
            publishable_key: "{{$stripe_public_key}}",
        };
         
        let stripe = Stripe(config.publishable_key);
        let elements = stripe.elements();
        var style = {
            base: {
                fontSize: '16px'
            }
        };
        let card = elements.create("card",{hidePostalCode: true, style: style});
         
        card.mount("#card-field");
         
        let form = document.getElementById('payment-form');
        let errors = document.getElementById('card-errors');
        
        $(document).ready(function() {
            $( "#pay_custom_pack" ).click(function() {
                /*card.clear();*/
                fetch("{{route('create-custom-pack')}}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                      payment_price: $("#custom_price_value").val(),
                    })
                }).then(function(confirmResult) {
                    return confirmResult.json();
                }).then(function(data) {
                    if (data.error) {
                        alert(data.error.message);
                        return;
                    }
                    $("#create-custom-package").hide();
                    $('#pack_id').val(data.pack_id);
                    $('#priceId').val(data.price_id);
                    $("#stripe-form").modal();
                    $("#payment_price").text($("#custom_price_value").val()+" EUR");
                    
                    /* save order attempt*/
                    var fd = new FormData();
                    fd.append('pack_id', data.pack_id);
                    fd.append('price', $("#custom_price_value").val());
                    fd.append('currency', "EUR");

                    $.ajax({
                        type: 'POST',
                        url: '{{route('packages.order.attemts.store')}}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: "json",
                        data: fd,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(data, textStatus){
                        if(textStatus=="success") {
                            console.log(data);
                        }
                    })
                    .fail(function(data, textStatus, errorThrown){
                        
                    });
                });
                        
                
                return false;
            });
                
            $( ".pack" ).click(function() {
                /* save order attempt*/
                var fd = new FormData();
                fd.append('pack_id', $(this).data("pack"));
                fd.append('price', $(this).data("price"));
                fd.append('currency', $(this).data("currency"));

                $.ajax({
                    type: 'POST',
                    url: '{{route('packages.order.attemts.store')}}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    data: fd,
                    processData: false,
                    contentType: false,
                })
                .done(function(data, textStatus){
                    if(textStatus=="success") {
                        console.log(data);
                    }
                })
                .fail(function(data, textStatus, errorThrown){
                    
                });
                
                userActivityLog('Initialize payment', 'Package name: ' + JSON.stringify(this.dataset.description));
                
                /*card.clear();*/
                $("#stripe-form").modal();
                $("#payment_price").text($(this).data("price")+" "+$(this).data("currency"));
                $('#pack_id').val( $(this).data("pack"));
                $('#priceId').val( $(this).data("priceid"));

                if($(this).data("priceid")!=undefined)
                    $('#stripe_submit_button').attr('value', '{{l("Purchase")}}');
                else
                    $('#stripe_submit_button').attr('value', '{{l("Purchase")}}');
                return false;
            });
        });
     
        form.addEventListener('submit', function(evt){
            evt.preventDefault();
            stripe.createPaymentMethod('card', card).then(function(result) {
                const customerId = "{{session()->get('stripeCustomerID')}}";
                
                let billingName = document.querySelector('#firstname').value+" "+document.querySelector('#lastname').value;

                let priceId = document.getElementById('priceId').value;

                if(priceId!='') {
                    stripe
                        .createPaymentMethod({
                            type: 'card',
                            card: card,
                            billing_details: {
                                name: billingName,
                            },
                        })
                        .then((result) => {
                            if (result.error) {
                                displayError(result);
                            } else {
                                createSubscription({
                                    customerId: customerId,
                                    paymentMethodId: result.paymentMethod.id,
                                    priceId: priceId,
                                    packID: document.getElementById('pack_id').value,
                                    email: document.getElementById('email').value,
                                    firstname: document.getElementById('firstname').value,
                                    lastname: document.getElementById('lastname').value,
                                });
                            }
                        });
                  } else {
                        if (result.error) {
                            errors.textContent = result.error.message;
                            return;
                        }
                        errors.textContent = "";
                 
                        fetch('{{route('new_payment')}}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({
                                payment_method_id: result.paymentMethod.id,
                                email: document.getElementById('email').value,
                                firstname: document.getElementById('firstname').value,
                                lastname: document.getElementById('lastname').value,
                                pack_id: document.getElementById('pack_id').value,
                                payment_price: document.getElementById('custom_price_value').value,
                            })
                        })
                        .then(function(responseBody) {
                            return responseBody.json()
                        })
                        .then(handleServerResponse);
                }
            });
        });
    
    function createSubscription({ customerId, paymentMethodId, priceId, packID, email, firstname, lastname }) {
        return (
            fetch("{{route('create-subscription')}}", {
                method: 'post',
                headers: {
                    'Content-type': 'application/json',
                },
                body: JSON.stringify({
                    customerId: customerId,
                    paymentMethodId: paymentMethodId,
                    priceId: priceId,
                    packID: packID,
                    email: email,
                    firstname: firstname,
                    lastname: lastname
                }),
            })
            .then((response) => {
                return response.json();
            })
            /* If the card is declined, display an error to the user.*/
            .then((result) => {
                if (result.error) {
                    /* The card had an error when trying to attach it to a customer.*/
                    throw result;
                }
                return result;
            })
            /*Normalize the result to contain the object returned by Stripe.
              Add the additional details we need.*/
            .then((result) => {
                return {
                    paymentMethodId: paymentMethodId,
                    priceId: priceId,
                    subscription: result,
                    packID: packID,
                };
            })
            /*Some payment methods require a customer to be on session
              to complete the payment process. Check the status of the
              payment intent to handle these actions.*/
            .then(handlePaymentThatRequiresCustomerAction)
            /*If attaching this card to a Customer object succeeds,
              but attempts to charge the customer fail, you
              get a requires_payment_method error.*/
            .then(handleRequiresPaymentMethod)
            /*No more actions required. Provision your service for the user.*/
            .then(onSubscriptionComplete)
            .catch((error) => {
                /*An error has happened. Display the failure to the user here.
                We utilize the HTML element we created.*/
                showCardError(error);
            })
        );
    }
    
    function showCardError(err) {
        displayError(err);
    }
    function handlePaymentThatRequiresCustomerAction({
        subscription,
        invoice,
        priceId,
        paymentMethodId,
        isRetry,
    }) {
        if (subscription && subscription.status === 'active') {
            /*Subscription is active, no customer actions required.*/
            return { subscription, priceId, paymentMethodId };
        }

        /*If it's a first payment attempt, the payment intent is on the subscription latest invoice.
        If it's a retry, the payment intent will be on the invoice itself.*/
        let paymentIntent = invoice ? invoice.payment_intent : subscription.latest_invoice.payment_intent;

        if (
            paymentIntent.status === 'requires_action' ||
            (isRetry === true && paymentIntent.status === 'requires_payment_method')
        ) {
            return stripe
                .confirmCardPayment(paymentIntent.client_secret, {
                    payment_method: paymentMethodId,
                })
                .then((result) => {
                    if (result.error) {
                        /*Start code flow to handle updating the payment details.
                        Display error message in your UI.
                        The card was declined (i.e. insufficient funds, card has expired, etc).*/
                        document.getElementById("payment-errors").textContent = result.error.message;
                    } else {
                        if (result.paymentIntent.status === 'succeeded') {
                            /*Show a success message to your customer.*/
                            /* subscription = Stripe::Subscription.retrieve(subscription.id);*/
                
                            fetch("{{route('finalize-subscription-payment')}}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                body: JSON.stringify({
                                    priceId: priceId,
                                    subscriptionId: subscription.id,
                                    invoice: invoice,
                                    paymentMethodId: paymentMethodId,
                                })
                            }).then(function(confirmResult) {
                                return confirmResult.json();
                            }).then(handleServerResponse);
                            window.location.replace("{{route('profile')}}");
                        }
                    }
                })
                .catch((error) => {
                    displayError(error);
                });
          } else {
            /*No customer action needed.*/
            return { subscription, priceId, paymentMethodId };
          }
    }
    
    
    function handleRequiresPaymentMethod({
        subscription,
        paymentMethodId,
        priceId,
    }) {
        if (subscription.status === 'active') {
            /*subscription is active, no customer actions required.*/
            return { subscription, priceId, paymentMethodId };
        } else if (
            subscription.latest_invoice.payment_intent.status ===
            'requires_payment_method'
        ) {
            /*Using localStorage to manage the state of the retry here,
            feel free to replace with what you prefer.
            Store the latest invoice ID and status.*/
            localStorage.setItem('latestInvoiceId', subscription.latest_invoice.id);
            localStorage.setItem(
                'latestInvoicePaymentIntentStatus',
                subscription.latest_invoice.payment_intent.status
            );
            throw { error: { message: 'Your card was declined.' } };
        } else {
            return { subscription, priceId, paymentMethodId };
        }
    }
    
    function onSubscriptionComplete(result) {
        /*Payment was successful.*/
        if (result.subscription.status === 'active') {
            /*Change your UI to show a success message to your customer.
            Call your backend to grant access to your service based on
            `result.subscription.items.data[0].price.product` the customer subscribed to.*/
            fetch("{{route('finalize-subscription-payment')}}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    priceId: result.subscription.items.data[0].price.id,
                    subscriptionId: result.id,
                    paymentMethodId: result.subscription.latest_invoice.payment_intent.charges.data[0].payment_method,
                })
            }).then(function(confirmResult) {
                return confirmResult.json();
            }).then(handleServerResponse);
            window.location.replace("{{route('profile')}}");
        }
    }
    
    card.on('change', function (event) {
        displayError(event);
    });
    function displayError(event) {
        /* changeLoadingStatePrices(false);*/
        let displayError = document.getElementById("payment-errors");
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    }
     
    function handleServerResponse(response) {
        if (response.error) {
            document.getElementById("payment-errors").textContent = response.error.message;
        } else if (response.requires_action) {
            document.getElementById("payment-errors").textContent = "Requires action";
            handleAction(response);
        } else {
            document.getElementById("payment-errors").textContent = "Success!";
            document.getElementById("payment-form").submit();
        }
    }
     
    function handleAction(response) {
        stripe.handleCardAction(
            response.payment_intent_client_secret
        ).then(function(result) {
            if (result.error) {
                document.getElementById("payment-errors").textContent = result.error.message;
            } else {
                fetch("{{route('confirm_payment')}}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        payment_intent_id: result.paymentIntent.id,
                    })
                }).then(function(confirmResult) {
                    return confirmResult.json();
                }).then(handleServerResponse);
            }
        });
    }
    </script>
@endsection
