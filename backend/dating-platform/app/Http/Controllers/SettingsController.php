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

        // Groups categorized rows (e.g. Boost's settings) under their own heading in the view,
        // keeping every never-categorized row (the vast majority - see the migration that
        // added this column) together under a single "General" section at the end, exactly as
        // the flat list looked before this existed. null keys aren't valid array keys for
        // Collection::groupBy's rendering purposes in the view, so they're normalized to the
        // literal string 'General' here rather than in the blade file.
        $groupedSettings = $settings->groupBy(fn ($setting) => $setting->category ?: 'General')
            // "General" holds almost every existing row (nothing was categorized before this),
            // so without this it would sort first (lowest ids) instead of acting like the
            // catch-all it's meant to be - named categories should stand out at the top.
            ->sortBy(fn ($group, $category) => $category === 'General' ? 1 : 0)
            // Within a category, the *_ENABLED/*_ACTIVE "master switch" (see the enabler
            // branch in admin/settings.blade.php) reads best first, above the fields it
            // grays out - but id order (how these rows happen to have been created over time)
            // doesn't always land it there (e.g. STRIPE_KEY/STRIPE_SECRET both have lower ids
            // than STRIPE_ACTIVE). Re-sorts each group's rows without touching $settings
            // itself, so nothing outside this grouped view is affected.
            ->map(fn ($group) => $group->sortBy(fn ($setting) => (str_ends_with($setting->name, '_ENABLED') || str_ends_with($setting->name, '_ACTIVE')) ? 0 : 1));

        return view('admin.settings', compact('on_page', 'settings', 'groupedSettings'));
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
