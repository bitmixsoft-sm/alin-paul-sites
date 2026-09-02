<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserActivity;
use Illuminate\Support\Facades\Auth;
use App\WebAccount;
use App\Client;
use App\Unsubscribe;
use App\AdminReplyEmail;
use Illuminate\Support\Facades\Hash;

class EditorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 'admin'){
        $administrators = User::where('role', 'admin')->orWhere('role', 'editor')->get();
        $on_page = 'Administratori';
        return view('admin.editors', compact('administrators', 'on_page'));
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if(Auth::user()->isAdmin()){
            if($request->option == 'admin' && Auth::user()->role == 'admin'){
            $search = $request->search;
            
            $tpl = '';
            $x = 1;
            if($search == ""){
                $admins = User::where('role', '!=', 'user')->get();
            }else{
            $admins = User::where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->get();
            }
            if($admins->count() != 0){
                foreach($admins as $admin){
                    if($admin->role != 'user'){
                    $tpl .= '<tr>
                        <td>'.$x.'</td>
                        <td>
                            <div class="table-data__info">
                                <h6>'.$admin->name().'</h6>
                                <span><a href="#">'.$admin->email.'</a></span>
                            </div>
                        </td>
                        <td>
                            <img src="/storage/images/'.$admin->profile_image().'" alt="'.$admin->name().'" style="width: 75px;border-radius: 50%;">
                        </td>
                        <td>
                            <span class="role '.$admin->role.'">'.$admin->role.'</span>
                        </td>
                        <td>
                            <span>'.$admin->created_at->format("d/m/Y").'</span>
                        </td>
                        <td>
                            <span></span>
                        </td>
                        <td>
                        <span class="more">
                            <i class="zmdi zmdi-more"></i>
                        </span>
                        </td>
                   </tr>'; 
                   $x++;
                   }    
                }
                return response()->json($tpl);
                }
            }
            if($request->option == 'clients' && Auth::user()->isAdmin()){
                $search = $request->search;
                
                $tpl = '';
                $x = 1;
                if($search == ""){
                    $users = Client::paginate(20);
                }else{
                $users = Client::where(function($q) use ($search) {
                                    $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('source', 'like', '%'.$search.'%');
                              })
                            ->paginate(20);
                }
                if($users->count() != 0){
                foreach($users as $user){
                    $tpl .= '<tr>
                                                    <td>'.$x.'</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>'.$user->name.'</h6>
                                                            <span>
                                                                <a href="#">'.$user->email.'</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span>'.$user->country.'</span>
                                                    </td>
                                                    <td>
                                                        <span>'.$user->region.'</span>
                                                    </td>
                                                    <td>
                                                        <span>'.$user->city.'</span>
                                                    </td>
                                                    <td>
                                                        <span>'.$user->source.'</span>
                                                    </td>';
                                                    if(Auth::user()->role == 'admin'){
                                                        $tpl .= '<td>
                                                            <span>'.$user->admin_name().'</span>
                                                        </td>';
                                                    }
                                                    $tpl .= '<td>';
                                                        if($user->isRegistered()){ $tpl .= '<i class="fas fa-check"></i>'; }else{ $tpl .= '<i class="fas fa-times"></i>'; }
                                                    $tpl .= '</td>
                                                    <td>
                                                        <span>'.$user->created_at->format('d/m/Y H:i').'</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/client/edit/'.$user->id.'"><i class="zmdi zmdi-more"></i></a>
                                                        </span>
                                                    </td>
                                                </tr>'; 
                   $x++;   
                }
                $users->withPath('/admin/clients');
                $view = view('admin.components.pagination', compact('users', 'search'))->render();
                $response = ['tpl' => $tpl, 'pags' => $view];
                return response()->json($response);
                }
            }
            
            if($request->option == 'user' && Auth::user()->isAdmin()){
                $search = $request->search;
                
                $tpl = '';
                $x = 1;
                if($search == ""){
                    $users = User::where('role', 'user')->paginate(20);
                }else{
                $users = User::where('role', 'user')->where(function($q) use ($search) {
                                    $q->where('firstname', 'like', '%'.$search.'%')
                            ->orWhere('lastname', 'like', '%'.$search.'%')
                            ->orWhere('username', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                              })
                            ->paginate(20);
                }
                if($users->count() != 0){
                foreach($users as $user){
                    if($user->role == 'user'){
                    $tpl .= '<tr>
                                                        <td>'.$x.'</td>
                                                        <td>
                                                            <div class="table-data__info">
                                                                <h6>'.$user->name().'</h6>
                                                                <span>
                                                                    <a href="#">'.$user->email.'</a>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>
                                                                    <a target="_blank" href="/profile/'.$user->username.'">'.$user->username.'</a>
                                                            </span>
                                                        </td>
                                                        <td>';
                                                        if($user->gender == 'male'){
                                                                $tpl .= 'Barbat';
                                                            }else{
                                                                $tpl .= 'Femeie';
                                                            }
                                                        $tpl .= '</td>
                                                        <td>
                                                            <span class="role user">';
                                                            if($user->package()){
                                                                $tpl .= $user->package()->name;
                                                            }else{
                                                                $tpl .= 'Nu';
                                                            }
                                                            $tpl .='</span>
                                                        </td>
                                                        <td>
                                                            <span class="role user">'.number_format($user->credits, 0, '.', ',').'</span>
                                                        </td>
                                                        <td>
                                                            <span>'.$user->created_at->format("d/m/Y H:i").'</span>
                                                        </td>
                                                        <td>
                                                            <span class="more">
                                                                <a href="/admin/users/'.$user->username.'"><i class="zmdi zmdi-more"></i></a>
                                                            </span>
                                                        </td>
                                                    </tr>'; 
                   $x++;
                   }    
                }
                $users->withPath('/admin/users');
                $view = view('admin.components.pagination', compact('users', 'search'))->render();
                $response = ['tpl' => $tpl, 'pags' => $view];
                return response()->json($response);
                }
            }
            if($request->option == 'editor_accounts' && Auth::user()->role == 'admin' || $request->option == 'newsletter_dest' && Auth::user()->isAdmin()){
            $search = $request->search;
            
            $tpl = '<div class="tag-search">';
            $x = 1;
            if($search == ""){
                $tpl = '<div class="tag-search"><span>Niciun rezultat</span></div>';
                return response()->json($tpl);
            }else{
                $unsubscribed = Unsubscribe::all();
                $unsubscribed = $unsubscribed->pluck('email')->toArray();
                if($request->option == 'editor_accounts'){
            $users = User::where('role', 'user')->where('gender', 'female')->whereNotIn('id', $request->payload)->where(function($q) use ($search) {
                                $q->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                          })
                        ->take(5)->get();
                }
                if($request->option == 'newsletter_dest' && Auth::user()->role == 'admin'){
            $users = User::where('role', 'user')->where('gender', 'male')->whereNotIn('id', $request->payload)->whereNotIn('email', $unsubscribed)->where(function($q) use ($search) {
                                $q->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                          })
                        ->take(5)->get();
                }
                if($request->option == 'newsletter_dest' && Auth::user()->role == 'editor'){
                    $users= User::select('users.*')->leftJoin('clients', 'users.email','=', 'clients.email')->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->where('admin_id', Auth::id())->whereNotIn('users.id', $request->payload)->whereNotIn('clients.email', $unsubscribed)->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->where(function($q) use ($search) {
                                $q->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('users.email', 'like', '%'.$search.'%');
                          })
                        ->take(5)->get();
                }
            if($users->count() != 0){
                foreach($users as $user){
                    if($user->role == 'user'){
                        $name = "'".$user->name()."'";
                        $option = "'".$request->option."'"; 
                    $tpl .= '<div class="tag-search-result" onclick="tag_result('.$user->id.','.$name.','.$option.');">
                                    <img src="/storage/images/'.$user->profile_image().'">
                                    <span>'.$user->name().'</span>
                                </div>
                            '; 
                   }    
                }
                $tpl .= ' </div>';
                return response()->json($tpl);
                }else{
                $tpl = '<div class="tag-search"><span>Niciun rezultat</span></div>';
                return response()->json($tpl);    
                }
            }
            }
            return false;
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
        $user = User::where('username', $id)->where('role', '!=', 'user')->firstOrFail();
        $on_page = "Editor";
        return view('admin.editor_profile', compact('user', 'on_page'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        if($request->input('block') == 'block'){
            $user = User::where('id', $request->input('user'))->firstOrFail();
            $user->banned = 'yes';
            $user->save();
        }
        if($request->input('update') == 'update'){
            $user = User::where('id', $request->input('user'))->firstOrFail();
            $user->role = $request->input('role');
            $user->admin_ip = $request->input('admin_ip');
            if($request->input('password'))
                $user->password = Hash::make($request->input('password'));
            $user->save();
            $del_acc = WebAccount::where('admin_id', $request->input('user'));
            $del_acc->delete();
            if($request->editor_accounts){
                foreach($request->editor_accounts as $acc){
                    $wb = new WebAccount;
                    $wb->admin_id = $request->input('user');
                    $wb->user_id = $acc;
                    $wb->save();
                }
            }else{
                $del_acc = WebAccount::where('admin_id', $request->input('user'));
                $del_acc->delete();    
            }

            if($request->input('admin_reply_email')){
                if($user->adminReplyEmail){
                    $adminReplyEmail = $user->adminReplyEmail;
                    $adminReplyEmail->email = $request->input('admin_reply_email');
                    $user->adminReplyEmail()->save($adminReplyEmail);
                }else{
                    $adminReplyEmail = new AdminReplyEmail(['email' => $request->input('admin_reply_email')]);
                    $user->adminReplyEmail()->save($adminReplyEmail);
                }
            }
        }
        if($request->input('all_profiles') == 'all_profiles'){
            $del_acc = WebAccount::where('admin_id', $request->input('user'));
            $del_acc->delete();
            $accounts = User::where('gender', 'female')->select('id')->get();
            $accounts = $accounts->pluck('id')->toArray();
            foreach($accounts as $acc){
                $wb = new WebAccount;
                $wb->admin_id = $request->input('user');
                $wb->user_id = $acc;
                $wb->save();
            }
        }
        \Cache::forget('accounts.' . $request->input('user'));
        \Cache::forget('accountIds.' . $request->input('user'));
        return redirect('/admin/editors');
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
        //
    }
}
