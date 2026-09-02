<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CMS;

class AdminCmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 'admin'){
        $on_page = 'Pagini CMS';
        $pages = CMS::select(['id','name','route','lang','created_at','updated_at'])->get(); 
        return view('admin.cms',compact('pages', 'on_page'));
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
        if(Auth::user()->role == 'admin'){
        $on_page = 'Adauga pagina CMS';
        $page = '';
        return view('admin.cms_edit',compact('page', 'on_page'));
        }else{
            return redirect('/admin/users');
        }
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
        if($request->name != '' && $request->route != '' && $request->content_value != ''){
            if($request->id == ''){
                $check_language = CMS::where('name', $request->name)->where('lang', $request->lang)->exists();
                $check_route = CMS::where('route', $request->route)->exists();
            }else{
                $check_language = CMS::where('name', $request->name)->where('lang', $request->lang)->where('id', '!=', $request->id)->exists();
                $check_route = CMS::where('route', $request->route)->where('id', '!=', $request->id)->exists();
            } 
            if($request->id == ''){
                if(!$check_language && !$check_route){
                    $cms = new CMS;
                    $cms->name = $request->name;
                    $cms->route = $request->route;
                    $cms->content = $request->content_value;
                    $cms->lang = $request->lang;
                    $cms->save();
                }else{
                    return response()->json(['deny' => 'Exista deja o pagina cu acelasi titlu si in aceeasi limba sau cu acelasi link']);
                }
            }else{
                if(!$check_language && !$check_route){
                    $cms = CMS::where('id', $request->id)->firstOrFail();
                    $cms->name = $request->name;
                    $cms->route = $request->route;
                    $cms->content = $request->content_value;
                    $cms->lang = $request->lang;
                    $cms->save();
                }else{
                    return response()->json(['deny' => 'Exista deja o pagina cu acelasi titlu si in aceeasi limba sau cu acelasi link']);
                }
            }
            return response()->json(['route' => '/admin/cms']);
        }else{
            return response()->json(['deny' => 'Completeaza toate campurile']);
        }
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
        /*if(Auth::user()->role == 'admin'){*/
        $page = CMS::where('route', $id)->firstOrFail();
        $title = $page->name;
        return view('cms_page',compact('page', 'title'));
        /*}else{
            return redirect('/admin/users');
        } */
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->role == 'admin'){
        $page = CMS::where('id', $id)->firstOrFail();
        $on_page = 'Editare '.$page->name;
        return view('admin.cms_edit',compact('page', 'on_page'));
        }else{
            return redirect('/admin/users');
        }
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
        //
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
            $page = CMS::where('id', $id)->firstOrFail();
            $page->delete();
            return redirect('/admin/cms');
        }else{
            return redirect('/admin/users');
        }
    }
}
