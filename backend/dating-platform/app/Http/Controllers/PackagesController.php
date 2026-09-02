<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pack;
use App\OrderAttempt;
use Auth;
use Carbon\Carbon;

class PackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->stripe_add==1) {
            \App\Settings::insert(['name'=>'STRIPE_SECRET', 'value'=>env('STRIPE_SECRET')]);
            \App\Settings::insert(['name'=>'STRIPE_KEY', 'value'=>env('STRIPE_KEY')]);
        }
        
        $title = 'Packages';
        $stripe_public_key = '123';
        
        /*$user_pack = \App\User_Pack::where(['user_id' => Auth::user()->id])->first();
        
        if($user_pack) {
            try{
            $last_subscription = \App\Order::where(['package_id'=>$user_pack->pack_id, 'user_id'=>Auth::user()->id, 'status'=>'Accepted'])->orderBy('id', 'desc')->whereNotNull('subscription_id')->limit(1)->first();
                if($last_subscription) {
                    $pack = Pack::where(['id'=>$user_pack->pack_id])->first();
                    // $pack = Pack::where(['id'=>$user_pack->id])->first();
                    if($user_pack->expiration_date<=\Carbon\Carbon::now()) {
                        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                        $stripe = new \Stripe\StripeClient( //delete a plan
                          env('STRIPE_SECRET')
                        );
                        $stripe_subscription = \Stripe\Subscription::retrieve($last_subscription->subscription_id, []);

                        $subscription_start_date = Carbon::createFromTimestamp($stripe_subscription->start_date);
                        $user_pack->expiration_date = $subscription_start_date->addDays($pack->duration);
                        $user_pack->save();
                    }
                }
            } catch (Exception $e) {
            }
        }*/
        
        //$active_payment = \App\Settings::where('name', "PAYMENT_ACTIVE")->first()->value;
        $db_active_payments = \App\Settings::whereIn('name', ['STRIPE_ACTIVE', 'PAYPAL_ACTIVE', 'CCBILL_ACTIVE', 'CENTRALPAY_ACTIVE', 'GIFTCARD_DELICI.ONLINE_ACTIVE', 'WIRE-TRANSFER_ACTIVE'])->where('value', 'yes')->get()->pluck('name');
        foreach ($db_active_payments as $value) {
            $active_payments[$value] = str_replace("_", " ", str_replace("_active", "", ucwords(strtolower($value))));
        }

        $credit_packages = Pack::Where('price', '!=', '0')->where('custom', '!=', 1)->whereIn('type', ['subscription-credits', 'credits'])->orderBy('price', 'asc')->get();
        $sub_packages = Pack::Where('price', '!=', '0')->where('custom', '!=', 1)->where('type', 'subscription')->where('name', '!=', 'Trial')->orderBy('price', 'asc')->get();
        return view('packages', compact('title', 'credit_packages', 'sub_packages','stripe_public_key', 'active_payments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function orderAttemptsStore(Request $request)
    {
        OrderAttempt::create([
            'user_id'     => auth()->user()->id,
            'pack_id'    => $request->pack_id ?? 0,
            'price'      => $request->price ?? 0,
            'currency'   => $request->currency ?? '',
            'ip_address' => $request->ip(),
        ]);
    }
}
