<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ImageGet;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title =l("REAL DATING OR LIVE SEX");
        
        return view('landing.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function game()
    {

        $title =l("REAL DATING OR LIVE SEX");
        $user = User::where('gender', 'female')->has('images', '>=', 4)->inRandomOrder()->take(3)->get();
        return view('landing.game', compact('title', 'user'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_account(Request $request)
    {

        $user = User::where('id', $request->id);

        if($user->exists()){
            $user = $user->firstOrFail();
            $data = array();
            $data['id'] = $user->id;
            $data['name'] = $user->name();
            $data['image'] = $user->profile_image();
        }else{
            $user = User::where('gender', 'female')->inRandomOrder()->firstOrFail();
            $data = array();
            $data['id'] = $user->id;
            $data['name'] = $user->name();
            $data['image'] = $user->profile_image(); 
        }
        
        return response()->json($data);
    }
}
