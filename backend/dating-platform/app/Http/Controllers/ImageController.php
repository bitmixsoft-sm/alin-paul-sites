<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function load_upload(Request $request)
    {
        $images = Auth::user()->images->where('privacy', '')->slice($request->items)->take(12);
        $tpl = '';
        foreach($images as $image){
            $tpl .= '<div class="choose-photo-item" data-mh="choose-item">
                            <div class="checks">
                                <label class="custom-radio">
                                    <img src="/storage/images/'.$image->name.'" alt="photo">
                                    <input class="optionsRadios" type="checkbox" name="optionsRadios[]" value="'.$image->id.'" data-url="/storage/images/'.$image->name.'">
                                    <span class="circle"></span><span class="check"></span>
                                </label>
                            </div>
                        </div>';
        }
        return response()->json(['tpl' =>$tpl, 'number' => $images->count()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function show(Image $image)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Image $image)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function destroy(Image $image)
    {
        //
    }
}
