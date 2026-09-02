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
