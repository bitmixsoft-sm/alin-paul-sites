<?php
namespace App\Services;

use Exception;
use Illuminate\Http\Request;

class StripePaymentService
{
    protected $config;
    protected $stripe;
    
    public function __construct() {
        $this->config = ['STRIPE_SECRET' => optional(\App\Settings::where('name', 'STRIPE_SECRET')->first())->value, 'STRIPE_KEY' => optional(\App\Settings::where('name', 'STRIPE_KEY')->first())->value];
        $this->accepted_payment_url = route('accepted_payment');
        $this->declined_payment_url = route('declined_payment');
        $this->init($this->config);
        return $this->stripe;
    }
    
    
    public function init() {
        \Stripe\Stripe::setApiKey($this->config['STRIPE_SECRET']);
        $this->stripe = new \Stripe\StripeClient($this->config['STRIPE_SECRET']);
    }
    
    public function get_settings() {
        return $this->config;
    }
    
    public function getPlans() {
        $plans = [];
        foreach ($this->stripe->plans->all(['limit'=>3])->autoPagingIterator() as  $plan) {
            $plans[] = $plan;
        }
        return $plans;
    }
    
    public function getCharges() {
        $plans = [];
        foreach ($this->stripe->charges->all(['limit'=>3])->autoPagingIterator() as  $plan) {
            $plans[] = $plan;
        }
        return $plans;
    }
    
