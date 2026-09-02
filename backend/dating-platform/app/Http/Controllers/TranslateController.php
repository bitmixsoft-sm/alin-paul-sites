<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Lang;
use Illuminate\Support\Facades\Auth;
use File;

class TranslateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $on_page = 'Traduceri';

        $langs = Lang::orderBy('created_at', 'asc')->get();

        return view('admin.translate',compact('on_page', 'langs'));
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
        if($request->hasFile('image')){

            $file = $request->file('image');
            
            $ext = $file->getClientOriginalExtension();

            $fileName = $request->code.'.'.$ext;

            $file->storeAs('public/lang', $fileName); 

            $lang = new Lang;
            $lang->name = $request->name;
            $lang->code = $request->code;
            $lang->ext = $ext;
            $lang->save();

            $path = base_path().'/resources/lang/'.$request->code;
            File::makeDirectory($path, $mode = 0777, true, true);

            $lg_path = base_path()."/resources/lang/en/lang.json";
            $trans = file_get_contents($lg_path);

            file_put_contents(base_path()."/resources/lang/".$request->code."/lang.json", $trans);

            return redirect('/translate')->with('success', 'Limba a fost adaugata!');

        }else{
            return redirect('/translate')->with('error', 'Nu ati incarcat steagul!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function change_lang($id)
    {
        if(Auth::check()){
            if(Lang::where('code', $id)->exists()){
                $_SESSION['lang'] = $id;
                $user = User::where('id', Auth::id())->firstOrFail();
                $user->lang = $id;
                $user->save();
            }else{
                $id = 'en';
                $_SESSION['lang'] = $id;
                $user = User::where('id', Auth::id())->firstOrFail();
                $user->lang = $id;
                $user->save();
            }
        }else{
            if(Lang::where('code', $id)->exists()){
                $_SESSION['lang'] = $id;
            }else{
                $id = 'en';
                $_SESSION['lang'] = $id;
            }
        }

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change_user_lang($id, Request $request)
    {
        if(Auth::user()->isAdmin()){
            $lang = $request->lang;
            if(Lang::where('code', $lang)->exists()){
                $user = User::where('id', $id)->firstOrFail();
                $user->lang = $lang;
                $user->save();
            }else{
                $lang = 'en';
                $user = User::where('id', $id)->firstOrFail();
                $user->lang = $lang;
                $user->save();
            }
        }

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $id = $request->lang;
        $lg = Lang::where('code', $id)->firstOrFail();
        $on_page = "Traducere ".$lg->name;

        $lg_path = base_path()."/resources/lang/".$id."/lang.json";

        $langs = json_decode(file_get_contents($lg_path), true);


        return view('admin.edit_translate',compact('on_page', 'langs', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $trans = array();
        for ($i=0; $i < $request->ct; $i++) { 
            $key = 'key_'.$i;
            $val = 'val_'.$i;
            $trans[$request->{$key}] = $request->{$val};
        }
        file_put_contents(base_path()."/resources/lang/".$request->lang."/lang.json", json_encode($trans));

        return redirect('/translate')->with('success', 'Traducerea a fost facuta!');
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
