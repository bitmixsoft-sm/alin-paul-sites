<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use App\Country;
use App\City;
use App\States;
use App\ImageGet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = User::where('id', Auth::id())->firstOrFail();
        $title = l("Profile Page");
        $posts = Post::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(30)->get();
        return view('profile.profile', compact('user', 'title', 'posts'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('username', $id)->firstOrFail();
        $title = l("Profile Page");
        $posts = Post::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(30)->get();

        if (Auth::check() && in_array($user->id, Auth::user()->allBlocked())) {
            return redirect('/profile');
        } else {
            return view('profile.profile', compact('user', 'title', 'posts'));
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit()
    {
        $user = User::where('id', Auth::id())->firstOrFail();
        $user->birthday = $user->birthday != '' ? date("d/m/Y", strtotime($user->birthday)) : '';
        $title = l("Profile Settings");
        $countries = Country::select(['name', 'id'])->get();
        if (Auth::user()->country) {
            $country = Country::where('name', Auth::user()->country)->firstOrFail();
            $states = States::where('country_id', $country->id)->get();
            if (Auth::user()->county) {
                $state = States::where('country_id', $country->id)->where('name', Auth::user()->county)->firstOrFail();
                $cities = City::where('state_id', $state->id)->get();
            } else {
                $cities = [];
            }
        } else {
            $states = [];
            $cities = [];
        }

        return view('profile.profile-info', compact('user', 'title', 'countries', 'states', 'cities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request)
    {
        //get inputs
        $firstname = $request->input('firstname');
        $lastname = $request->input('lastname');
        $email = $request->input('email');
        $job = $request->input('job');
        $country = $request->input('country');
        $county = $request->input('county');
        $city = $request->input('city');
        $description = $request->input('description');
        $social_status = $request->input('social_status');
        if ($request->hasFile('background_image')) {
            $backgroundImagePath = $request->file('background_image')->store('/images/users/background_images', 'public');
        }

        $validator = \Validator::make($request->all(), [
            'phone' => 'numeric',
            'datetimepicker' => 'nullable|date',
        ],
            [
                'datetimepicker.date' => 'The date entered for your birthday is not a valid date.' // custom message
            ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $phone = $request->input('phone');
        if ($request->input('datetimepicker') != "") {
            $birthday = date_create_from_format('d/m/Y', $request->input('datetimepicker'));
        } else {
            $birthday = null;
        }

        //select user
        $user = User::where('id', Auth::id())->firstOrFail();

        //save all
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->phone = $phone;
        $user->birthday = $birthday;
        $user->job = $job;
        $user->country = $country;
        $user->county = $county;
        $user->city = $city;
        $user->description = $description;
        $user->social_status = $social_status;
        if ($request->hasFile('background_image')) {
            $user->background_image = $backgroundImagePath;
        }
        $user->save();

        return redirect('/profile-settings');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        $user = User::where('id', Auth::id())->firstOrFail();
        if ($id == 'online' || $id == 'away' || $id == 'disconected' || $id == 'status-invisible') {
            $user->status = $id;
            $user->save();
        }

        return response()->json($user->status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function moto(Request $request)
    {
        $moto = $request->moto;

        $user = User::where('id', Auth::id())->firstOrFail();
        $user->moto = $moto;
        $user->save();

        return response()->json($moto);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function image(Request $request)
    {
        $user_id = Auth::id();
        $image = $request->image;
        $image_ext = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
        $image_name = rand() . '_' . rand() . '_' . $user_id . '.' . $image_ext;

        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);
        Storage::put('public/images/' . $image_name, $image);
        if ($request->role == "profile") {
            $usr = User::where('id', Auth::id())->firstOrFail();
            $usr->profile_image = $image_name;
            $usr->save();
        }
        if ($request->role == "cover") {
            $usr = User::where('id', Auth::id())->firstOrFail();
            $usr->cover_image = $image_name;
            $usr->save();
        }
        $img = new ImageGet;

        $img->user_id = $user_id;
        $img->name = $image_name;
        $img->role = $request->role;
        $img->save();

        $response = ['route' => '/profile'];

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function search(Request $request)
    {
        $search = $request->search;
        $tpl = "";
        if ($search != "") {
            $users = User::where('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->take(6)->get();

            $tpl = '<div class="selectize-dropdown-content">';

            foreach ($users as $user) {
                if ($user->id != Auth::id() && !in_array($user->id, Auth::user()->allBlocked())) {
                    if ($user->gender == 'male' && Auth::user()->isAdmin() || $user->gender == 'female' && Auth::user()->role != 'editor') {
                        $tpl .= '<a href="/profile/' . $user->username . '"><div class="inline-items">
                        <div class="author-thumb"><img src="/storage/images/' . $user->profile_image() . '" alt="avatar"></div>
                        <div class="notification-event"><span class="h6 notification-friend"><span class="highlight">' . $user->firstname . '</span> ' . $user->lastname . '</span>';
                        switch ($user->social_status) {
                            case 'single':
                                $status = 'Single';
                                break;
                            case 'relationship':
                                $status = 'In a relationship';
                                break;
                            case 'married':
                                $status = 'Married';
                                break;
                            case 'dating':
                                $status = 'Dating';
                                break;
                            case 'complicated':
                                $status = 'It\'s complicated';
                                break;
                            default:
                                $status = '';
                        }
                        $tpl .= '   <span class="chat-message-item">' . $status . '</span></div>';

                        $tpl .= '</div></a>';
                    }
                }
            }

            $tpl .= '</div>';

        }

        return response()->json($tpl);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function getField(Request $request)
    {
        if ($request->field == 'state') {

            $states = States::where('country_id', $request->option)->select(['name', 'id'])->get();
            $response = '';
            foreach ($states as $state) {
                $response .= '<option data-state="' . $state->id . '" value="' . $state->name . '">' . $state->name . '</option>';
            }

        }
        if ($request->field == 'city') {

            $cities = City::where('state_id', $request->option)->select(['name', 'id'])->get();

            $response = '';
            foreach ($cities as $city) {
                $response .= '<option value="' . $city->name . '">' . $city->name . '</option>';
            }

        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function getOnline(Request $request)
    {
        $n = $request->count;
        $skip = $request->skip;
        if ($n == 'all') {
            if (Auth::check()) {
                if (Auth::user()->isAdmin()) {
                    $users = User::select('id', 'firstname', 'lastname')->where('gender', 'male')->where('status', 'online')->orderBy('created_at', 'desc')->get();
                } else {
                    $users = User::select('id', 'firstname', 'lastname')->where('gender', 'female')->orderBy('created_at', 'desc')->get();
                }
            } else {
                $users = User::where('gender', 'female')->orderBy('created_at', 'desc')->skip($skip)->take($n)->get();
            }
        } else {
            if (Auth::check()) {
                if (Auth::user()->isAdmin()) {
                    $users = User::where('gender', 'male')->where('status', 'online')->orderBy('created_at', 'desc')->skip($skip)->take($n)->get();
                } else {
                    $users = User::where('gender', 'female')->orderBy('created_at', 'desc')->skip($skip)->take($n)->get();
                }
            } else {
                $users = User::where('gender', 'female')->orderBy('created_at', 'desc')->skip($skip)->take($n)->get();
            }
        }
        if ($n == 'all') {
            $user_arr = array();
            foreach ($users as $usr) {
                $user_arr[$usr->id]['name'] = $usr->name();
            }
        } else {
            $tpl = '';
            foreach ($users as $usr) {
                $tpl .= '<a href="/profile/' . $usr->username . '"';
                if (Auth::check()) {
                    $tpl .= ' onclick="chat_open(this,event);"';
                }
                $tpl .= ' data-id="' . $usr->id . '">
                            <div class="inline-items">
                                <div class="author-thumb">
                                    <img src="/storage/images/' . $usr->profile_image() . '" alt="avatar">
                                </div>
                                <div class="notification-event">
                                    <span class="h6 notification-friend">
                                        <span class="highlight">' . $usr->firstname . '</span> ' . $usr->lastname . '
                                    </span>
                                    <span class="chat-message-item"></span>
                                </div>
                            </div>
                        </a>';
            }
        }

        if ($n == 'all') {
            return response()->json($user_arr);
        } else {
            return response()->json($tpl);
        }


    }

    public function delete_profile_image($id)
    {
        if (Auth::user()->isAdmin()) {
            $profile_image = ImageGet::where('user_id', $id)->where('role', '=', 'profile')->delete();
            $usr = User::where('id', $id)->firstOrFail();
            $usr->profile_image = NULL;
            $usr->save();
        }

        return back();
    }

}
