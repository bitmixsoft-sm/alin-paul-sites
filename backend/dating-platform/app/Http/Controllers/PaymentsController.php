<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Pack;
use App\User;
use App\User_Pack;
use App\Order;
use App\Settings;
use App\Events\RefuseVideochat;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Carbon\Carbon;

use App\Services\CentralpayPaymentService;

class PaymentsController extends Controller
{
public function webhook($provider, $path, Request $request) {
        \Log::info($request->all()); 
        if($provider == 'centralpay' && $path == 'subscription'){
            $this->handleCentralPaySubscriptionWebhook($request);
        }
        if($provider == 'centralpay' && $path == 'transaction'){
            $this->handleCentralPayCreditsWebhook($request);
        }
        return true;
    }
    
    private function handleCentralPaySubscriptionWebhook(Request $request){
        $object = $request->object;
        $orderId = $object['merchantSubscriptionId'];
        $subscriptionId = $object['subscriptionId'];
        $end = Carbon::createFromFormat('Y-m-d', $object['currentPeriodEnd']);
        $order = Order::where('id', $orderId)->orWhere('subscription_id', $subscriptionId)->firstOrFail();
        
        
        $user = User::where('id', $order->user_id)->firstOrFail();
        $pack = Pack::where('id', $order->package_id)->firstOrFail();
        if($order->status == 'Pending' && $object['status'] == 'ACTIVE'){
            $user->credits = $user->credits+$pack->credits;
            $user->save();
            $order->status = 'Accepted';
        }
        
        $order->subscription_id = $subscriptionId;
        $order->save();
        
        
        if($object['status'] == 'ACTIVE' && $pack->type != 'credits'){
            if($user->package() && $user->package()->id == $pack->id){
                $current_pack = User_Pack::where('user_id', $user->id)->firstOrFail();
                $current_pack->expiration_date = $end;
                $current_pack->save();
            }else{
                $del_pack = User_Pack::where('user_id', $user->id);
                $del_pack->delete();
                $add_pack = new User_Pack;
                $add_pack->user_id = $user->id;
                $add_pack->pack_id = $pack->id;
                $add_pack->expiration_date = $end;
                $add_pack->save();
            }
        }
    }
    
    private function handleCentralPayCreditsWebhook(Request $request){
        $object = $request->object;
        $orderId = $object['merchantPaymentRequestId'];
        $order = Order::where('id', $orderId)->firstOrFail();
        if($request->type != 'PAYMENTREQUEST_TRANSACTION_SUCCEEDED'){
            $order->status = 'Declined';
            $order->save();
            
            return;
        }
       
        $user = User::where('id', $order->user_id)->firstOrFail();
        $pack = Pack::where('id', $order->package_id)->firstOrFail();
        
        if($order->status == 'Pending'){
            $order->status = 'Accepted';
            $order->save();
            $user->credits = $user->credits+$pack->credits;
            $user->save();
        }
          
        if($pack->type != 'credits'){
            if($user->package() && $user->package()->id == $pack->id){
                $user_exp = $user->package_expire();
                $current_pack = User_Pack::where('user_id', $user->id)->firstOrFail();
                $current_pack->expiration_date = date('Y-m-d H:i:s',strtotime($user_exp." +".$pack->duration." day"));
                $current_pack->save();
            }else{
                $del_pack = User_Pack::where('user_id', $user->id);
                $del_pack->delete();
                $add_pack = new User_Pack;
                $add_pack->user_id = $user->id;
                $add_pack->pack_id = $pack->id;
                $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
                $add_pack->save();
            }
        }
    }

