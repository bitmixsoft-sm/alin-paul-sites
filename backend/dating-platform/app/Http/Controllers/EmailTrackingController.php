<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Email;
use App\EmailTracking;
use Carbon\Carbon;

class EmailTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $on_page = 'Newsletter';
        if(Auth::user()->role == 'admin'){
            $emails = Email::orderBy('id', 'desc')->paginate(25);
        }else{
            $emails = Email::where('website', $_SERVER['SERVER_NAME'])->orderBy('id', 'desc')->paginate(25);
        }
        $local_emails = Email::whereDate('created_at', Carbon::today())->where('website', $_SERVER['SERVER_NAME'])->select('id')->get();
        $local_emails = $local_emails->pluck('id');
        $email_local = EmailTracking::whereIn('email_id', $local_emails)->count();
        $email_global = EmailTracking::whereDate('created_at', Carbon::today())->count();

        return view('admin.email_tracking', compact('emails', 'on_page', 'email_local', 'email_global'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $on_page = 'Newsletter';
        $email = Email::where('id', $id)->firstOrFail();

        $users = EmailTracking::where('email_id', $id)->orderBy('seen', 'desc')->orderBy('updated_at', 'desc')->paginate(25);

        return view('admin.email_tracking_user', compact('email', 'on_page', 'users'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function track($code)
    {
        $track = EmailTracking::where('tracking', $code)->firstOrFail();
        if($track->seen == 0){
            $track->seen = 1;
            $track->save();
        }
        return redirect()->secure('/images/pixel.png');
    }
}
