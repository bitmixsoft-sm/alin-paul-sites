<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Order;
use Illuminate\Support\Facades\Auth;

class AdminDashController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 'admin'){
        $analytics = array();
        $users = User::where('role', 'user');
        $analytics['total_users'] = $users->count();
        $months = array();
        $month_usr = array();
        foreach($users->orderBy('created_at')->get() as $user){
            if(!in_array($user->created_at->format('m'), $months)){
            $months[] = $user->created_at->format('m');
            }
        }
        foreach ($months as $m) {
            $month_usr[] = User::where('role', 'user')->whereMonth('created_at', $m)->count();
        }
        $analytics['users_m'] = json_encode($months);
        $analytics['users_m_number'] = json_encode($month_usr);

        $analytics['users_male'] = User::where('role', 'user')->where('gender', 'male')->count();
        $analytics['users_female'] = User::where('role', 'user')->where('gender', 'female')->count();

        $orders = Order::where('status', 'Accepted');

        $analytics['total_items'] = $orders->count(); 

        $it_months = array();
        $it_months_num = array();
        $ch_months_num = array();

        foreach($orders->orderBy('created_at')->get() as $order){
            if(!in_array($order->created_at->format('m'), $it_months)){
            $it_months[] = $order->created_at->format('m');
            }
        }
        foreach ($it_months as $m) {
            $it_months_num[] = Order::where('status', 'Accepted')->whereMonth('created_at', $m)->count();
            $ch_months_num[] = Order::where('status', 'Accepted')->whereMonth('created_at', $m)->sum('price');
        }

        $analytics['orders_m'] = json_encode($it_months);
        $analytics['orders_m_number'] = json_encode($it_months_num);

        $analytics['total_cash'] = $orders->sum('price');

        $analytics['cash_month'] = json_encode($ch_months_num);


        $on_page = 'Dashboard';

        return view('admin.dashboard',compact('analytics', 'on_page'));
        }else{
            return redirect('/admin/users');
        }
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
        //
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
        //
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
}