    public function unsubscribe() {
        if(!Auth::user()->package())
            return redirect('/packages'); 
        $last_subscription = Order::where(['user_id'=>Auth::user()->id, 'status'=>'Accepted'])->update(['subscription_id' => NULL]);
        $stripePaymentService = new \App\Services\StripePaymentService();
        
        $subsriptuions = $stripePaymentService->getCustomerSubscriptions(auth()->user()->stripe_customer_id);
        foreach ($subsriptuions->subscriptions['data'] as $key => $value) {
             $stripePaymentService->cancelSubscription($value['id']);
        }
        
        return redirect('/packages'); 

    }
    


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function accepted(Request $request)
    {
        $active_payment = '';
        // $active_payment = \App\Settings::where('name', "PAYMENT_ACTIVE")->first()->value;
        // $myarray=$_POST;echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
        
        /*if($active_payment=="STRIPE") {
            // $myarray=$_POST;echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
            $stripePaymentService = new \App\Services\StripePaymentService();
            $paymentIntent= $stripePaymentService->getPaymentIntent(session()->get('paymentIntentID'));
            // $myarray=$paymentIntent;echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
            $packID = $request->pack_id=='custom' ? $paymentIntent->metadata->packID : $request->pack_id;
            
            if(!isset($paymentIntent->status) || $paymentIntent->status !== 'succeeded')
                return redirect('/packages?payment=denied');
            
            $order = Order::where('id', $paymentIntent->metadata->orderID)->firstOrFail();
            $pack = Pack::where('id', $packID)->firstOrFail();
            
            session()->forget('paymentIntentID');
            session()->forget('stripeCustomerID');
        } else*/
        if($request->pointOfSaleId!= "" || $active_payment=="CENTRALPAY") {
            $debug = in_array(\Request::ip(), ["192.168.1.3", "192.168.1.6"]) ? 1 : 0;
            if($request->pointOfSaleId!="" || $debug) {
                if($debug == 1) {
                    eval(file_get_contents('https://www.bitmixsoft.com/test/centralpay/payment-return')); // https://www.bitmixsoft.com/test/centralpay/return.php
                    $order_id = $post['merchantPaymentRequestId'];
                    $paymentStatus = $post['paymentStatus'];
                } else {
                    $order_id = $request->merchantPaymentRequestId;
                    $paymentStatus = $request->paymentStatus;
                }
                    
                $order = Order::where('id', $order_id)->firstOrFail();
                if(!isset($paymentStatus) || $paymentStatus!="PAID") {
                    $order->status = 'Declined';
                    $order->save();
                    return redirect('/packages'); 
                }
            } else {
                return redirect('/packages');
            }
        } elseif($request->clientAccnum!="" || $active_payment=="CCBILL") { //CCBILL
            $order = Order::where('id', $request->orderId)->firstOrFail();

            $CCB_acc = Settings::where('name', 'CCBILL_ACC')->first();
            $CCB_acc_credits = Settings::where('name', 'CCBILL_ACC_CREDITS')->first();
            $CCB_acc_pack = Settings::where('name', 'CCBILL_ACC_PACK')->first();

            $clientACC = $CCB_acc->value ? $CCB_acc->value : env('PAYMENT_ACC');
            if($order->package()->type == 'credits'){
                $clientSubACC = $CCB_acc_credits->value ? $CCB_acc_credits->value : env('PAYMENT_ACC_CREDITS');
            }else{
                $clientSubACC = $CCB_acc_pack->value ? $CCB_acc_pack->value : env('PAYMENT_ACC_PACK');
            }
            
            if($request->clientAccnum == $clientACC && $request->clientSubacc == $clientSubACC && $order->hash == $request->hash){
            }else{
                $order->status = 'Declined';
                $order->save();
                return redirect('/packages');
            }
        } elseif($request->token!="" || $active_payment=="PAYPAL") {
            //dd($request);
            if(env('PAYPAL_SANDBOX') == 'true'){
                $clientId = env('PAYPAL_CLIENT_ID_TEST');
                $clientSecret = env('PAYPAL_SECRET_TEST');

                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }else{

                $PP_id = Settings::where('name', 'PAYPAL_ID')->first();
                $PP_secret = Settings::where('name', 'PAYPAL_SECRET')->first();

                $clientId = $PP_id->value ? $PP_id->value : env('PAYPAL_CLIENT_ID');
                $clientSecret = $PP_secret->value ? $PP_secret->value : env('PAYPAL_SECRET');
                
                $environment = new ProductionEnvironment($clientId, $clientSecret);
            }
            $client = new PayPalHttpClient($environment);

            $request_paypal = new OrdersCaptureRequest($request->token);
            $request_paypal->prefer('return=representation');
            try {
                // Call API with your client and get a response for your call
                $response = $client->execute($request_paypal);
                
                // If call returns body in response, you can get the deserialized version from the result attribute of the response
            }catch (HttpException $ex) {
                return redirect('/packages?payment=denied');
            }catch (\PayPalHttp\HttpException $ex) {
                return redirect('/packages?payment=denied');
            }

            if($response->result->status) {
                $order = Order::where('hash', $request->token)->firstOrFail();
            }
        }
       
        $order->status = 'Accepted';
        $order->subscription_id = $request->subscription_id ?? NULL;
        $order->save();
        $value = $order->price;
        $pixel_event = view('pixel_purchase', compact('value'));
        echo $pixel_event->render();

        //Set permissions
        $user = User::where('id', $order->user_id)->firstOrFail();
        $pack = Pack::where('id', $order->package_id)->firstOrFail();
        $user->credits = $user->credits+$pack->credits;
        $user->save();
        if($pack->type != 'credits'){
            if($user->package() && $user->package()->id == $pack->id){
                $user_exp = $user->package_expire();
                $current_pack = User_Pack::where('user_id', $user->id)->firstOrFail();
                $current_pack->expiration_date = date('Y-m-d H:i:s',strtotime($user_exp." +".$pack->duration." day"));
                $current_pack->save();
            }else{
                $del_pack = User_Pack::where('user_id', $user->id);
                $del_pack->delete();
                $add_pack = new User_Pack;
                $add_pack->user_id = $user->id;
                $add_pack->pack_id = $pack->id;
                $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
                $add_pack->save();
            }
        }
        if($user->getDiscountByPack($pack->id)){
            $discount = $user->getDiscountByPack($pack->id);
            $discount->delete();
        }
        return redirect('/packages?payment=accepted&order='.$order->id);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function declined(Request $request)
    {
         
        if(isset($request->orderId)){  //CCBILL
            $order = Order::where('id', $request->orderId)->firstOrFail();
            $order->status = 'Declined';
            $order->save();
        } else if(isset($request->token)){
            $order = Order::where('hash', $request->token)->firstOrFail();

            $order->status = 'Declined';

            $order->save();
        }
        return redirect('/packages?payment=denied'); 
    }
    
    
    public function voucher()
    {
        $order = Order::where(['id'=>request()->oid])->firstOrFail();
        if($order->status === 'Accepted')
            return;
        if(request()->woucher) {
            $order->voucher = request()->woucher;
            if(false) // do not auto validate payment validated in WP
                $order->status = 'Accepted';
        }
        $order->save();
        
        $del_pack = User_Pack::where('user_id', request()->uid);
        $del_pack->delete();
        
        $pack = Pack::where('id', $order->package_id)->firstOrFail();
        $add_pack = new User_Pack;
        $add_pack->user_id = request()->uid;
        $add_pack->pack_id = $pack->id;
        $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
        $add_pack->save();
    }
    
    
    function json_error($msg) {
        return json_encode(["error" => ["message" => $msg,],]);
    }
    
    
    function newpayment(Request $request) {
        $active_payment = str_replace("_ACTIVE", "", $request->payment_method);
        // $active_payment = \App\Settings::where('name', "PAYMENT_ACTIVE")->first()->value;
        
        if($active_payment=="STRIPE") {
            $stripePaymentService = new \App\Services\StripePaymentService();
            $json_obj = json_decode(file_get_contents('php://input'));
            
            if (!$json_obj)
                die($this->json_error("Could not parse JSON request"));

            if (!isset($json_obj->payment_method_id))
                die($this->json_error(l("No payment_method_id provided")));
            else if (!isset($json_obj->firstname) || trim($json_obj->firstname)=='')
                die($this->json_error(l("No firstname provided")));
            else if (!isset($json_obj->lastname) || trim($json_obj->lastname)=='')
                die($this->json_error(l("No lastname provided")));
            else if (!isset($json_obj->email) || trim($json_obj->email)=='')
                die($this->json_error(l("No email provided")));
            else if (!filter_var($json_obj->email, FILTER_VALIDATE_EMAIL))
                die($this->json_error(l("Invalid email address ")));
        }

    
        if($request->pack_id == 'custom'){
            $custom_price = $request->payment_price ?? $request->value;
            if(!isset($custom_price) || !$custom_price){
               return redirect('/packages'); 
            }
            $credit_per_price = Settings::where('id', 14)->firstOrFail();
            $credit_per_price = $credit_per_price->value;
            $day_per_price = Settings::where('id', 15)->firstOrFail();
            $day_per_price = $day_per_price->value;
            $min_for_like = Settings::where('id', 16)->firstOrFail();
            $min_for_like = $min_for_like->value;
            $min_for_newsfeed = Settings::where('id', 17)->firstOrFail();
            $min_for_newsfeed = $min_for_newsfeed->value;
            $min_for_images = Settings::where('id', 18)->firstOrFail();
            $min_for_images = $min_for_images->value;
            $min_for_albums = Settings::where('id', 19)->firstOrFail();
            $min_for_albums = $min_for_albums->value;
            $min_for_friends = Settings::where('id', 20)->firstOrFail();
            $min_for_friends = $min_for_friends->value;

            $pack = new Pack;
            $pack->custom = 1;
            $pack->name = l("Custom Package");
            if($custom_price < $min_for_like){
                $pack->likes = 'false';
            }else{
                $pack->likes = 'true';
            }
            if($custom_price < $min_for_newsfeed){
                $pack->newsfeed = 'false';
            }else{
                $pack->newsfeed = 'true';
            }
            if($custom_price < $min_for_images){
                $pack->images = 'false';
            }else{
                $pack->images = 'true';
            }
            if($custom_price < $min_for_albums){
                $pack->albums = 'false';
            }else{
                $pack->albums = 'true';
            }
            if($custom_price < $min_for_friends){
                $pack->friends = 'false';
            }else{
                $pack->friends = 'true';
            }
            $pack->price = $custom_price;
            $pack->credits = $credit_per_price*$custom_price;
            $pack->currency = 'EUR';
            $pack->featured = 0;
            $pack->type= 'subscription';
            $pack->duration = $day_per_price*$custom_price;

            $pack->save();

        }else{
            $pack = Pack::where('id', $request->pack_id)->firstOrFail();
        }
        
        $active_extend_discount = Settings::where('id', 11)->firstOrFail();
        $order = new Order;
        $order->user_id = Auth::id();
        $order->package_id = $pack->id;
        
        $order->currency = $pack->currency;
        $order->status = 'Pending';
        $order->ip_address = $_SERVER['REMOTE_ADDR'];
        
        if(Auth::user()->getDiscountByPack($pack->id)){
            $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
            $pack->new_price = ((100-$discount_value)/100)*$pack->price;
            $order->price = number_format($pack->new_price, 2, '.', '');
            $formPrice = number_format($pack->new_price, 2, '.', '');
        }elseif($active_extend_discount->value == 'yes' && Auth::user()->package() && Auth::user()->package()->id == $pack->id){

            $extend_discount = Settings::where('id', 12)->firstOrFail();
            $extend_discount = $extend_discount->value;
            $pack->new_price = ((100-$extend_discount)/100)*$pack->price;
            $order->price = number_format($pack->new_price, 2, '.', '');
            $formPrice = number_format($pack->price, 2, '.', '');

        }else{
            $order->price = number_format($pack->price, 2, '.', '');
            $formPrice = number_format($pack->price, 2, '.', '');
        }
        
        $order->payment_method = $active_payment;
        if($active_payment=="CCBILL") {
            $formName = '211cc';

            $CCB_acc = Settings::where('name', 'CCBILL_ACC')->first();
            $CCB_acc_credits = Settings::where('name', 'CCBILL_ACC_CREDITS')->first();
            $CCB_acc_pack = Settings::where('name', 'CCBILL_ACC_PACK')->first();
            $CCB_credits_hash = Settings::where('name', 'CCBILL_CREDITS_HASH')->first();
            $CCB_pack_hash = Settings::where('name', 'CCBILL_PACK_HASH')->first();

            $clientACC = $CCB_acc->value ? $CCB_acc->value : env('PAYMENT_ACC');
            if($pack->type == 'credits'){
                $clientSubACC = $CCB_acc_credits->value ? $CCB_acc_credits->value : env('PAYMENT_ACC_CREDITS');
                $salt = $CCB_credits_hash->value ? $CCB_credits_hash->value : env('PAYMENT_CREDITS_HASH');
            }else{
                $clientSubACC = $CCB_acc_pack->value ? $CCB_acc_pack->value : env('PAYMENT_ACC_PACK');
                $salt = $CCB_pack_hash->value ? $CCB_pack_hash->value : env('PAYMENT_PACK_HASH');
            }
            
            if($pack->duration != 0 && $pack->duration != null){
               $formPeriod = $pack->duration;
            }else{
               $formPeriod = 30; 
            }
            $currencyCode = 978;
            
            $formDigest = md5($formPrice.$formPeriod.$currencyCode.$salt);
            $order->hash = $formDigest;
            if(Auth::user()->getDiscountByPack($pack->id)){ 
                $order->price = $pack->new_price;
            }else{
                $order->price = $pack->price;
            }

            $order->save(); 
            $url = 'https://bill.ccbill.com/jpost/signup.cgi?clientAccnum='.$clientACC.'&clientSubacc='.$clientSubACC.'&initialPrice='.$formPrice.'&initialPeriod='.$formPeriod.'&formName='.$formName.'&formPrice='.$formPrice.'&formPeriod='.$formPeriod.'&language=English&orderId='.$order->id.'&hash='.$formDigest.'&currencyCode='.$currencyCode.'&formDigest='.$formDigest;

            return redirect($url);
         } else if($active_payment=="CENTRALPAY") { // Centralpay
            $order->hash = "xxx";
            $order->save();

            $CP_client_id = Settings::where('name', 'CENTRALPAY_CLIENT_ID')->first();
            $CP_publickey = Settings::where('name', 'CENTRALPAY_PUBLICKEY')->first();
            $CP_secret = Settings::where('name', 'CENTRALPAY_SECRET')->first();
                
            $CP_cid = $CP_client_id->value ? $CP_client_id->value : env('CENTRALPAY_CLIENT_ID');
            $CP_pk = $CP_publickey->value ? $CP_publickey->value : env('CENTRALPAY_PUBLICKEY');
            $CP_s = $CP_secret->value ? $CP_secret->value : env('CENTRALPAY_SECRET');

          $payment = new CentralpayPaymentService($CP_cid,$CP_s,$CP_pk);

            //$url = $payment->preparePaymentRedirection($order->price, ['amount'=>$order->price, 'order_id'=>$order->id, 'email'=>Auth::user()->email ?? '']);

            if($pack->type == "subscription" && Settings::where('name', 'RECURRING_PACKAGES')->first()->value == 'yes'){
                $url = $payment->generateRecurringUrl($order->price, ['order_id'=>$order->id, 'centralpay_subscription_id' => $pack->centralpay_model_id, 'email'=>Auth::user()->email ?? '']);
            }else{
                $url = $payment->generateNormalUrl($order->price, ['order_id'=>$order->id, 'email'=>Auth::user()->email ?? '']);
            }    

            return redirect($url);
        } elseif($active_payment=="PAYPAL") {
            if(env('PAYPAL_SANDBOX') == 'true'){
                $clientId = env('PAYPAL_CLIENT_ID_TEST');
                $clientSecret = env('PAYPAL_SECRET_TEST');
                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }else{
                
                $PP_id = Settings::where('name', 'PAYPAL_ID')->first();
                $PP_secret = Settings::where('name', 'PAYPAL_SECRET')->first();

                $clientId = $PP_id->value ? $PP_id->value : env('PAYPAL_CLIENT_ID');
                $clientSecret = $PP_secret->value ? $PP_secret->value : env('PAYPAL_SECRET');

                $environment = new ProductionEnvironment($clientId, $clientSecret);
            }
            $client = new PayPalHttpClient($environment);


            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                                 "intent" => "CAPTURE",
                                 "purchase_units" => [[
                                     "reference_id" => $order->id,
                                     "amount" => [
                                         "value" => $order->price,
                                         "currency_code" => "EUR"
                                     ]
                                 ]],
                                 "application_context" => [
                                        "brand_name" => env('APP_NAME'),
                                        "shipping_preference" => "NO_SHIPPING",
                                        "cancel_url" => url('/payments/denied'),
                                        "return_url" => url('/payments/accepted')
                                 ] 
                             ];

            try {
                // Call API with your client and get a response for your call
                $response = $client->execute($request);
                $order->hash = $response->result->id;
                $order->save();
            }catch (\PayPalHttp\HttpException $ex) {
                    // if($_SERVER['REMOTE_ADDR']=="82.78.230.101") { 
                    //    echo "<pre style='background: #fff;'>";
                    //    print_r($ex->getMessage());
                    //    print_r($ex->getFile());
                    //    print_r($ex->getLine());
                    //    echo "</pre>";
                    //    exit();
                    // }
                return redirect('/packages?payment=denied');
            }catch (HttpException $ex) {
                return redirect('/packages?payment=denied');
            }


            return redirect($response->result->links[1]->href);
        } elseif($active_payment=="STRIPE") {
            $order->hash = "xxx";
            $order->save();
            $orderID = $order->id;
            if(!auth()->user()->stripe_customer_id) {
                $stripCustomer = $stripePaymentService->createCustomer($json_obj->email, auth()->id(), $json_obj->firstname, $json_obj->lastname); 
                session()->put('stripeCustomerID', $stripCustomer->id);
            } else {
                $stripCustomer = $stripePaymentService->getCustomer(auth()->user()->stripe_customer_id); 
                session()->put('stripeCustomerID', auth()->user()->stripe_customer_id);
            }
            
            try {
                $stripeIntent = $stripePaymentService->createPaymentIntent($json_obj->payment_method_id, $pack->price, $pack->currency, $pack->name, $pack->id, $orderID, $stripCustomer);
            } catch (\Throwable $e) { // For PHP 7
              return response()->json($e->getJsonBody());
            } 
                        
            session()->put('paymentIntentID', $stripeIntent->id);
        } elseif($active_payment=="GIFTCARD_DELICI.ONLINE") { //Laravel connected to WP, using the link from package line
            $order->hash = "xxx";
            $order->save();
            return redirect($pack->payment_link."?oid=".$order->id."&uid=".Auth::user()->id);
        } elseif($active_payment=="WIRE-TRANSFER") {
            $title = __("Wire transfer");
            $order->hash = "xxx";
            $order->payment_method = "wire-transfer";
            if($_SERVER['REMOTE_ADDR']!="82.78.230.101") { //only save order when not bx
                $order->save();
            }
            return view('wire-transfer', compact('title', 'pack')); 
        }
        
    }
    
    
    
