<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Block;
use App\User;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title =l("Profile Page");
        $blocked = Auth::user()->blocked();
        $users = User::whereIn('id', $blocked)->get();
        return view('profile.blocked_users', compact('users', 'title'));
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
    public function block($id)
    {
        $user = Auth::id();
        $checkBlock = Block::where(function ($query) use ($user, $id) {
                        $query->where('user', $id)
                              ->orWhere('user', $user);
                    })->where(function ($query) use ($user, $id) {
                        $query->where('block', $id)
                              ->orWhere('block', $user);
                    })->exists();
        if(!$checkBlock){
            $block = new Block;
            $block->user = $user;
            $block->block = $id;
            //$block->save(); 
        }
        return back();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unblock($id)
    {
        $user = Auth::id();
        $checkBlock = Block::where(function ($query) use ($user, $id) {
                        $query->where('user', $id)
                              ->orWhere('user', $user);
                    })->where(function ($query) use ($user, $id) {
                        $query->where('block', $id)
                              ->orWhere('block', $user);
                    })->exists();
        if($checkBlock){
            $block = Block::where('user', $user)->where('block', $id);
            $block->delete(); 
        }
        return back();
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
