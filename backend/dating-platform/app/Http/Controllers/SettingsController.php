<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $on_page = 'Setari';

        $settings = Settings::all()->sortBy('id');
        
        return view('admin.settings', compact('on_page', 'settings'));
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
        $settings = $request->all();
        var_dump($settings);
        foreach ($settings as $key => $value) {
            if($key != '_token'){
                if($key == '7' && request()->hasFile('7')){
                    $user_id = Auth::id();
                    $imageWithExt = request()->file('7')->getClientOriginalName();
                    $image = pathinfo($imageWithExt, PATHINFO_FILENAME);
                    $ext = request()->file('7')->getClientOriginalExtension();
                    $user_id = Auth::id();

                    $image_name = rand().'_'.rand().'_'.'main_img'.'_'.$user_id.'.'.$ext;

                    $path = request()->file('7')->storeAs('public/images/', $image_name);

                    $current = Settings::where('id', $key)->firstOrFail();
                    $img = '/storage/images/'.$image_name;
                    $current->value = $img;
                    $current->save();
                }else{
                    $current = Settings::where('id', $key)->firstOrFail();
                    $current->value = $value;
                    $current->save();
                }
            }
        }
        return redirect('/admin/settings');
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