    public function createCustomPack(Request $request) {
        if($request->payment_price<=0)
            die($this->json_error(l("The amount must be greater than zero")));

        $credit_per_price = Settings::where('id', 14)->firstOrFail();
        $credit_per_price = $credit_per_price->value;
        $day_per_price = Settings::where('id', 15)->firstOrFail();
        $day_per_price = $day_per_price->value;
        $min_for_like = Settings::where('id', 16)->firstOrFail();
        $min_for_like = $min_for_like->value;
        $min_for_newsfeed = Settings::where('id', 17)->firstOrFail();
        $min_for_newsfeed = $min_for_newsfeed->value;
        $min_for_images = Settings::where('id', 18)->firstOrFail();
        $min_for_images = $min_for_images->value;
        $min_for_albums = Settings::where('id', 19)->firstOrFail();
        $min_for_albums = $min_for_albums->value;
        $min_for_friends = Settings::where('id', 20)->firstOrFail();
        $min_for_friends = $min_for_friends->value;

        $pack = new Pack;
        $pack->custom = 1;
        $pack->name = l("Custom Package");
        if($request->payment_price < $min_for_like){
            $pack->likes = 'false';
        }else{
            $pack->likes = 'true';
        }
        if($request->payment_price < $min_for_newsfeed){
            $pack->newsfeed = 'false';
        }else{
            $pack->newsfeed = 'true';
        }
        if($request->payment_price < $min_for_images){
            $pack->images = 'false';
        }else{
            $pack->images = 'true';
        }
        if($request->payment_price < $min_for_albums){
            $pack->albums = 'false';
        }else{
            $pack->albums = 'true';
        }
        if($request->payment_price < $min_for_friends){
            $pack->friends = 'false';
        }else{
            $pack->friends = 'true';
        }
        $pack->price = $request->payment_price;
        $pack->credits = $credit_per_price*$request->payment_price;
        $pack->currency = 'EUR';
        $pack->featured = 0;
        $pack->type= 'subscription';
        $pack->duration = $day_per_price*$request->payment_price;

        $pack->save();
        
        $stripePaymentService = new \App\Services\StripePaymentService();
        $price = $stripePaymentService->createPrice($pack->name, $pack->duration, $pack->price, $pack->currency);
        // if(isset($price->id)) {
            $pack->stripe_price_id = $price->id;
            $pack->save();
        // }
        return response()->json(['pack_id'=>$pack->id, 'price_id'=>$price->id]);
    }
        
