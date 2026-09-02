<?php

namespace App\Http\Controllers;

use App\Client;
use App\Email;
use App\EmailTracking;
use App\Newsletter as NewsletterQueue;
use App\Mail\Newsletter;
use Illuminate\Support\Facades\Mail;
use App\Settings;
use App\Unsubscribe;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function index(Request $request, $id)
    {
        $unsubscribed = Unsubscribe::all();
        $unsubscribed = $unsubscribed->pluck('email')->toArray();
        if (Auth::user()->isAdmin()) {
            if ($request->ajax()) {
                if (Auth::user()->role == 'editor') {
                    if ($id == 'users') {
                        //$users= User::leftJoin('clients', 'users.email','=', 'clients.email')->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->where('admin_id', Auth::id())->whereNotIn('clients.email', $unsubscribed)->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
                        $users= User::where('email', 'not like', '%@'.request()->getHttpHost())->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
                        $clients = '';
                    }
                    if ($id == 'clients') {
                        $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->get();
                        $users2 = $users2->pluck('email')->toArray();
                        $clients = Client::where('email', 'not like', '%@'.request()->getHttpHost())->where('admin_id', Auth::id())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->paginate(20);
                        $users = '';
                    }
                } else {
                    if ($id == 'users') {
                        $users = User::where('email', 'not like', '%@'.request()->getHttpHost())->where('role', 'user')->where('banned', 'no')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->paginate(20);
                        $clients = '';
                    }
                    if ($id == 'clients') {
                        $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->get();
                        $users2 = $users2->pluck('email')->toArray();
                        $clients = Client::where('email', 'not like', '%@'.request()->getHttpHost())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->paginate(20);
                        $users = '';
                    }
                }
                if ($id == 'users') {
                    $tpl_users = '';
                    if ($request->page) {
                        $x = ($request->page - 1) * 20 + 1;
                    } else {
                        $x = 1;
                    }
                    foreach ($users as $user) {
                        $tpl_users .= '<tr>
                                                    <td>' . $x . '</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>' . $user->name() . '</h6>
                                                            <span>
                                                                <a href="#">' . $user->email . '</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>';
                        if ($user->package()) {
                            $tpl_users .= '<span class="role user">' . $user->package()->name . '</span>';
                        } else {
                            $tpl_users .= '<span class="role user">Nu</span>';
                        }
                        $tpl_users .= '</td>
                                                    <td>
                                                        <span class="role user">' . $user->credits . '</span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>' . $user->created_at->format('d/m/Y') . '</h6>
                                                        </span>
                                                    </td>
                                                    <td>';
                        if (in_array($user->id, $request->ids)) {
                            $tpl_users .= '<a class="del_dest" onclick="del_dest(' . $user->id . ',event);" data-id="' . $user->id . '" data-name="' . $user->name() . '" href="#"><i class="fas fa-minus"></i></a>';
                        } else {
                            $tpl_users .= '<a class="add_dest" onclick="add_dest(' . $user->id . ',event);" data-id="' . $user->id . '" data-name="' . $user->name() . '" href="#"><i class="fas fa-plus"></i></a>';
                        }

                        $tpl_users .= '</td>
                                                </tr>';
                        $x++;
                    }
                    return response()->json($tpl_users);
                }
                if ($id == 'clients') {
                    $tpl_users = '';
                    if ($request->page) {
                        $x = ($request->page - 1) * 20 + 1;
                    } else {
                        $x = 1;
                    }
                    foreach ($clients as $user) {
                        $tpl_users .= '<tr>
                                                    <td>' . $x . '</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>' . $user->name . '</h6>
                                                            <span>
                                                                <a href="#">' . $user->email . '</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td><span class="role user">Nu</span>
                                                    <td>
                                                        <span class="role user">0</span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>' . $user->created_at->format('d/m/Y') . '</h6>
                                                        </span>
                                                    </td>
                                                    <td>';
                        if (in_array($user->id, $request->ids)) {
                            $tpl_users .= '<a class="del_dest" onclick="del_dest(' . $user->id . ',event);" data-id="' . $user->id . '" data-name="' . $user->name . '" href="#"><i class="fas fa-minus"></i></a>';
                        } else {
                            $tpl_users .= '<a class="add_dest" onclick="add_dest(' . $user->id . ',event);" data-id="' . $user->id . '" data-name="' . $user->name . '" href="#"><i class="fas fa-plus"></i></a>';
                        }

                        $tpl_users .= '</td>
                                                </tr>';
                        $x++;
                    }
                    return response()->json($tpl_users);
                }
            } else {
                $on_page = 'Newsletter';
                if (Auth::user()->role == 'editor') {
                    if ($id == 'users') {
                        //$users= User::leftJoin('clients', 'users.email','=', 'clients.email')->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->where('admin_id', Auth::id())->whereNotIn('clients.email', $unsubscribed)->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
                        $users= User::where('email', 'not like', '%@'.request()->getHttpHost())->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->paginate(20);
                        $clients = '';
                        //$all_usr = User::leftJoin('clients', 'users.email','=', 'clients.email')->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->where('admin_id', Auth::id())->whereNotIn('clients.email', $unsubscribed)->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->get();
                        $all_usr = User::where('email', 'not like', '%@'.request()->getHttpHost())->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->orderBy('users.created_at', 'desc')->get();
                        $all_users_names = array();
                        foreach ($all_usr as $u) {
                            $all_users_names[] = [$u->id, $u->name()];
                        }
                        $all_clients_names = '';
                    }
                    if ($id == 'clients') {
                        $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->get();
                        $users2 = $users2->pluck('email')->toArray();
                        $clients = Client::where('email', 'not like', '%@'.request()->getHttpHost())->where('admin_id', Auth::id())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->paginate(20);
                        $users = '';
                        $all_cln = Client::where('email', 'not like', '%@'.request()->getHttpHost())->where('admin_id', Auth::id())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->get();
                        $all_clients_names = array();
                        foreach ($all_cln as $u) {
                            $all_clients_names[] = [$u->id, $u->name];
                        }
                        $all_users_names = '';
                    }
                } else {
                    if ($id == 'users') {
                        $users = User::where('email', 'not like', '%@'.request()->getHttpHost())->where('banned', 'no')->where('gender', 'male')->orderBy('created_at', 'desc')->paginate(20);
                        $clients = '';
                        $all_usr = User::where('email', 'not like', '%@'.request()->getHttpHost())->where('banned', 'no')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->get();
                        $all_users_names = array();
                        foreach ($all_usr as $u) {
                            $all_users_names[] = [$u->id, $u->name()];
                        }
                        $all_clients_names = '';
                    }
                    if ($id == 'clients') {
                        $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->whereNotIn('email', $unsubscribed)->get();
                        $users2 = $users2->pluck('email')->toArray();
                        $clients = Client::where('email', 'not like', '%@'.request()->getHttpHost())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->paginate(20);
                        $users = '';
                        $all_cln = Client::where('email', 'not like', '%@'.request()->getHttpHost())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->orderBy('created_at', 'desc')->get();
                        $all_clients_names = array();
                        foreach ($all_cln as $u) {
                            $all_clients_names[] = [$u->id, $u->name];
                        }
                        $all_users_names = '';
                    }
                }

                $send_from = Auth::user()->getAccounts();
                $send_from = $send_from->load('getUserRelationship');

                return view('admin.newsletter', compact('clients', 'users', 'on_page', 'send_from', 'all_users_names', 'all_clients_names'));
            }
        } else {
            return redirect('/profile');
        }

    }

    private function createNewsletterQueue($email, $data)
    {
        $newsletter = new NewsletterQueue;
        $newsletter->email = $email;
        $newsletter->data = serialize($data);
        $newsletter->save();

        return $newsletter;
    }
    
    public function sendNewsletterQueue($count)
    {
        if($count){
            $mails = NewsletterQueue::where('sent', false)->take((int)$count)->get();
            
            foreach($mails as $mail){
                Mail::to($mail->email)->send(new Newsletter(unserialize($mail->data)));
                
                //$mail->sent = true;
                //$mail->save();
                $mail->delete();
                
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function create(Request $request)
    {
        $unsubscribed = Unsubscribe::all();
        $unsubscribed = $unsubscribed->pluck('email')->toArray();
        $dests = $request->newsletter_dest;
        $header = $request->header;
        $exp = $request->from;

        $user = User::where('id', $exp)->firstOrFail();

        $text = $request->mail_message;

        $data = array();
        $data['header'] = $header;
        $data['name'] = $user->name();
        $data['text'] = $text;
        $data['cover_img'] = $user->cover_image();
        $data['username'] = $user->username;
        if (Auth::user()->adminReplyEmail && Auth::user()->adminReplyEmail->email != '') {
            $data['reply_email'] = Auth::user()->adminReplyEmail->email;
        }

        $email_tracking = new Email;
        $email_tracking->subject = $header;
        $email_tracking->message = $text;
        $email_tracking->from_user = $exp;
        $email_tracking->website = $_SERVER['SERVER_NAME'];

        $email_tracking->save();

        if ($request->option == 'users') {
            if ($request->submit == 'send_all') {
                if (Auth::user()->role == 'editor') {
                    $clients = Client::select('email')->where('admin_id', Auth::id())->get();
                    $clients = $clients->pluck('email')->toArray();
                    // $users = User::where('role', 'user')->where('banned', 'no')->where('gender', 'male')->whereIn('email', $clients)->whereNotIn('email', $unsubscribed)->latest()->get();
                    $users= User::where('email', 'not like', '%@'.request()->getHttpHost())->where('role', 'user')->where('banned', 'no')->where('users.gender', 'male')->whereNotNull('users.email')->get();
                } else {
                    $users = User::where('email', 'not like', '%@'.request()->getHttpHost())->where('gender', 'male')->where('banned', 'no')->whereNotIn('email', $unsubscribed)->latest()->get();
                }

                $local_emails = Email::whereDate('created_at', Carbon::today())->where('website', $_SERVER['SERVER_NAME'])->select('id')->get();
                $local_emails = $local_emails->pluck('id');
                $email_local = EmailTracking::whereIn('email_id', $local_emails)->count();
                $limit = Settings::where('id', 23)->firstOrFail();
                foreach ($users as $usr) {
                    if (!filter_var($usr->email, FILTER_VALIDATE_EMAIL)) {
                        $unsubscribe_user = new Unsubscribe;
                        $unsubscribe_user->email = $usr->email;
                        $unsubscribe_user->save();
                    } else {
                        if ($email_local < (int) $limit->value) {
                            $data['lang'] = $usr->lang;
                            $email = $usr->email;
                            $data['email'] = $email;

                            $user_tracking = new EmailTracking;
                            $user_tracking->email = $email;
                            $user_tracking->email_id = $email_tracking->id;
                            $user_tracking->tracking = md5(uniqid() . $email . $email_tracking->id);

                            $user_tracking->save();

                            $data['tracking'] = $user_tracking->tracking;

                            $data['text'] = str_replace('_nume_', $usr->name(), $data['text']);
                            $data['header'] = str_replace('_nume_', $usr->name(), $data['header']);
                            $email_local++;
                            $this->createNewsletterQueue($email, $data);

                        } else {
                            return redirect('/admin/newsletter/users');
                        }
                    }
                }
            } else {
                $local_emails = Email::whereDate('created_at', Carbon::today())->where('website', $_SERVER['SERVER_NAME'])->select('id')->get();
                $local_emails = $local_emails->pluck('id');
                $email_local = EmailTracking::whereIn('email_id', $local_emails)->count();
                $limit = Settings::where('id', 23)->firstOrFail();
                foreach ($dests as $dest) {
                    $usr = User::where('id', $dest)->whereNotIn('email', $unsubscribed);
                    if ($usr->exists()) {

                        $usr = $usr->firstOrFail();
                        if (!filter_var($usr->email, FILTER_VALIDATE_EMAIL)) {
                            $unsubscribe_user = new Unsubscribe;
                            $unsubscribe_user->email = $usr->email;
                            $unsubscribe_user->save();
                        } else {
                            if ($email_local < (int) $limit->value) {
                                $data['lang'] = $usr->lang;
                                $email = $usr->email;
                                $data['email'] = $email;

                                $user_tracking = new EmailTracking;
                                $user_tracking->email = $email;
                                $user_tracking->email_id = $email_tracking->id;
                                $user_tracking->tracking = md5(uniqid() . $email . $email_tracking->id);

                                $user_tracking->save();

                                $data['tracking'] = $user_tracking->tracking;

                                $data['text'] = str_replace('_nume_', $usr->name(), $data['text']);
                                $data['header'] = str_replace('_nume_', $usr->name(), $data['header']);
                                $email_local++;
                                $this->createNewsletterQueue($email, $data);

                            } else {
                                return redirect('/admin/newsletter/users');
                            }
                        }
                    }
                }
            }
        }
        if ($request->option == 'clients') {
            if ($request->submit == 'send_all') {
                if (Auth::user()->role == 'editor') {
                    $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->get();
                    $users2 = $users2->pluck('email')->toArray();
                    $users = Client::where('email', 'not like', '%@'.request()->getHttpHost())->where('admin_id', Auth::id())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->latest()->get();
                } else {
                    $users2 = User::select('email')->where('role', 'user')->where('gender', 'male')->get();
                    $users2 = $users2->pluck('email')->toArray();
                    $users = Client::where('email', 'not like', '%@'.request()->getHttpHost())->whereNotIn('email', $users2)->whereNotIn('email', $unsubscribed)->latest()->get();
                }
                $local_emails = Email::whereDate('created_at', Carbon::today())->where('website', $_SERVER['SERVER_NAME'])->select('id')->get();
                $local_emails = $local_emails->pluck('id');
                $email_local = EmailTracking::whereIn('email_id', $local_emails)->count();
                $limit = Settings::where('id', 23)->firstOrFail();
                foreach ($users as $usr) {
                    if (!filter_var($usr->email, FILTER_VALIDATE_EMAIL)) {
                        $unsubscribe_user = new Unsubscribe;
                        $unsubscribe_user->email = $usr->email;
                        $unsubscribe_user->save();
                    } else {
                        if ($email_local < (int) $limit->value) {
                            $data['lang'] = 'it';
                            $email = $usr->email;
                            $data['email'] = $email;

                            $user_tracking = new EmailTracking;
                            $user_tracking->email = $email;
                            $user_tracking->email_id = $email_tracking->id;
                            $user_tracking->tracking = md5(uniqid() . $email . $email_tracking->id);

                            $user_tracking->save();

                            $data['tracking'] = $user_tracking->tracking;

                            $data['text'] = str_replace('_nume_', $usr->name, $data['text']);
                            $data['header'] = str_replace('_nume_', $usr->name, $data['header']);
                            $email_local++;
                            $this->createNewsletterQueue($email, $data);

                        } else {
                            return redirect('/admin/newsletter/users');
                        }
                    }
                }
            } else {
                $local_emails = Email::whereDate('created_at', Carbon::today())->where('website', $_SERVER['SERVER_NAME'])->select('id')->get();
                $local_emails = $local_emails->pluck('id');
                $email_local = EmailTracking::whereIn('email_id', $local_emails)->count();
                $limit = Settings::where('id', 23)->firstOrFail();
                foreach ($dests as $dest) {
                    $usr = Client::where('id', $dest)->whereNotIn('email', $unsubscribed);
                    if ($usr->exists()) {

                        $usr = $usr->firstOrFail();
                        if (!filter_var($usr->email, FILTER_VALIDATE_EMAIL)) {
                            $unsubscribe_user = new Unsubscribe;
                            $unsubscribe_user->email = $usr->email;
                            $unsubscribe_user->save();
                        } else {
                            if ($email_local < (int) $limit->value) {
                                $data['lang'] = 'it';
                                $email = $usr->email;
                                $data['email'] = $email;

                                $user_tracking = new EmailTracking;
                                $user_tracking->email = $email;
                                $user_tracking->email_id = $email_tracking->id;
                                $user_tracking->tracking = md5(uniqid() . $email . $email_tracking->id);

                                $user_tracking->save();

                                $data['tracking'] = $user_tracking->tracking;

                                $data['text'] = str_replace('_nume_', $usr->name, $data['text']);
                                $data['header'] = str_replace('_nume_', $usr->name, $data['header']);
                                $email_local++;
                                $this->createNewsletterQueue($email, $data);
                            } else {
                                return redirect('/admin/newsletter/users');
                            }
                        }
                    }
                }
            }
        }
        return redirect('/admin/newsletter/users');
    }
}
