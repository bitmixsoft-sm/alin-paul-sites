<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pack;
use App\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Settings;

class LiveFeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //if(Auth::check()){

            $user = User::where('gender', 'female')->where('profile_image', '!=', '')->select(['username', 'firstname', 'lastname', 'profile_image'])->inRandomOrder()->first();

            if (! $user) {
                return response()->json(false);
            }

            $active_custom_pack = Settings::where('id', 13)->first();

            if($active_custom_pack && $active_custom_pack->value == 'yes'){

                $pack = Pack::whereIn('type', ['subscription', 'subscription-credits'])->where('name', '!=', 'Trial')->select('name', 'id')->inRandomOrder()->first();

            }else{

                $pack = Pack::whereIn('type', ['subscription', 'subscription-credits'])->where('name', '!=', 'Trial')->where('custom', '!=', 1)->select('name', 'id')->inRandomOrder()->first();

            }

            // No matching subscription package exists to fake a purchase for (e.g. all
            // "subscription" type packs are named "Trial", or none are configured yet) -
            // skip the live-feed ticker instead of erroring out.
            if (! $pack) {
                return response()->json(false);
            }

            $time = rand(2, 23);

            $time_units = [l('hours'), l('minutes'), l('seconds')];

            $time_unit = $time_units[rand(0,2)];

            return response()->json(['user' => $user, 'pack' => $pack, 'time' => $time, 'time_unit' => $time_unit]);
        //}
        //return response()->json(false);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return response()->json($discount);

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
}
