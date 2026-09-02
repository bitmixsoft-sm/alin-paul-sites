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

        var payment_method = '';
        var pack_id;
        $( ".divs" ).click(function() {
            payment_method = $(this).data("payment_method");
           
        });
        $( "#btn_select_payment_method" ).click(function() {
            $("a[data-pack='"+pack_id+"']").trigger("click");
            return false;
        });
        
        $( ".pack" ).click(function() {
            if ($(this).data("discount") !== 100) {
                $("#payment-selector").modal();
                if(payment_method=='') {
                    /* save order attempt*/
                    var fd = new FormData();
                    fd.append('pack_id', $(this).data("pack"));
                    fd.append('price', $(this).data("price"));
                    fd.append('currency', $(this).data("currency"));
                    fd.append('payment_method', "STRIPE");
    
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
                }
                
                pack_id=$(this).data("pack");
                if("{{count($active_payments)}}"==1) {
                    payment_method="{{key($active_payments)}}";
                } else if(payment_method=='') {
                    return false;
                }
                
                if(payment_method!='STRIPE_ACTIVE') {
                    window.location.replace("{!!route('new_payment', ['pack_id'=>"__pack_id__", "payment_method"=>"__payment_method__"])!!}".replace('__pack_id__', pack_id).replace('__payment_method__', payment_method));
                    return false;
                }
                payment_method = '';
                $("#payment-selector").modal('hide');
    
    
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
            }
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