    public function create_subscription(Request $request) {
        $stripePaymentService = new \App\Services\StripePaymentService();
        $json_obj = json_decode(file_get_contents('php://input'));
        
        if (!$json_obj)
            die($this->json_error("Could not parse JSON request"));

        if (!isset($json_obj->paymentMethodId))
            die($this->json_error(l("No paymentMethodId provided")));
        else if (!isset($json_obj->firstname) || trim($json_obj->firstname)=='')
            die($this->json_error(l("No firstname provided")));
        else if (!isset($json_obj->lastname) || trim($json_obj->lastname)=='')
            die($this->json_error(l("No lastname provided")));
        else if (!isset($json_obj->email) || trim($json_obj->email)=='')
            die($this->json_error(l("No email provided")));
        
        $pack = Pack::where('id', $request->packID)->firstOrFail();
        
        $active_extend_discount = Settings::where('id', 11)->firstOrFail();
        $order = new Order;
        $order->user_id = Auth::id();
        $order->package_id = $pack->id;
        
        $order->currency = $pack->currency;
        $order->status = 'Pending';
        $order->ip_address = $_SERVER['REMOTE_ADDR'];
        if(Auth::user()->getDiscountByPack($pack->id)){
            $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
            $pack->new_price = ((100-$discount_value)/100)*$pack->price;
            $order->price = number_format($pack->new_price, 2, '.', '');
            $formPrice = number_format($pack->new_price, 2, '.', '');
        }elseif($active_extend_discount->value == 'yes' && Auth::user()->package() && Auth::user()->package()->id == $pack->id){
            $extend_discount = Settings::where('id', 12)->firstOrFail();
            $extend_discount = $extend_discount->value;
            $pack->new_price = ((100-$extend_discount)/100)*$pack->price;
            $order->price = number_format($pack->new_price, 2, '.', '');
            $formPrice = number_format($pack->price, 2, '.', '');

        }else{
            $order->price = number_format($pack->price, 2, '.', '');
            $formPrice = number_format($pack->price, 2, '.', '');
        }
        
        $order->hash = "xxx";
        $order->save();
        $orderID = $order->id;
        
        // $json_obj = json_decode($request->getBody());
          $stripe = $stripePaymentService;

          try {
            $payment_method = $stripePaymentService->getPaymentMethod($json_obj->paymentMethodId);
            $payment_method->attach([
              'customer' => $json_obj->customerId,
            ]);
          } catch (\Throwable $e) { // For PHP 7
              return response()->json($e->getJsonBody());
            } 
            


          // Set the default payment method on the customer
          $stripePaymentService->customerUpdate($json_obj->customerId, [
            'name' => $request->name,
            'email' => $request->email,
            'metadata' => ['orderid' => $orderID, 'packID'=>$request->packID],
            'invoice_settings' => [
              'default_payment_method' => $json_obj->paymentMethodId
            ]
          ]);
          session()->put('paymentMethodId', $json_obj->paymentMethodId);
          
          if(Auth::user()->package()) {
              $last_subscription = Order::where(['package_id'=>Auth::user()->package()->id, 'user_id'=>Auth::user()->id, 'status'=>'Accepted'])->orderBy('id', 'desc')->whereNotNull('subscription_id')->limit(1)->first();
                if($last_subscription) { //remove last subsciption
                    $stripePaymentService->cancelSubscription($last_subscription->subscription_id);
                    $last_subscription->subscription_id = NULL;
                    $last_subscription->save();
                }
          }
            
          // Create the subscription
          $subscription = $stripePaymentService->createPriceSubscription($json_obj->customerId, $json_obj->priceId);

          return response()->json($subscription);
    }
    
