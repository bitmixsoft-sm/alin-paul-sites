<?php
namespace App\Services;

use App\Settings;
use Exception;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;

class CentralpayPaymentService
{
    protected $config;
    public $baseURL;
    public $client_id;
    public $public_key;
    public $secret;
    public $currency;
    public $sandbox;
    public $payment_form_id;
    public $subscription_model_id;
    public $point_of_sale_id;

    public function __construct($clientId, $secret, $publicKey) {

        $this->client_id = $clientId;
        $this->public_key = $publicKey;
        $this->secret = $secret;
        $this->currency = env('CENTRALPAY_CURRENCY');
        $this->sandbox = env('CENTRALPAY_SANDBOX');

        $payment_form_id = Settings::where('name', 'CENTRALPAY_FORM_ID')->first();
        $pointOfSaleId = Settings::where('name', 'CENTRALPAY_POINT_OF_SALE')->first();

        $this->payment_form_id = $payment_form_id ? $payment_form_id->value : env('CENTRALPAY_TEMPLATEID');

        if($pointOfSaleId){
            $this->setPointOfSale($pointOfSaleId->value);
        }

        if ( $this->sandbox == 'yes' ) {
            $this->baseURL = "https://test-api.centralpay.net/v2/rest";
        } else {
            $this->baseURL = "https://api.centralpay.net/v2/rest";
        }
    }

    public function setPointOfSale($pointOfSaleId) {
        $this->point_of_sale_id = $pointOfSaleId;
    }

    public function setSubscriptionModelId($subscriptionModelId) {
        $this->subscription_model_id = $subscriptionModelId;
    }

    // https://ref-api.centralpay.net/recurring#157-subscription-model
    public function updateOrCreateSubscriptionModel($name, $price, $intervalUnit = 'DAY', $intervalCount, $packId, $iterationCount = null ) {
        $amount = (int) ($price * 100);
        $request = [
            'amount' => $amount,
            'currency' => $this->currency,
            'name' => $name,
            'intervalUnit' => $intervalUnit,
            'intervalCount' => $intervalCount,
            'pointOfSaleId' => $this->point_of_sale_id ?? env('CENTRALPAY_POINT_OF_SALE'),
            'merchantSubscriptionModelId' => $packId,
        ];

        if ( $iterationCount ) {
            $request['iterationCount'] = $iterationCount;
        }

        $url = $this->baseURL."/subscriptionModel/?".http_build_query($request);

        if ( $this->subscription_model_id ) {
            $request['subscriptionModelId'] = $this->subscription_model_id;
            $url = $this->baseURL.'/subscriptionModel/'.$this->subscription_model_id.'?'.http_build_query($request);
        }

        $client = new Client();
        $response = $client->request('POST', $url, [
            'auth' => [$this->client_id, $this->secret],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Idempotence-Key' => Str::random(16),
            ]
        ]);


        $data = json_decode($response->getBody()->getContents(), true);

        if ( isset($data['subscriptionModelId']) ) {
            return $data['subscriptionModelId'];
        } else {
            Log::error('Centralpay: Error creating subscription model: '.json_encode($data));
            return false;
        }
    }



    public function generateNormalUrl($amount, $userInfo) {
        try {
                $amount = (int)($amount * 100);
                
                $posts = [
                        'paymentMethod[]' => 'TRANSACTION',
                        'currency' => $this->currency,
                        'totalAmount' => $amount,
                        'merchantPaymentRequestId' => $userInfo['order_id'],
                        'paymentFormTemplateId' => $this->payment_form_id,
                        'breakdown[]' => '{"amount":'.$amount.',"email":"'.($userInfo['email'] ?? '').'"}',
                        "pointOfSaleId" => $this->point_of_sale_id ?? env('CENTRALPAY_POINT_OF_SALE'),
                    ];
                    
                $url = $this->baseURL."/paymentRequest";
                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
                curl_setopt($ch, CURLOPT_USERPWD, $this->client_id.":".$this->secret);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json',]);
    
                $result = curl_exec($ch);
    
                if (curl_errno($ch)) {
                    //echo 'Error:' . curl_error($ch);
                }
    
                $ret = json_decode($result);

                 $debug = in_array(\Request::ip(), ["82.78.230.101","192.168.1.6"]) ? 1 : 0;
                if($debug) {
                echo "<pre style='background: #fff;'>";
                print_r("Normal URL");
                print_r("<br>");
                print_r(\Request::ip());
                print_r("<br>");
                print_r($url);
                print_r($posts);
                print_r("<br>");
                print_r($ret);
                //print_r($_GET);
                //print_r($_SESSION);
                //print_r($_COOKIE);
                //print_r($_FILES);
                //echo print_r(array_keys(get_defined_vars()));
                //echo var_export(array_diff(get_defined_vars(), array(array())));
                echo "</pre>";
                exit();
                }
                    	
    
                if($ret->paymentRequestId && $ret->breakdowns[0]->endpoint!='') {
                   return $ret->breakdowns[0]->endpoint;
                }
            } catch(Exception $e) {
                \Log::error($e->getMessage());
            }
            return false;
    }
    
    
    public function generateRecurringUrl($amount, $userInfo) {
        try {
                $amount = (int)($amount * 100);
                
                $posts = [
                        'paymentMethod[]' => 'SUBSCRIPTION',
                        'currency' => $this->currency,
                        'totalAmount' => $amount,
                        'merchantPaymentRequestId' => $userInfo['order_id'],
                        'paymentFormTemplateId' => $this->payment_form_id,
                        'breakdown[]' => '{"amount":'.$amount.',"email":"'.($userInfo['email'] ?? '').'"}',
                        "pointOfSaleId" => $this->point_of_sale_id ?? env('CENTRALPAY_POINT_OF_SALE'),
                        'subscription[subscriptionModelId]' => $userInfo['centralpay_subscription_id'],
                    ];
                    
                $url = $this->baseURL."/paymentRequest";
                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
                curl_setopt($ch, CURLOPT_USERPWD, $this->client_id.":".$this->secret);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json',]);
    
                $result = curl_exec($ch);
    
                if (curl_errno($ch)) {
                    //echo 'Error:' . curl_error($ch);
                }
    
                $ret = json_decode($result);

                $debug = in_array(\Request::ip(), ["82.78.230.101","192.168.1.6"]) ? 1 : 0;
                if($debug) {
                echo "<pre style='background: #fff;'>";
                print_r("Reccuring URL");
                print_r("<br>");
                print_r(\Request::ip());
                print_r("<br>");
                print_r($url);
                print_r($posts);
                print_r("<br>");
                print_r($ret);
                //print_r($_GET);
                //print_r($_SESSION);
                //print_r($_COOKIE);
                //print_r($_FILES);
                //echo print_r(array_keys(get_defined_vars()));
                //echo var_export(array_diff(get_defined_vars(), array(array())));
                echo "</pre>";
                exit();
                }
    
                if($ret->paymentRequestId && $ret->breakdowns[0]->endpoint!='') {
                   return $ret->breakdowns[0]->endpoint;
                }
            } catch(Exception $e) {
                \Log::error($e->getMessage());
            }
            return false;
    }


    public function return(Request $request) {
        $post = \Request::post();

    }

}