    public function getCustomerSubscriptions($customerID) {
        return $this->stripe->customers->retrieve($customerID, [ 'expand' => ['subscriptions'] ]);
    }
    public function getCustomer($customerID) {
        try {
            return $this->stripe->customers->retrieve($customerID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    public function customerUpdate($customerID, $data) {
        $this->stripe->customers->update($customerID, $data);
    }
    
    public function getCustomers() {
        $customers = [];
        foreach ($this->stripe->customers->all(['limit'=>3])->autoPagingIterator() as  $customer) {
            $customers[] = $customer;
        }
        return $customers;
    }
    
    public function getSubscriptions($status = 'all') {
        $filteredsubscriptions = [];
        $subscriptions = [];
        foreach ($this->stripe->subscriptions->all(['limit'=>3])->autoPagingIterator() as  $subscription) {
            $subscriptions[] = $subscription;
        }
        
        return $subscriptions;
    }
    
    public function cancelSubscription($subscriptionID) {
        try {
            return $this->stripe->subscriptions->cancel($subscriptionID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    
    public function getPaymentMethod($paymentMethodID) {
        try {
            return $this->stripe->paymentMethods->retrieve($paymentMethodID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    public function getProduct($productID) {
        try {
            return $this->stripe->products->retrieve($productID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    public function getprice($priceID) {
        try {
            return $this->stripe->prices->retrieve($priceID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    public function getSubscription($subscriptionID) {
        // try {
            return $this->stripe->subscriptions->retrieve($subscriptionID, []);
        // } catch (\Stripe\Exception\InvalidRequestException $e) {
            // return NULL;
        // }
    }
    
    public function getPaymentIntent($paymentIntentID) {
        try {
            return $this->stripe->paymentIntents->retrieve($paymentIntentID, []);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return NULL;
        }
    }
    
    public function preparePaymentRedirection($amount, $currency, $description, $userInfo) {
        try {
            header('Content-Type: application/json');

            $YOUR_DOMAIN = env('APP_URL');

            $checkout_session = $this->stripe->checkout->sessions->create([
                'line_items' => [[
                    'price_data' => [
                        'unit_amount' => $amount*100,
                        'currency' => $currency,
                        'product_data' => ['name'=>$description],
                    ],
                    'quantity' => 1,
                ]],
                'payment_method_types' => [
                    'card',
                ],
                "metadata" => [
                    "order_id" => $userInfo->id
                ],
                'mode' => 'payment',
                'success_url' => $this->accepted_payment_url."?scode={CHECKOUT_SESSION_ID}",
                'cancel_url' => $this->declined_payment_url,
            ]);
            
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit();
        } catch(Exception $e) {
            \Log::error($e->getMessage());
        }
        return false;
    }
    
    
    public function log() {
        $post = \Request::post();
        $invoice_log = "";
        $logs_vars=['post'=>$_POST,'get'=> $_GET, 'session'=>$_SESSION];
        foreach ($logs_vars as $lk => $log_vars)
             if (is_array($log_vars))
                foreach ($log_vars as $k=>$v)
                     $invoice_log .= "Returned ${lk}: $k=".$v."\n";
        
        
        $infoLog = new \Monolog\Logger('payment_log');
        $infoLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')), \Monolog\Logger::INFO);
        $infoLog->info('', [$invoice_log]);
    }

    public function createCustomer($email, $userId, $firstName, $lastName) {
        $customer = \Stripe\Customer::create(
            [
                'email' => $email,
                'metadata' => [
                    'user_id' => $userId,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                ],
            ]
        );
        if ($customer === null)
            die(json_encode(["error" => ["message" => "Error creating or updating customer",],]));
        return $customer;
    }
    
    
    public function createPrice($packName, $packDuration, $packPrice, $packCurrency) {
        // $price_id = "price-".$packID."_".preg_replace("/[^a-zA-Z0-9_\-]/", "_",  request()->server->get('SERVER_NAME'));
        try {            
            $price = $this->stripe->prices->retrieve(
              $price_id,
              []
            );
        } catch (\Throwable $e) {
            $price = $this->stripe->prices->create(array(
              "nickname" => $packName." - ".($packPrice*100)." ".$packCurrency." (".request()->server->get('SERVER_NAME').")",
              "unit_amount" => $packPrice*100,
              "currency" => $packCurrency,
              "recurring" =>array(
                    "interval" => 'day',
                    "interval_count" => $packDuration
                    ),
              'product_data' => array(
                    "name" => $packName." (".request()->server->get('SERVER_NAME').")",
                  ),
            ));
        }
        return $price;
    }
    
    public function createPlan($packID, $packName, $packDuration, $packPrice, $packCurrency) {
        $plan_id = "plan-".$packID."_".preg_replace("/[^a-zA-Z0-9_\-]/", "_",  request()->server->get('SERVER_NAME'));
        try {            
            $plan = $this->stripe->plans->retrieve(
              $plan_id,
              []
            );
        } catch (\Throwable $e) {
            $plan = $this->stripe->plans->create(array(
              "id" => $plan_id,
              "product" => array(
                    "name" => $packName." (".request()->server->get('SERVER_NAME').")",
                  ),
              "interval" => 'day',
              "interval_count" => $packDuration,
              "currency" => $packCurrency,
              "amount" => $packPrice*100,
            ));
        }
        return $plan;
    }
    
    public function createSubscription($customer, $plan) {
        $subscription = \Stripe\Subscription::create(array(
            "customer" => $customer->id,
            "items" => array(
                array(
                    "plan" => $plan->id,
                ),
            ),
            'expand' => ['latest_invoice.payment_intent'],
        ));
        return $subscription;
    }
    
    public function createPriceSubscription($customerID, $priceID) {
        $subscription = $this->stripe->subscriptions->create(array(
            'customer' => $customerID,
            'items' => [
              [
                'price' => $priceID,
              ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ));
        return $subscription;
    }
    
    
    public function createPaymentIntent($paymentMethodID, $price, $currency, $description, $packID, $orderID, $customer) {
 /*       // try {
            $payment_method = $this->getPaymentMethod(
              $body->paymentMethodId
            );
            $payment_method->attach([
              'customer' => $body->customerId,
            ]);
          // } catch (Exception $e) {
          //   return $response->withJson($e->jsonBody);
          // }


          // Set the default payment method on the customer
          $this->stripe->customers->update($body->customerId, [
            'invoice_settings' => [
              'default_payment_method' => $body->paymentMethodId
            ]
          ]);

          // Create the subscription
          $subscription = $this->stripe->subscriptions->create([
            'customer' => $body->customerId,
            'items' => [
              [
                'price' => 'price_HGd7M3DV3IMXkC',
              ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
          ]);

          return $response->withJson($subscription);*/
        
        
        $intent = \Stripe\PaymentIntent::create([
            'payment_method' => $paymentMethodID,
            'amount' => ($price * 100),
            'currency' => $currency,
            'customer' => $customer,
            'metadata' => [
                "product_name" => $description,
                "orderID" => $orderID,
                "packID" => $packID,
            ],
            'confirmation_method' => 'manual',
            'confirm' => true,
        ]);

        if ($intent->status == 'requires_action' &&
            $intent->next_action->type == 'use_stripe_sdk') {
            # Tell the client to handle the action
            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        } else if ($intent->status == 'succeeded') {
            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            echo json_encode([
                'success' => true
            ]);
        } else {
            # Invalid status
            http_response_code(500);
            echo json_encode(['error' => 'Invalid PaymentIntent status']);
        }
        return $intent;
    }
    
    
    public function createPaymentIntentSubscription($subscription) {
        $intent = $this->getPaymentIntent($subscription->latest_invoice->payment_intent->id);
        $subscription = $this->getSubscription($subscription->id);
        if ($intent->status == 'requires_action' && $subscription->status == 'incomplete' &&
                $intent->next_action->type == 'use_stripe_sdk' ) {
            # Tell the client to handle the action

            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        } else if ($intent->status == 'succeeded' && $subscription->status == 'active') {

            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            echo json_encode([
                'success' => true
            ]);
        } else if ($intent->status == 'requires_payment_method' && $subscription->status == 'incomplete') {
            echo "Subscription failed";

        } else {
            # Invalid status
            http_response_code(500);
            echo json_encode(['error' => 'Invalid PaymentIntent status']);
        }
    }
    
    public function paymentReturn($stripeToken, $stripeEmail, $pack) {
        if ($pack->type=='subscription') {

            if($stripeToken=='')
                return false;
            $plan_id = "plan-".$pack->id."_".preg_replace("/[^a-zA-Z0-9_\-]/", "_",  request()->server->get('SERVER_NAME'));
            try {            
                $plan_exists = $stripe->plans->retrieve(
                  $plan_id,
                  []
                );
            } catch (\Throwable $e) {
                // echo "create plan";
                $plan = $this->stripe->plans->reate(array(
                  "id" => $plan_id,
                  "product" => array(
                        "name" => $pack->name." (".request()->server->get('SERVER_NAME').")",
                      ),
                  "interval" => 'day',
                  "interval_count" => $pack->duration,
                  "currency" => $pack->currency,
                  "amount" => $pack->price*100,
                ));
            }
            
            
            try {
            
            $customer = \Stripe\Customer::create(array(
              "email" => $stripeEmail,
              "source" => $stripeToken, // The token submitted from Checkout
            ));
            
            $subscription = \Stripe\Subscription::create(array(
              "customer" => $customer->id,
              "items" => array(
                array(
                  "plan" => $plan_id,
                ),
              ),
            ));
            
            if($last_subscription) { //remove last subsciption
                try {
                    $stripe->subscriptions->cancel(
                            $last_subscription->subscription_id,
                        []
                    );
                } catch (\Throwable $e) { // For PHP 7
                  // echo $e->getMessage();
                }
                $last_subscription->subscription_id = NULL;
                $last_subscription->save();
            }
         
                
              $success = "Thanks! You've subscribed to the " . $pack->name .  " plan.";
            }
            catch(\Stripe\Error\Card $e) {
              // Since it's a decline, \Stripe\Error\Card will be caught
              $body = $e->getJsonBody();
              $error  = $body['error']['message'];
            } 
            // Probably want to log all of these for later or send yourself a notification
            catch (\Stripe\Error\RateLimit $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            } catch (\Stripe\Error\InvalidRequest $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            } catch (\Stripe\Error\Authentication $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            } catch (\Stripe\Error\ApiConnection $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            } catch (\Stripe\Error\Base $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            } catch (Exception $e) {
              $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            }
        } else { //credits
             $success = "Thanks!";
            try{ 
                  $charge_response = $this->stripe->charges::create ([
                      "amount" => $pack->price * 100,
                      "currency" => $pack->currency,
                      "source" => $request->stripeToken,
                      "description" => $pack->name 
                  ]);
                  
             } catch (Exception $e) {
                // echo $e->getMessage();
                $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            }
          }
    
    
     }
     
     function stripeButton() {
         /*<form action="/payments?pack_id={{$pack->id}}" method="POST">
<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
    data-key="{{$stripe_public_key}}"
    data-amount="2000"
    data-name="Your APp"
    data-description="Your Product"
    data-image="Link to your logo"
    data-currency="usd">
</script>
</form>                            */
     }
}