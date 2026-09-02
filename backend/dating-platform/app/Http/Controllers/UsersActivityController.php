<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserActivity;
use DB;

class UsersActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role === 'admin') {
            return view('admin.users_activity', [
                'activities' => UserActivity::with('user')->orderBy('created_at', 'desc')->paginate(20),
                'on_page' => 'Activitate utilizatori'
                ]);
        } else {
            return redirect('/admin/users');
        }
    }

    // Store user activity
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'eventDetails' => 'required|string',
        ]);

        $userActivity = UserActivity::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'details' => $validated['eventDetails'],
            'ip' => get_client_ip(),
            'location' => get_user_country() . ', ' . get_user_region() . ', ' . get_user_city()
        ]);
    }

    public function search(Request $request){
        if(Auth::user()->role === 'admin'){
                $search = $request->search;

                $tpl = '';

                if(empty($search)){
                    $userActivities = UserActivity::with('user')->paginate(20);
                }else{
                $userActivities = UserActivity::with('user')->where(function($q) use ($search) {
                                    $q->where('name', 'like', '%'.$search.'%')->orWhere(DB::raw('lower(details)'), 'like', '%'. strtolower($search) . '%');
                              })->orWhereHas('user', function($q) use ($search){
                                  $q->where('firstname', 'like', '%'.$search.'%')
                                    ->orWhere('lastname', 'like', '%'.$search.'%')
                                    ->orWhere('username', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%');
                              })
                            ->paginate(20);
                }
                if($userActivities->count() != 0){
                foreach($userActivities as $key => $activity){
                    $tpl .= '<tr>
                                                    <td>' . $key . '</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>' .$activity->user->firstname . ' '. $activity->user->lastname.'</h6>
                                                            <span>
                                                                <a href="#">'.$activity->user->email.'</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                       '.$activity->name.'
                                                    </td>

                                                    <td>
                                                       '. $activity->details .'
                                                    </td>

                                                    <td>
                                                      '.$activity->created_at.'
                                                    </td>
                                                </tr>';
                    }
                }

                $userActivities->withPath('/admin/users-activity');
                $view = view('admin.components.pagination', [
                    'users' => $userActivities,
                    'search' => $search
                    ])->render();
                $response = ['tpl' => $tpl, 'pags' => $view];
                return response()->json($response);
                }
    }
}
