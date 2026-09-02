<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\CallVideochat;
use App\Events\AnswerVideochat;
use App\Events\RefuseVideochat;
use Illuminate\Support\Facades\Auth;
use App\User;

class VideoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->call_to){
            $otherUser = User::where('id', $request->call_to)->firstOrFail();
        }
        if($request->called_by){
             $otherUser = User::where('id', $request->called_by)->firstOrFail();
        }
        $title = 'Ongoing Call with '.$otherUser->name();
        return view('videochat', compact('title', 'otherUser'));
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
    public function credits(Request $request)
    {
        if(!Auth::user()->isAdmin()){
            $user = User::where('id', Auth::id())->firstOrFail();
            if($user->credits > $request->credits){
                $user->credits = $user->credits - $request->credits;
                $user->save();
            } else {
                $u1 = ['id' => Auth::id()];
                if (isset($request->callFrom) && $request->callFrom != '') {
                    $u2 = ['id' => $request->callFrom];
                } else {
                    $u2 = ['id' => $request->otherUser];
                }
                event(new RefuseVideochat($u1));
                event(new RefuseVideochat($u2));
            }
        }
        return response()->json('true');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function call(Request $request)
    {
        $user = User::where('id', $request->to)->firstOrFail();
        if ($request->callFrom != null && $request->callFrom != '') {
            $adm = User::where('id', $request->callFrom)->firstOrFail();
            $response = ['id' => $request->to,'offer' => $request->data, 'from' => $adm->id, 'callFrom' => Auth::id(), 'profile_image' => $adm->profile_image(), 'name' => $adm->name(), 'ready' => $request->ready, 'name' => $adm->name(), 'profile_image' => $adm->profile_image()];
        } else {
            $response = ['id' => $request->to,'offer' => $request->data, 'from' => Auth::id(), 'profile_image' => Auth::user()->profile_image(), 'name' => Auth::user()->name(), 'ready' => $request->ready, 'name' => Auth::user()->name(), 'profile_image' => Auth::user()->profile_image()];
        }
        if(in_array($user->id, Auth::user()->allBlocked())){
            $res = ['id' => Auth::id()];
            event(new RefuseVideochat($res));
        }else{
            event(new CallVideochat($response));
        }

        $return = ['name' => $user->name(), 'profile_image' => $user->profile_image()];
        return response()->json($return);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function answer(Request $request)
    {
        $response = ['id' => $request->to,'answer' => $request->data, 'from' => Auth::id(), 'ready' => $request->ready];
        if ($request->callFrom != null && $request->callFrom != '') {
            $response['id'] = $request->callFrom;
        }
        event(new AnswerVideochat($response));
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refuse(Request $request)
    {
        $response = ['id' => $request->to];
        if ($request->callFrom != null && $request->callFrom != '') {
            $response = ['id' => $request->callFrom];
        }
        event(new RefuseVideochat($response));
        return response()->json($response);
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
