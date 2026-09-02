<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use App\Settings;
use App\AISetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\NewMessage;
use Pusher\Pusher;
use App\Client;
use Cookie;
use Illuminate\Support\Facades\Log;
use App\Services\AI\SimliService;
use App\Services\AI\PersonaPromptBuilder;
use App\Services\AdminAlertService;
use Throwable;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function get(Request  $request)
    {
        $to = $request->to;
        if(Auth::user()->isAdmin()){
          if($request->from != 'no'){
            $user = $request->from;
          }else{
            $user = Auth::id();
          }
        }else{
        $user = Auth::id();
        }
        $to_data = User::where('id', $to)->firstOrFail();
        $user_from = User::where('id', $user)->firstOrFail();
        $messages = Message::orderBy('id', 'desc')->where(function($query) use ($to, $user)
                          {
                              $query->where("from_user",$user)
                                    ->where("to_user",$to);
                          })
                          ->orWhere(function($query) use ($to, $user)
                          {
                              $query->where("from_user",$to)
                                    ->where("to_user",$user);
                          })->select('id', 'from_user', 'to_user', 'message', 'status', 'created_at')->take(15)->get();
        $msg = array();
        $i = $messages->count();
        $unread = 0;
        while($i>0) {
          if($messages[$i-1]->from_user != $user){
            if(Auth::user()->isAdmin()){
              $translated_message = translate($messages[$i-1]->message, Auth::user()->lang);
              if($translated_message != $messages[$i-1]->message){
                $messages[$i-1]->message = $translated_message.' <br> Original:'.$messages[$i-1]->message;
              }
            }else{
              $messages[$i-1]->message = translate($messages[$i-1]->message, $user_from->lang);
            }
          }else{
            if(Auth::user()->isAdmin()){
              $translated_message = translate($messages[$i-1]->message, $to_data->lang);
              if($translated_message != $messages[$i-1]->message){
                $messages[$i-1]->message = $messages[$i-1]->message.' <br> Traducere:'.$translated_message;
              }
            }
          }
          $msg[] = $messages[$i-1];
          if($messages[$i-1]->status != 1 && $messages[$i-1]->from_user != Auth::id()){
          $messages[$i-1]->update(['status' => 1]);
          $unread++;
          }
          $i--;
        }
        $bg_image = '';
        if($to_data->images->count() > 0){
          $bg_image = $to_data->images->take(1)[0]->name;
        }
        // $user_from is the identity acting in this popup (admin's own account, or the
        // managed female profile picked via the account-switcher) — the AI pause state is
        // only meaningful when that acting identity is an AI-enabled female profile.
        $ai_paused = null;
        if(Auth::user()->isAdmin() && $user_from->gender == 'female' && $user_from->ai_enabled){
          $ai_paused = (bool) $user_from->chat($to)->ai_paused;
        }
        // Live AI video call button: only offered to a real (non-admin) user chatting with
        // an AI-enabled female profile that has a Simli Face configured, while the feature is
        // switched on and this specific conversation isn't currently paused for admin takeover.
        $ai_video_available = false;
        if (! Auth::user()->isAdmin() && $to_data->gender == 'female' && $to_data->ai_enabled) {
          $ai_video_available = AISetting::current()->liveAiVideoEnabled()
              && trim((string) $to_data->simli_face_id) !== ''
              && ! (bool) $to_data->chat(Auth::id())->ai_paused;
        }
        // Same package-tier privacy rule as the live AI video call applied to the chat
        // popup's own background photo/video — admins always see it unblurred (they're
        // managing the account, not a paying viewer).
        $background_blur_amount = Auth::user()->isAdmin()
            ? 0
            : AISetting::current()->resolveVideoPrivacyForUser(Auth::user())['amountPx'];
        return response()->json(['user_name' => $to_data->name(), 'user_img' => $to_data->profile_image(), 'auth_img' => $user_from->profile_image(), 'msg' => $msg, 'unread' => $unread, 'bg_image' => $bg_image, 'lang' => $to_data->lang, 'video' => $to_data->video, 'ai_paused' => $ai_paused, 'ai_video_available' => $ai_video_available, 'background_blur_amount' => $background_blur_amount]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function load(Request  $request)
    {
        $to = $request->to;
        if(Auth::user()->isAdmin()){
          if($request->from != 'no'){
            $user = $request->from;
          }else{
            $user = Auth::id();
          }
        }else{
        $user = Auth::id();
        }
        $to_data = User::where('id', $to)->firstOrFail();
        $user_from = User::where('id', $user)->firstOrFail();
        $messages = Message::orderBy('id', 'desc')->where(function($query) use ($to, $user)
                          {
                              $query->where("from_user",$user)
                                    ->where("to_user",$to);
                          })
                          ->orWhere(function($query) use ($to, $user)
                          {
                              $query->where("from_user",$to)
                                    ->where("to_user",$user);
                          })->select('id', 'from_user', 'to_user', 'message', 'status', 'created_at')->offset($request->msg_count)->limit(15)->get();
        $msg = array();
        $i = $messages->count();
        $unread = 0;
        while($i>0) {
          if($messages[$i-1]->from_user != $user){
            if(Auth::user()->isAdmin()){
              $translated_message = translate($messages[$i-1]->message, $user_from->lang);
              if($translated_message != $messages[$i-1]->message){
                $messages[$i-1]->message = $translated_message.' <br> Original:'.$messages[$i-1]->message;
              }
            }else{
              $messages[$i-1]->message = translate($messages[$i-1]->message, $user_from->lang);
            }
          }else{
            if(Auth::user()->isAdmin()){
              $translated_message = translate($messages[$i-1]->message, $to_data->lang);
              if($translated_message != $messages[$i-1]->message){
                $messages[$i-1]->message = $messages[$i-1]->message.' <br> Traducere:'.$translated_message;
              }
            }
          }
          $msg[] = $messages[$i-1];
          if($messages[$i-1]->status != 1 && $messages[$i-1]->from_user != Auth::id()){
          $messages[$i-1]->update(['status' => 1]);
          $unread++;
          }
          $i--;
        }
        return response()->json(['user_name' => $to_data->name(), 'user_img' => $to_data->profile_image(), 'auth_img' => $user_from->profile_image(), 'msg' => $msg, 'unread' => $unread, 'video' => $to_data->video]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function send(Request $request)
    {
      if(Auth::user()->hasPermission('chat')){
        $text = $request->text;
        if($text != "" && !in_array($request->to, Auth::user()->allBlocked())){
          $canSendMessage = false;
          $chatOnCredits = Settings::where('id', 24)->firstOrFail();
          if($chatOnCredits->value != 'yes'){
            $messages_per_day = Settings::where('id', 10)->firstOrFail();
            $messages_sent_today = Message::where('from_user', Auth::id())->whereDate('created_at', Carbon::today())->count();
            if(!Auth::user()->paidPackage() && $messages_sent_today < $messages_per_day->value || Auth::user()->paidPackage() || Auth::user()->isAdmin()){
              $canSendMessage = true;
            }
          }else{
            if(Auth::user()->credits > 0 || Auth::user()->isAdmin()){
              $canSendMessage = true;
            }
          }
          if($canSendMessage){
        // An admin explicitly picking a managed account via the account-switcher means
        // they're manually taking over this conversation — pause the AI auto-reply for it
        // (see ChatBotController@response) so it doesn't answer over the admin.
        $adminTookOver = false;
        if(Auth::user()->isAdmin()){
          if($request->from != 'no'){
            $from = $request->from;
            $adminTookOver = true;
          }else{
            $from = Auth::id();
          }
        }else{
        $from = Auth::id();
        }
        $to = $request->to;
        $user_from = User::where('id', $from)->firstOrFail();
        $user_to = User::where('id', $to)->firstOrFail();
        $msg = new Message;
        $msg->from_user = $from;
        $msg->to_user = $to;
        $msg->message = html_entity_decode($text);
        $msg->save();
        if(!Auth::user()->isAdmin() && Auth::user()->allMessagesCount() <=5 && isset($_COOKIE['autoregister_fake'])){
          $found_emails = extract_emails($text);
          if(count($found_emails) > 0){
            $check_change_user = User::where('email', $found_emails[0]);
            if(!$check_change_user->exists()){
              $change_email = User::where('id', Auth::id())->firstOrFail();
              $change_email->email = strval($found_emails[0]);
              $change_email->save();
              Cookie::queue(Cookie::forget('autoregister_fake'));
              Cookie::queue(Cookie::forever('autoregister', 1));
            }
          }
        }elseif(!Auth::user()->isAdmin()){
          $found_emails = extract_emails($text);
          if(count($found_emails) > 0){
            $check_change_client = Client::whereIn('email', $found_emails);
            if(!$check_change_client->exists()){
              $admin_for_client = User::where('role', 'admin')->firstOrFail();
              foreach($found_emails as $add_email) {
                $add_client = new Client;
                $name = explode('@', $add_email);
                $add_client->admin_id = $admin_for_client->id;
                $add_client->name = $name[0];
                $add_client->email = $add_email;
                $add_client->source = 'Mesaje';
                $add_client->save();
              }
            }
          }
        }
        $chat = $user_from->chat($to);
        $chat->last_msg = $msg->id;
        if($adminTookOver){
          $chat->ai_paused = true;
          $chat->ai_paused_at = now();
        }
        $chat->save();
        $message[] = Message::where('id', $msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail();
        $message_translated[] = Message::where('id', $msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail();
        if(isset($request->translate) && $request->translate == 'no'){
          $message_translated[0]->message = $message_translated[0]->message;
        }else{
          $message_translated[0]->message = translate($message_translated[0]->message, $user_to->lang);
        }
        if($user_to->isAdmin()){
          if($message_translated[0]->message != $message[0]->message){

            $new_translated_msg = $message_translated[0]->message.' <br> Original:'.$message[0]->message;
            $message_translated[0]->message = $new_translated_msg;

          }
        $response = ['check_adm' => 'true', 'to_username' => $user_to->name(), 'user_name' => $user_from->name(), 'user_img' => $user_from->profile_image(), 'auth_img' => "", 'msg' => $message_translated, 'to_user' => $to, 'to_user_account' => $to];
        }else{
          $response = ['user_name' => $user_from->name(), 'user_img' => $user_from->profile_image(), 'auth_img' => "", 'msg' => $message_translated, 'to_user' => $to];
        }
        try {
          event(new NewMessage($response));
        } catch (\Throwable $e) {
          Log::error('Broadcast failed in ChatController@send (primary recipient).', [
            'from_user' => $from,
            'to_user' => $to,
            'exception' => $e->getMessage(),
          ]);
        }
        foreach($message[0]->userTo()->accountAdmins() as $adm){
          $message_translated[0]->message = translate($message[0]->message, $adm->lang);
          $new_translated_msg = $message_translated[0]->message.' <br> Original:'.$message[0]->message;
          $message_translated[0]->message = $new_translated_msg;
          $response = ['check_adm' => 'true', 'to_username' => $user_to->name(), 'user_name' => $user_from->name(), 'user_img' => $user_from->profile_image(), 'auth_img' => "", 'msg' => $message_translated, 'to_user' => $adm->id, 'to_user_account' => $to];
          try {
            event(new NewMessage($response));
          } catch (\Throwable $e) {
            Log::error('Broadcast failed in ChatController@send (admin mirror).', [
              'from_user' => $from,
              'to_user' => $adm->id,
              'exception' => $e->getMessage(),
            ]);
          }
        }
        if(!Auth::user()->isAdmin()){
          $usrcr = User::where('id', Auth::id())->firstOrFail();
          if($chatOnCredits->value == 'yes'){
            $usrcr->credits = $usrcr->credits-1;
            $usrcr->save();
          }
          $res = ['user_name' => $message[0]->userTo()->name(), 'user_img' => $message[0]->userTo()->profile_image(), 'auth_img' => $user_from->profile_image(), 'msg' => $message, 'credits' => $usrcr->credits];
        }else{
          if($message_translated[0]->message != $message[0]->message){

            $new_translated_msg = $message[0]->message.' <br> Traducere:'.$message_translated[0]->message;
            $message[0]->message = $new_translated_msg;

          }
          $res = ['check_adm' => 'true', 'to_username' => $user_from->name(), 'user_name' => $message[0]->userTo()->name(), 'user_img' => $message[0]->userTo()->profile_image(), 'auth_img' => $user_from->profile_image(), 'msg' => $message, 'to_user_account' => $to];
        }

        return response()->json($res);
      }else{
          return response()->json('/packages');
      }
        }else{
          return response()->json(['user_name' => "", 'user_img' => "", 'auth_img' => "", 'msg' => ""]);
        }
      }else{
        return response()->json('/packages');
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function auth(Request $request)
    {
        $options = array(
            'cluster' => 'eu',
            'encrypted' => true
          );
          $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
          );
        $socket_id = $request->socket_id;
        $channel_name = $request->channel_name;
        if($channel_name == 'presence-online'){
          $auth = $pusher->presence_auth($channel_name, $socket_id, Auth::id(), array('name' => Auth::user()->name()));
          return $auth;
        }else{
          $id = explode(".", $channel_name);
          if($id[1] != Auth::id()){
          return false;
          }else{
          $auth = $pusher->socket_auth($channel_name, $socket_id);
          return $auth;
          }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function online(Request $request)
    {
      $app_secret = getenv('PUSHER_APP_SECRET');

      $app_key = $_SERVER['HTTP_X_PUSHER_KEY'];
      $webhook_signature = $_SERVER ['HTTP_X_PUSHER_SIGNATURE'];

      $body = file_get_contents('php://input');

      $expected_signature = hash_hmac( 'sha256', $body, $app_secret, false );

      if($webhook_signature == $expected_signature) {
        // decode as associative array
        $payload = json_decode( $body, true );

        header("Status: 200 OK");
      }
      else {
        header("Status: 401 Not authenticated");
      }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function seen(Request $request)
    {
        $id = $request->id;
        if(Auth::user()->isAdmin()){
          if($request->from != 'no'){
            $from = $request->from;
          }else{
            $from = Auth::id();
          }
        }else{
        $from = Auth::id();
        }
        $usr = User::where('id', $from)->firstOrFail();
        $messages = Message::where('to_user', $usr->id)->where('from_user', $id)->where('status', 0)->get();

        foreach ($messages as $message) {
          $message->status = 1;
          $message->save();
        }

        return response()->json(['counter' => $messages->count(), 'user_name' => $usr->name()]);
    }

    /**
     * Admin-only: flips the AI auto-reply pause state for one conversation (a managed
     * female profile <-> a counterpart), so staff can resume AI replies after manually
     * taking over, without having to send another message.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function toggleAi(Request $request)
    {
        if(!Auth::user()->isAdmin()){
          return response()->json(['message' => 'Forbidden'], 403);
        }

        $to = $request->to;
        $from = $request->from != 'no' ? $request->from : Auth::id();
        $user_from = User::where('id', $from)->firstOrFail();

        $chat = $user_from->chat($to);
        $chat->ai_paused = !$chat->ai_paused;
        $chat->ai_paused_at = $chat->ai_paused ? now() : null;
        $chat->save();

        return response()->json(['ai_paused' => (bool) $chat->ai_paused]);
    }

    /**
     * Starts a live Simli AI video call with a real (non-catalog) female profile — the
     * "Live AI Video Call" feature, fully separate from the AI Companions (AIProfile) live
     * video session in AIChatController::startVideoSession(). Any authenticated user can
     * call any ai_enabled female profile that has a Simli Face ID configured, unless that
     * profile's conversation with them specifically is currently ai_paused (admin took over).
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function startAiVideoSession(Request $request, SimliService $simli, PersonaPromptBuilder $persona, AdminAlertService $adminAlert)
    {
        $validated = $request->validate([
            'to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $profile = User::where('id', $validated['to'])->where('gender', 'female')->firstOrFail();

        if (! AISetting::current()->liveAiVideoEnabled()) {
            return response()->json(['error' => 'live_ai_video_disabled', 'message' => 'Live AI video calls are not enabled right now.'], 422);
        }

        if (! $profile->ai_enabled) {
            return response()->json(['error' => 'ai_not_enabled', 'message' => 'This profile is not available for an AI call.'], 422);
        }

        if (trim((string) $profile->simli_face_id) === '') {
            return response()->json(['error' => 'not_configured', 'message' => 'This profile is not set up for video calls yet.'], 422);
        }

        $chat = $profile->chat(Auth::id());
        if ($chat->ai_paused) {
            return response()->json(['error' => 'ai_paused', 'message' => 'This profile is not available for a call right now.'], 422);
        }

        try {
            $session = $simli->createSessionForUser($profile, $persona);
        } catch (Throwable $throwable) {
            Log::error('Live AI video call failed to start.', [
                'to_user' => $profile->id,
                'caller_id' => Auth::id(),
                'exception' => $throwable->getMessage(),
            ]);

            $adminAlert->notify(
                key: 'ai_video_call_failed',
                subject: 'Live AI video call is failing',
                body: "A live AI video call failed to start for profile #{$profile->id} ({$profile->firstname}).\n\n"
                    . "This usually means the Simli API key/Face ID is invalid or Simli is unreachable — check the AI Settings admin page and this profile's Simli Face ID.\n\n"
                    . "Error:\n" . $throwable->getMessage(),
            );

            return response()->json(['error' => 'video_call_failed', 'message' => "We couldn't start the video call. Please try again in a little while."], 502);
        }

        $privacy = AISetting::current()->resolveVideoPrivacyForUser(Auth::user());

        return response()->json([
            'room_url' => $session['room_url'],
            'session_id' => $session['session_id'],
            'status' => $session['status'],
            'video_blur_amount' => $privacy['amountPx'],
            'audio_muted' => $privacy['audioMuted'],
        ]);
    }

    /**
     * Ends a live Simli AI video call started via startAiVideoSession().
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function endAiVideoSession(Request $request, SimliService $simli)
    {
        $sessionId = (string) $request->input('session_id', '');

        try {
            $simli->endSession($sessionId);
        } catch (Throwable $throwable) {
            Log::error('Failed to end live AI video call.', ['session_id' => $sessionId, 'exception' => $throwable->getMessage()]);
        }

        return response()->json(['status' => 'ended']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function status(Request $request)
    {
      // environmental variable must be set
      $app_secret = getenv('PUSHER_APP_SECRET');

      $app_key = $_SERVER['HTTP_X_PUSHER_KEY'];
      $webhook_signature = $_SERVER ['HTTP_X_PUSHER_SIGNATURE'];

      $body = file_get_contents('php://input');

      $expected_signature = hash_hmac( 'sha256', $body, $app_secret, false );

      if($webhook_signature == $expected_signature) {
        // decode as associative array
        $payload = json_decode( $body, true );
        foreach($payload['events'] as $event) {
          if($event['name'] == 'member_added'){
            $user = User::where('id', $event['user_id'])->firstOrFail();
            if($user->always_online == 'false'){
              $user->status = 'online';
            }
            if($user->role == 'admin' && $user->getAccountIds() || $user->role == 'editor' && $user->getAccountIds()){
              foreach($user->getAccountIds() as $acc){
                $usr_acc = User::where('id', $acc)->firstOrFail();
                $usr_acc->status = 'online';
                $usr_acc->save();
              }
            }
            $user->true_status = 'online';
            $user->save();
          }
          if($event['name'] == 'member_removed'){
            $user = User::where('id', $event['user_id'])->firstOrFail();

            if($user->always_online == 'false'){
              $user->status = 'disconected';
            }
            if($user->role == 'admin' && $user->getAccountIds() || $user->role == 'editor' && $user->getAccountIds()){
              foreach($user->getAccountIds() as $acc){
                $usr_acc = User::where('id', $acc)->firstOrFail();
                $usr_acc->status = 'disconected';
                $usr_acc->save();
              }
            }
            $user->true_status = 'disconected';
            $user->save();
          }
        }

        header("Status: 200 OK");
      }
      else {
        header("Status: 401 Not authenticated");
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function load_right(Request $request)
    {
      $n = 20;
      $page = $request->chats;
      $chats = Auth::user()->lastChats($n,$page);

      $tpl_sm = '';
      $tpl_lg = '';

      foreach($chats as $chat){
        $user = $chat->otherUser();
        //tpl_sm start
        $tpl_sm .= '<li data-id="'.$user->id.'" ';

          $tpl_sm .= 'data-from="'.$chat->thisUser()->id.'" ';
          $tpl_sm .= 'onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/'.$user->profile_image().'" class="avatar">
                        <span class="icon-status';

          if($user->gender == 'female'){ $tpl_sm .= ' online'; }else{ if($user->status == 'status-invisible'){ $tpl_sm .= ' disconected'; }else{ $tpl_sm .= ' '.$user->status; }}
            $tpl_sm .= '"></span>';
          if($chat->thisUser()->unreadFrom($user->id, true) != 0){
                        $tpl_sm .= '<div data-from="'.$user->id.'" class="label-avatar unread-right bg-orange">'.$chat->thisUser()->unreadFrom($user->id, true).'</div>';
          }
                    $tpl_sm .= '</div>
                </li>';

        //tpl_sm end

        //tpl_lg

        $tpl_lg .= '<li data-id="'.$user->id.'" ';

        $tpl_lg .= 'data-from="'.$chat->thisUser()->id.'" ';
        $tpl_lg .= 'onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/'.$user->profile_image().'" class="avatar">
                        <span  data="status" class="icon-status ';
                        if($user->gender == 'female'){ $tpl_lg .= 'online'; } else {$tpl_lg .= $user->status ;}
                        $tpl_lg .='"></span>';
                        if($chat->thisUser()->unreadFrom($user->id, true) != 0){
                        $tpl_lg .= '<div data-from="'.$user->id.'" class="label-avatar unread-right bg-orange">'.$chat->thisUser()->unreadFrom($user->id, true).'</div>';
                        }
                    $tpl_lg .= '</div>

                    <div class="author-status">';
                    if(Auth::user()->isAdmin()){
                        $tpl_lg .= '<a href="#" class="h6 author-name">'.$user->name().' - '.$chat->thisUser()->name().'</a>
                        <span class="status">'.$user->moto.'</span>';
                      }else{
                        $tpl_lg .= '<a href="#" class="h6 author-name">'.$user->name().'</a>
                        <span class="status">'.$user->moto.'</span>';
                      }
                    $tpl_lg .= '</div>

                    <div class="more"><a href="/block/'.$user->id.'" onclick="event.stopPropagation()">
                                </a>
                    </div>

                </li>';

      }

      foreach(Auth::user()->allFriendsNotIn(Auth::user()->allUserChatsOnly()) as $user){
        //tpl_sm start
        $tpl_sm .= '<li data-id="'.$user->id.'" ';
          $tpl_sm .= 'data-from="'.Auth::id().'" ';
          $tpl_sm .= 'onClick="chat_open(this,event);" class="inline-items js-chat-open">
                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/'.$user->profile_image().'" class="avatar">
                        <span class="icon-status';

          if($user->gender == 'female'){ $tpl_sm .= ' online'; }else{ if($user->status == 'status-invisible'){ $tpl_sm .= ' disconected'; }else{ $tpl_sm .= ' '.$user->status; }}
            $tpl_sm .= '"></span>';
          if(Auth::user()->unreadFrom($user->id, true) != 0){
                        $tpl_sm .= '<div data-from="'.$user->id.'" class="label-avatar unread-right bg-orange">'.Auth::user()->unreadFrom($user->id, true).'</div>';
          }
                    $tpl_sm .= '</div>
                </li>';

        //tpl_sm end

        //tpl_lg

        $tpl_lg .= '<li data-id="'.$user->id.'" ';

        $tpl_lg .= 'data-from="'.Auth::id().'" ';
        $tpl_lg .= 'onClick="chat_open(this,event);" class="inline-items js-chat-open">

                    <div class="author-thumb">
                        <img alt="author" src="/storage/images/'.$user->profile_image().'" class="avatar">
                        <span  data="status" class="icon-status ';
                        if($user->gender == 'female'){ $tpl_lg .= 'online'; } else {$tpl_lg .= $user->status ;}
                        $tpl_lg .='"></span>';
                        if(Auth::user()->unreadFrom($user->id, true) != 0){
                        $tpl_lg .= '<div data-from="'.$user->id.'" class="label-avatar unread-right bg-orange">'.Auth::user()->unreadFrom($user->id, true).'</div>';
          }
                    $tpl_lg .= '</div>

                    <div class="author-status">';
                    if(Auth::user()->isAdmin()){
                        $tpl_lg .= '<a href="#" class="h6 author-name">'.$user->name().' - '.Auth::user()->name().'</a>
                        <span class="status">'.$user->moto.'</span>';
                      }else{
                        $tpl_lg .= '<a href="#" class="h6 author-name">'.$user->name().'</a>
                        <span class="status">'.$user->moto.'</span>';
                      }
                    $tpl_lg .= '</div>

                    <div class="more"><a href="/block/'.$user->id.'" onclick="event.stopPropagation()">
                                    <svg data-toggle="tooltip" data-placement="top" data-original-title="'.l('BLOCK USER').'" class="olymp-block-from-chat-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-block-from-chat-icon"></use></svg>
                                </a>
                    </div>

                </li>';

      }


      return response()->json(['tpl_sm' => $tpl_sm, 'tpl_lg' => $tpl_lg]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */
    public function load_top(Request $request)
    {
      if(Auth::user()->isAdmin()){
        $auth_id = Auth::user()->getAccountIds();
      }else{
        $auth_id = [Auth::id()];
      }

      $chats = Auth::user()->lastChats(10,$request->chats)->with('userFromRelationship, userToRelationship');

      $tpl = '';

      foreach($chats as $chat){
        $userFrom = $chat->lastMessage()->userFromRelationShip;
        $userTo = $chat->lastMessage()->userToRelationShip;

        $tpl .= '<li ';
        if(!in_array($chat->lastMessage()->from_user, $auth_id)){
          $tpl .= 'data-id="'.$userFrom->id.'" data-from="'.$userTo->id.'" ';
        }
        elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id()){
          $tpl .= 'data-id="'.$userFrom->id.'" data-from="'.$userTo->id.'" ';
        }
        else{
          $tpl .= 'data-id="'.$userTo->id.'" data-from="'.$userFrom->id.'" ';
        }
        $tpl .= 'class="mess__item ';
        if($chat->lastMessage()->status == 0 && !in_array($chat->lastMessage()->from_user, $auth_id)){ $tpl .= 'message-unread';}
        $tpl .= '" onclick="chat_open(this,event);">
                                <div class="author-thumb">
                                    <img src="/storage/images/';
        if(!in_array($chat->lastMessage()->from_user, $auth_id)){

          $tpl .= $userFrom->profile_image();

        }elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id()){

          $tpl .= $userFrom->profile_image();

        }else{

          $tpl .= $userTo->profile_image();

        }
        $tpl .= '" alt="author">
                                </div>
                                <div class="notification-event">
                                    <a href="#" class="h6 notification-friend">';
        if(Auth::user()->isAdmin()){
          if(!in_array($chat->lastMessage()->from_user, $auth_id)){
            $tpl .= $userFrom->name().' - '.$userTo->name();
          }
          elseif($chat->lastMessage()->from_user != Auth::id() && $chat->lastMessage()->to_user == Auth::id()){
            $tpl .= $userFrom->name().' - '.$userTo->name();
          }
          else{
            $tpl .= $userTo->name().' - '.$userFrom->name();
          }
        }else{
          if($chat->lastMessage()->from_user != Auth::id()){
            $tpl .= $userFrom->name();
          }
          else{
            $tpl .= $userTo->name();
          }
        }
        $tpl .= '</a><span class="chat-message-item">';
        if(in_array($chat->lastMessage()->from_user, $auth_id)){
         $tpl .= 'You:';
        }
        elseif($chat->lastMessage()->from_user == Auth::id() && $chat->lastMessage()->to_user != Auth::id()){
         $tpl .= 'You:';
        }
        $tpl .= ' '.$chat->lastMessage()->message.'</span>
                                </div>
                                <span class="notification-icon">
                                    <svg class="olymp-chat---messages-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-chat---messages-icon"></use></svg>
                                </span>
                                <div class="more">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg>
                                </div>
                            </li>';
        }

      return response()->json($tpl);
    }
}
