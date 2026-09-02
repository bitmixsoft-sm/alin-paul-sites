<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Client;
use Illuminate\Support\Facades\Auth;
use App\Unsubscribe;

class AdminClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $on_page = 'Clienti';
        if (Auth::user()->role == 'editor') {
            $clients = Client::where('admin_id', Auth::id())->orderBy('id', 'desc')->paginate(20);
        } else {
            $clients = Client::orderBy('id', 'desc')->paginate(20);
        }

        return view('admin.clients', compact('clients', 'on_page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $on_page = 'Adauga Client';
        $add = true;

        $editors = User::where('role', 'admin')->orWhere('role', 'editor')->get();

        return view('admin.clients_add', compact('add', 'on_page', 'editors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_multiple()
    {
        $on_page = 'Adauga Client';
        $add = true;

        $editors = User::where('role', 'admin')->orWhere('role', 'editor')->get();

        return view('admin.clients_add_multiple', compact('add', 'on_page', 'editors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $check_email = Client::where('email', $request->email)->exists();
        if ($check_email) {
            return redirect('/admin/clients/add');
        } else {
            $requests = $request->all();
            $client = new Client;
            foreach ($requests as $key => $value) {
                if ($key != '_token') {
                    $client->{$key} = $request->{$key};
                }
            }
            if ($request->admin_id) {
                $client->admin_id = $request->admin_id;
            } else {
                $admin_id = Auth::id();
                $client->admin_id = $admin_id;
            }
            $client->save();

            return redirect('/admin/clients');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function add_multiple(Request $request)
    {
        $emails = $request->emails;
        $emails = explode(',', $emails);
        foreach ($emails as $email) {
            $email = str_replace(' ', '', $email);
            $email = str_replace('"', '', $email);
            $email = str_replace('[', '', $email);
            $email = str_replace(']', '', $email);
            $email = str_replace('{', '', $email);
            $email = str_replace('}', '', $email);
            $email = str_replace("'", '', $email);
            $check_email = Client::where('email', $email)->exists();
            if (!$check_email) {
                $requests = $request->all();
                $client = new Client;
                if ($request->admin_id) {
                    $client->admin_id = $request->admin_id;
                } else {
                    $admin_id = Auth::id();
                    $client->admin_id = $admin_id;
                }
                $client->email = $email;
                $name = explode('@', $email);
                $client->name = $name[0];
                $client->source = $request->source;
                $client->save();


            }
        }
        return redirect('/admin/clients');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function unsubscribe_multiple(Request $request)
    {
        $emails = $request->emails;
        $emails = explode(',', $emails);
        foreach ($emails as $email) {
            $email = str_replace(' ', '', $email);
            $email = str_replace('"', '', $email);
            $email = str_replace('[', '', $email);
            $email = str_replace(']', '', $email);
            $email = str_replace('{', '', $email);
            $email = str_replace('}', '', $email);
            $email = str_replace("'", '', $email);
            $check_email = Unsubscribe::where('email', $email)->exists();
            if (!$check_email) {
                $client = new Unsubscribe;
                $client->email = $email;
                $client->save();
            }
        }
        return redirect('/admin/clients');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $on_page = 'Editare Client';
        $add = false;
        $client = Client::where('id', $id)->firstOrFail();
        $editors = User::where('role', 'admin')->orWhere('role', 'editor')->get();

        return view('admin.clients_add', compact('add', 'client', 'on_page', 'editors'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $requests = $request->all();
        $client = Client::where('id', $id)->firstOrFail();
        foreach ($requests as $key => $value) {
            if ($key != '_token') {
                $client->{$key} = $request->{$key};
            }
        }
        $client->save();

        return redirect('/admin/clients');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->isAdmin()) {
            $user = Client::where('id', $id)->firstOrFail();
            $user->delete();
        }
        return redirect('/admin/clients');
    }

    public function removeByEmails(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt']
        ]);

        $file = $request->file('file');
        $ext = explode('.', $file->getClientOriginalName());

        $path = $file->getRealPath();
        $csv_data = array_map('str_getcsv', file($path));
        if ($validator->fails() || strtolower(end($ext)) != "csv" || !count($csv_data))
            return response()->json(['success' => false]);
        $deleted = Client::whereIn('email', $csv_data)->delete();
        return response()->json(['success' => true, 'data' => $deleted]);
    }
}
