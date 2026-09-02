<?php
namespace App\Services;

use Exception;
use Illuminate\Http\Request;

class CentralpayPaymentService
{
    protected $config;
    
    public function __construct() {
        $this->config = ['client_id'=>env('CENTRALPAY_CLIENT_ID'), 'publickey'=>env('CENTRALPAY_PUBLICKEY'), 'secret'=>env('CENTRALPAY_SECRET'), 'currency'=>env('CENTRALPAY_CURRENCY'), 'sandbox'=>env('CENTRALPAY_SANDBOX'), 'paymentFormTemplateId'=>env('CENTRALPAY_TEMPLATEID')];
        $this->api_url = $this->config['sandbox'] !="yes" ? 'https://api.centralpay.net/v2/rest' : "https://test-api.centralpay.net/v2/rest";
        $this->payment_url = $this->api_url. '/paymentRequest';
        $this->client = new \GuzzleHttp\Client();
    }
    
    
    public function preparePaymentRedirection($amount, $userInfo) {
        try {
            $amount = (int)($amount * 100);
            
            $posts = [
                    'paymentMethod[]' => 'TRANSACTION',
                    'currency' => $this->config['currency'],
                    'totalAmount' => $amount,
                    'merchantPaymentRequestId' => $userInfo['order_id'],
                    'paymentFormTemplateId' => $this->config['paymentFormTemplateId'],
                    'breakdown[]' => '{"amount":'.$amount.',"email":"'.($userInfo['email'] ?? '').'"}',
                ];
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->payment_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
            curl_setopt($ch, CURLOPT_USERPWD, $this->config['client_id'].":".$this->config['secret']);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                // echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $ret = json_decode($result);

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