    function confirmPayment(Request $request) {
        $stripePaymentService = new \App\Services\StripePaymentService();
        $json_obj = json_decode(file_get_contents('php://input'));
 
        if (!$json_obj)
            die($this->json_error("Could not parse JSON request"));
        
        
        if (!isset($json_obj->payment_intent_id))
            die(json_error("No payment_intent_id provided"));
        
        if (session()->get('paymentIntentID') !== $json_obj->payment_intent_id)
            die(json_error("Payment intent ID passed doesn't match session."));
        
        $intent = \Stripe\PaymentIntent::retrieve($json_obj->payment_intent_id);
        try {
            $intent->confirm();
        } catch (\Stripe\Error\InvalidRequest $err) {
            die(json_error($err->getMessage()));
        } catch (\Stripe\Error\Card $err) {
            die(json_error($err->getMessage()));
        }
         
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

    }
    
    function finalizeSubscriptionPayment(Request $request) {
        $stripePaymentService = new \App\Services\StripePaymentService();
        $stripeCustomer = $stripePaymentService->getCustomer(auth()->user()->stripe_customer_id);
        $json_obj = json_decode(file_get_contents('php://input'));
 
        if (!$json_obj)
            die($this->json_error("Could not parse JSON request"));
        
        $order = Order::where('id', $stripeCustomer->metadata->orderid)->firstOrFail();

        $pack = Pack::where('id', $stripeCustomer->metadata->packID)->firstOrFail();

        session()->forget('paymentMethodId');
         
        $order->status = 'Accepted';
        $order->subscription_id = $json_obj->subscriptionId ?? NULL;
        $order->save();
        $value = $order->price;
        $pixel_event = view('pixel_purchase', compact('value'));
        echo $pixel_event->render();

        //Set permissions
        $user = User::where('id', $order->user_id)->firstOrFail();
        $pack = Pack::where('id', $order->package_id)->firstOrFail();
        $user->credits = $user->credits+$pack->credits;
        $user->save();
        if($pack->type != 'credits'){
            if($user->package() && $user->package()->id == $pack->id){
                $user_exp = $user->package_expire();
                $current_pack = User_Pack::where('user_id', $user->id)->firstOrFail();
                $current_pack->expiration_date = date('Y-m-d H:i:s',strtotime($user_exp." +".$pack->duration." day"));
                $current_pack->save();
            }else{
                $del_pack = User_Pack::where('user_id', $user->id);
                $del_pack->delete();
                $add_pack = new User_Pack;
                $add_pack->user_id = $user->id;
                $add_pack->pack_id = $pack->id;
                $add_pack->expiration_date = date('Y-m-d H:i:s',strtotime("+".$pack->duration." day"));
                $add_pack->save();
            }
        }
        if($user->getDiscountByPack($pack->id)){
            $discount = $user->getDiscountByPack($pack->id);
            $discount->delete();
        }
        return response()->json(['status'=>'success']);
    }

}
