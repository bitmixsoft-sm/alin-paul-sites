<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pack;
use App\Discount;
use App\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Settings;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 'admin'){

            $on_page = 'Reduceri';

            $discounts = Discount::where('ending_at', '>', Carbon::now())->get();

            return view('admin.discounts', compact('discounts', 'on_page'));

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
        //if(!Auth::user()->isAdmin()){
        $settings = Settings::where('id', 9)->firstOrFail();
        if($settings->value == 'yes'){
            $user = Auth::user();

            if(!$user->hasDiscount()){

                $discount = new Discount;
                $discount->user_id = $user->id;

                if($user->package() && $user->package()->name != 'Trial'){
                    $packs = Pack::where('name', '!=', 'Trial')->where('custom', '!=', 1)->where('id', '!=', $user->package()->id)->where('price', '>', $user->package()->price);
                }else{
                    $packs = Pack::where('name', '!=', 'Trial')->where('custom', '!=', 1);
                }

                $p_count = $packs->count();
                $packs = $packs->get();
                $pack = $packs[rand ( 0 , $p_count-1 )];
                $discount->pack_id = $pack->id;
                $discount->value = rand( 15, 50);
                $discount->ending_at = Carbon::now()->addDay();
                $discount->save();

                return response()->json($discount);
            }else{

                 return response()->json($user->getDiscount());

            }
        }else{
          return response()->json(false);  
        }
        //}

        return response()->json(false);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->role == 'admin'){

            $users_id = $request->newsletter_dest;

            foreach($users_id as $user_id){

                $user = User::where('id', $user_id)->where('discount', 1);

                if($user->exists()){
                $user = $user->firstOrFail();
                $discounts = $user->getDiscounts();

                if($discounts){
                    foreach($discounts as $discount){
                        $discount->delete();
                    }
                }

                $discount = new Discount;
                $discount->user_id = $user->id;
                $discount->pack_id = $request->pack_id;
                $discount->value = $request->value;
                $discount->ending_at = Carbon::parse($request->ending_at);
                $discount->save();
                }

            }

            return redirect('/admin/discounts');

        }else{
            return redirect('/admin/users');
        }
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
        if(Auth::user()->role == 'admin'){

            $discount = Discount::where('id', $id);

            if($discount->exists()){
                $discount->delete();
            }

            return redirect('/admin/discounts');

        }else{
            return redirect('/admin/users');
        }
    }
}
