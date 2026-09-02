<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\OrderAttempt;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $on_page = 'Comenzi';
        $orders = Order::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.orders', compact('on_page', 'orders'));
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
    
    
    public function orderAttempts()
    {
        $on_page = 'Comenzi Initiate';
        $order_attemps = OrderAttempt::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.order-attempts', compact('on_page', 'order_attemps'));
    }
    
    public function deleteOlderOrderAttempts(Request $request) {
        if($request->days > 0)
            OrderAttempt::where('created_at', "<=", now()->subDays($request->days)->format('Y-m-d'))->delete();
        return redirect('/admin/order-attempts');
    }
    
    public function deleteOrderAttempts($id)
    {
        OrderAttempt::where('id', $id)->delete();
        return redirect('/admin/order-attempts');
    }
}
