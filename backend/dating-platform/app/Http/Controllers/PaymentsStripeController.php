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

class PaymentsStripeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     
    public function newpayment(Request $request)
    {
        //CCBILL

        if($request->pack_id == 'custom'){
            if(!isset($request->value)){
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
            if($request->value < $min_for_like){
                $pack->likes = 'false';
            }else{
                $pack->likes = 'true';
            }
            if($request->value < $min_for_newsfeed){
                $pack->newsfeed = 'false';
            }else{
                $pack->newsfeed = 'true';
            }
            if($request->value < $min_for_images){
                $pack->images = 'false';
            }else{
                $pack->images = 'true';
            }
            if($request->value < $min_for_albums){
                $pack->albums = 'false';
            }else{
                $pack->albums = 'true';
            }
            if($request->value < $min_for_friends){
                $pack->friends = 'false';
            }else{
                $pack->friends = 'true';
            }
            $pack->price = $request->value;
            $pack->credits = $credit_per_price*$request->value;
            $pack->currency = 'EUR';
            $pack->featured = 0;
            $pack->type= 'subscription';
            $pack->duration = $day_per_price*$request->value;

            $pack->save();

        }else{
            $pack = Pack::where('id', $request->pack_id)->firstOrFail();
        }

        $order = new Order;
        $order->user_id = Auth::id();
        $order->package_id = $pack->id;
        
        $order->currency = $pack->currency;
        $order->status = 'Pending';
        $order->ip_address = $_SERVER['REMOTE_ADDR'];


        $formName = '211cc';
        $clientACC = env('PAYMENT_ACC');
        if($pack->type == 'credits'){
            $clientSubACC = env('PAYMENT_ACC_CREDITS');
            $salt = env('PAYMENT_CREDITS_HASH');
        }else{
            $clientSubACC = env('PAYMENT_ACC_PACK');
            $salt = env('PAYMENT_PACK_HASH');
        }
        if(Auth::user()->getDiscountByPack($pack->id)){
            $discount_value = Auth::user()->getDiscountByPack($pack->id)->value;
            $pack->new_price = ((100-$discount_value)/100)*$pack->price;
            $formPrice = number_format($pack->new_price, 2, '.', '');
        }else{
            $formPrice = number_format($pack->price, 2, '.', '');
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


    }*/
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
    public function newpayment_pre(Request $request)
    {
        if($request->pack_id == 'custom'){
            if(!isset($request->value)){
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
            if($request->value < $min_for_like){
                $pack->likes = 'false';
            }else{
                $pack->likes = 'true';
            }
            if($request->value < $min_for_newsfeed){
                $pack->newsfeed = 'false';
            }else{
                $pack->newsfeed = 'true';
            }
            if($request->value < $min_for_images){
                $pack->images = 'false';
            }else{
                $pack->images = 'true';
            }
            if($request->value < $min_for_albums){
                $pack->albums = 'false';
            }else{
                $pack->albums = 'true';
            }
            if($request->value < $min_for_friends){
                $pack->friends = 'false';
            }else{
                $pack->friends = 'true';
            }
            $pack->price = $request->value;
            $pack->credits = $credit_per_price*$request->value;
            $pack->currency = 'EUR';
            $pack->featured = 0;
            $pack->type= 'subscription';
            $pack->duration = $day_per_price*$request->value;

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
        
        $order->hash = "xxx";
        $order->save();
        $orderid = $order->id;
        $title = l('Payment');
        return view('pay_stripe', compact('pack', 'title', 'orderid'));
       
        $title = l('Packages');
        /*if(!$request->pay_processor) {
            $PP_status = Settings::where('name', 'PAYPAL_ACTIVE')->firstOrFail();
            $PP_active = $PP_status->value;
            $CCB_status = Settings::where('name', 'CCBILL_ACTIVE')->firstOrFail();
            $CCB_active = $CCB_status->value;
            $CP_status = Settings::where('name', 'CENTRALPAY_ACTIVE')->firstOrFail();
            $CP_active = $CP_status->value;
            return view('pay_processor', compact('title', 'PP_active', 'CCB_active', 'CCB_active', 'CP_active'));
        }*/
        
        if($request->pack_id == 'custom'){
            if(!isset($request->value)){
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
            if($request->value < $min_for_like){
                $pack->likes = 'false';
            }else{
                $pack->likes = 'true';
            }
            if($request->value < $min_for_newsfeed){
                $pack->newsfeed = 'false';
            }else{
                $pack->newsfeed = 'true';
            }
            if($request->value < $min_for_images){
                $pack->images = 'false';
            }else{
                $pack->images = 'true';
            }
            if($request->value < $min_for_albums){
                $pack->albums = 'false';
            }else{
                $pack->albums = 'true';
            }
            if($request->value < $min_for_friends){
                $pack->friends = 'false';
            }else{
                $pack->friends = 'true';
            }
            $pack->price = $request->value;
            $pack->credits = $credit_per_price*$request->value;
            $pack->currency = 'EUR';
            $pack->featured = 0;
            $pack->type= 'subscription';
            $pack->duration = $day_per_price*$request->value;

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

        if(true) {
            $order->hash = "xxx";
            $order->save();
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            header('Content-Type: application/json');

            $YOUR_DOMAIN = env('APP_URL');

            $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price_data' => [
                        'unit_amount' => $formPrice*100,
                        'currency' => $pack->currency,
                        'product_data' => ['name'=>$pack->name],
                    ],
                    'quantity' => 1,
                ]],
                'payment_method_types' => [
                    'card',
                ],
                "metadata" => [
                    "order_id" => $order->id
                ],
                'mode' => 'payment',
                'success_url' => route('accepted_payment')."?scode={CHECKOUT_SESSION_ID}",
                'cancel_url' => route('declined_payment'),
            ]);
     
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit();
        } elseif(false) { //Laravel connected to WP, using the link from package line
            $order->hash = "xxx";
            $order->save();
            return redirect($pack->payment_link."?oid=".$order->id."&uid=".Auth::user()->id);
        }
        
        
        if($request->pay_processor=="ccbill") { // ccbill
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

        } else if($request->pay_processor=="centralpay") { // Centralpay
            $order->hash = "xxx";
            $order->save();

            $CP_client_id = Settings::where('name', 'CENTRALPAY_CLIENT_ID')->first();
            $CP_publickey = Settings::where('name', 'CENTRALPAY_PUBLICKEY')->first();
            $CP_secret = Settings::where('name', 'CENTRALPAY_SECRET')->first();
    	    	
            $CP_cid = $CP_client_id->value ? $CP_client_id->value : env('CENTRALPAY_CLIENT_ID');
            $CP_pk = $CP_publickey->value ? $CP_publickey->value : env('CENTRALPAY_PUBLICKEY');
            $CP_s = $CP_secret->value ? $CP_secret->value : env('CENTRALPAY_SECRET');

            $payment = new CentralpayPaymentService($CP_cid,$CP_s,$CP_pk);

            $url = $payment->preparePaymentRedirection($order->price, ['amount'=>$order->price, 'order_id'=>$order->id, 'email'=>Auth::user()->email ?? '']);
            return redirect($url);
        } elseif($request->pay_processor=="paypal") {
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
                return redirect('/packages?payment=denied');
            }catch (HttpException $ex) {
                return redirect('/packages?payment=denied');
            }


            return redirect($response->result->links[1]->href);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     
    public function accepted(Request $request)
    {
        //CCBILL
        if(isset($request->orderId)){
        $order = Order::where('id', $request->orderId)->firstOrFail();

        $clientACC = env('PAYMENT_ACC');
        if($order->package()->type == 'credits'){
            $clientSubACC = env('PAYMENT_ACC_CREDITS');
        }else{
            $clientSubACC = env('PAYMENT_ACC_PACK');
        }

        if($request->clientAccnum == $clientACC && $request->clientSubacc == $clientSubACC && $order->hash == $request->hash){

            $order->status = 'Accepted';
            $order->subscription_id = $request->subscription_id;
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

        }else{

            $order->status = 'Declined';
            $order->save();
            
        }
    }else{

        return redirect('/profile');

    }

    }*/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function accepted(Request $request)
    {
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
         
        $order->status = 'Accepted';
        $order->subscription_id = $subscription->id ?? NULL;
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
        return redirect('/profile');
        
/*        $last_subscription = Order::where(['package_id'=>Auth::user()->package()->id, 'user_id'=>Auth::user()->id, 'status'=>'Accepted'])->orderBy('id', 'desc')->whereNotNull('subscription_id')->limit(1)->first();

        $invoice_log = "";
        $logs_vars=['post'=>$_POST,'get'=> $_GET, 'session'=>$_SESSION];
        foreach ($logs_vars as $lk => $log_vars)
             if (is_array($log_vars))
                foreach ($log_vars as $k=>$v)
                     $invoice_log .= "Returned ${lk}: $k=".$v."\n";
        
        
        $infoLog = new \Monolog\Logger('payment_log');
        $infoLog->pushHandler(new \Monolog\Handler\StreamHandler(storage_path('logs/payment.log')), \Monolog\Logger::INFO);
        $infoLog->info('', [$invoice_log]);
        
        
        
        $stripeToken = $request->stripeToken;
        $stripeEmail = $request->stripeEmail;
        $order = Order::where('id', $request->orderid)->firstOrFail();
        $pack = Pack::where('id', $request->pack_id)->firstOrFail();
        if ($pack->type=='subscription') {

            if(!isset($stripeToken) || $stripeToken=='')
                return redirect('/packages')->with('error', "Token is missing");
            try {
            
            $pack = Pack::where('id', $request->pack_id)->firstOrFail();
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient( //delete a plan
              env('STRIPE_SECRET')
            );
            $plan_id = "plan-".$pack->id."_".preg_replace("/[^a-zA-Z0-9_\-]/", "_",  request()->server->get('SERVER_NAME'));
            
            try {            
                $plan_exists = $stripe->plans->retrieve(
                  $plan_id,
                  []
                );
            } catch (\Throwable $e) {
                // echo "create plan";
                $plan = \Stripe\Plan::create(array(
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
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $stripe = new \Stripe\StripeClient( //delete a plan
                  env('STRIPE_SECRET')
                );
                
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
              if(!isset($stripeToken) || $stripeToken=='')
                return redirect('/packages')->with('error', "Token is missing");
              \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                  $stripe = new \Stripe\StripeClient( //delete a plan
                    env('STRIPE_SECRET')
                  );
             $success = "Thanks!";
            try{ 
                  $charge_response = \Stripe\Charge::create ([
                      "amount" => $pack->price*100,
                      "currency" => $pack->currency,
                      "source" => $request->stripeToken,
                      "description" => $pack->name 
                  ]);
                  
             } catch (Exception $e) {
                // echo $e->getMessage();
                $error = "Sorry, we weren't able to authorize your card. You have not been charged.";
            }
          }
          
        
                if(isset($error) && $error!='') {
                    $order->status = 'Declined';
                    $order->save();
                    return redirect('/packages'); 
                } else {
                    $order->status = 'Accepted';
                    $order->subscription_id = $subscription->id ?? NULL;
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
                    return redirect('/profile');
                }
                
            if(isset($success))
                return redirect('/profile')->with('success', $success);
            else
                return redirect('/packages')->with('error', $error);
            */
          
        return redirect('/packages'); 
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
            } else {
                $order->status = 'Accepted';
                $order->subscription_id = $request->subscription_id ?? NULL;
                $order->save();
                $value = $order->price;
                $pixel_event = view('pixel_purchase', compact('value'));
                if($debug != 1)
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
                return redirect('/profile');
            }
        } elseif(isset($request->orderId)){ //ccbill
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

                $order->status = 'Accepted';
                $order->subscription_id = $request->subscription_id;
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

            }else{

                $order->status = 'Declined';
                $order->save();
                
            }
            return redirect('/profile');
        }
        
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

        if($response->result->status){

            $order = Order::where('hash', $request->token)->firstOrFail();

            $order->status = 'Accepted';

            $order->save();
            
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
            return redirect('/packages?payment=denied');
        }else{
            return redirect('/packages?payment=denied'); 
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     
    public function declined(Request $request)
    {
        //CCBILL
        if(isset($request->orderId)){
            $order = Order::where('id', $request->orderId)->firstOrFail();
            $order->status = 'Declined';
            $order->save();
        }else{
           return redirect('/packages'); 
        }
    }*/
    
    
    public function voucher()
    {
        $order = Order::where(['id'=>request()->oid])->firstOrFail();
        if($order->status === 'Accepted')
            return;
        if(request()->woucher) {
            $order->voucher = request()->woucher;
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

    
        if($request->pack_id == 'custom'){
            if(!isset($request->payment_price)){
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
