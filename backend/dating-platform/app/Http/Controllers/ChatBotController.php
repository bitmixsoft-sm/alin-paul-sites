<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\AISetting;
use App\Message;
use App\Settings;
use App\User;
use App\ChatBotResponse;
use App\ChatBotTrain;
use App\WelcomeMessage;
use App\Events\NewMessage;
use App\Services\AI\AIOrchestratorService;
use App\Services\AI\PersonaPromptBuilder;
use App\Services\AdminAlertService;
use Throwable;

class ChatBotController extends Controller
{
    /**
     * How long a conversation stays AI-paused after an admin manually takes over, before
     * the AI automatically resumes replying — so a forgotten pause doesn't leave a
     * conversation dead indefinitely. Each new manual admin reply resets this clock.
     */
    private const AI_PAUSE_AUTO_RESUME_HOURS = 24;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function train(Request $request)
    {
        $train = ChatBotTrain::where('website', $_SERVER['SERVER_NAME']);
        if(!$train->exists()){
        	$train = new ChatBotTrain;
        	$train->message_id = 0;
        	$train->website = $_SERVER['SERVER_NAME'];
        	$train->save();
        }else{
        	$train = $train->firstOrFail();
        }
        //Check if all from last chat is learned
        $females = User::where('gender', 'female')->select('id')->get();
        $females = $females->pluck('id');
        $chat = Message::where('id', '>', $train->message_id)->whereNotIn('from_user', $females)->orderBy('id', 'asc');
        $response = array();
        if($chat->exists()){
        	$chat = $chat->take(100)->get();
        	foreach ($chat as $message) {
        		$nextMessage = Message::where(function($query) use ($message){
                  $query->where('chatbot', '!=', 'true');
	        		    $query->where('id', '>', $message->id);
	                $query->where('from_user', $message->from_user);
	                $query->where('to_user', $message->to_user);
	            })->orWhere(function($query) use ($message){
                  $query->where('chatbot', '!=', 'true');
	        		    $query->where('id', '>', $message->id);
	                $query->where('from_user', $message->to_user);
	                $query->where('to_user', $message->from_user);
	            });
	            if($nextMessage->exists()){
	            	$nextMessage = $nextMessage->firstOrFail();
	            	if($nextMessage->from_user != $message->from_user){
	            		//$response[] = ['question' => $message->message, 'response' => $nextMessage->message];
	            		$addResponse = new ChatBotResponse;
	            		$addResponse->question = $message->message;
	            		$addResponse->response = $nextMessage->message;
	            		$addResponse->save();
	            		
	            	}
	            }
	            $train->message_id = $message->id;
	            $train->save();
        	}
        }
        //dd($response);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function response(Request $request, AIOrchestratorService $orchestrator, AdminAlertService $adminAlert, PersonaPromptBuilder $persona)
    {
      if (Auth::user()->isAdmin()) {
        return;
      }

      $text = $request->text;

      $from = $request->from;
      $to = $request->to;
      $user_to = User::where('id', $to)->firstOrFail();
      $user_from = User::where('id', $from)->firstOrFail();

      // Real-chat AI auto-reply — gated by its own master switch on the AI Settings admin
      // page, independent of the legacy "Activare Chat Bot" toggle below (which only
      // controls the old keyword-match fallback bot).
      if (AISetting::current()->textAiEnabled() && $user_to->gender === 'female' && $user_to->ai_enabled) {
          $bot_chat = $user_to->chat($from);

          // If an admin manually took over and then forgot to resume the AI, don't leave
          // the conversation dead forever — auto-resume after a period of admin inactivity.
          if ($bot_chat->ai_paused && $bot_chat->ai_paused_at !== null
              && $bot_chat->ai_paused_at->lt(now()->subHours(self::AI_PAUSE_AUTO_RESUME_HOURS))) {
              $bot_chat->ai_paused = false;
              $bot_chat->ai_paused_at = null;
              $bot_chat->save();
          }

          if (! $bot_chat->ai_paused) {
              if ($this->sendAiReply($orchestrator, $adminAlert, $persona, $user_to, $user_from, $bot_chat, $text)) {
                  return;
              }
          }
      }

      $chat_bot = Settings::where('id', 22)->firstOrFail();
      if($chat_bot->value == 'yes'){
        $text = str_replace('?', '', $text);
        $text = str_replace('!', '', $text);
        $text = str_replace('.', '', $text);
    	$bot_message = ChatBotResponse::where('question', 'like', '%' . translate($text, 'en') . '%')->orWhere('question', 'like', '%' . translate($text, 'it') . '%')->orWhere('question', 'like', '%' . translate($text, 'ro') . '%');

          if($bot_message->exists()){
            $bot_message = $bot_message->inRandomOrder()->firstOrFail();
            $bot_message = $bot_message->response;
          $bot_msg = new Message;
          $bot_msg->from_user = $to;
          $bot_msg->to_user = $from;
          $bot_msg->chatbot = 'true';
          $bot_msg->message = translate($bot_message, $user_from->lang);
          $bot_msg->save();
          $bot_chat = $user_to->chat($from);
          $bot_chat->last_msg = $bot_msg->id;
          $bot_chat->save();

          $bot_message_response[] = Message::where('id', $bot_msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail();

          $bot_response = ['user_name' => $user_to->name(), 'user_img' => $user_to->profile_image(), 'auth_img' => "", 'msg' => $bot_message_response, 'to_user' => $from, 'chat_bot' => 'true'];
          sleep(rand(2,5));
          try {
            event(new NewMessage($bot_response));
          } catch (Throwable $throwable) {
            Log::error('Broadcast failed in ChatBotController@response (keyword bot reply already saved).', [
              'to_user' => $from,
              'from_user' => $to,
              'exception' => $throwable->getMessage(),
            ]);
          }
      	}
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function auto_message(Request $request)
    {
      $user_to = User::where('id', Auth::id())->firstOrFail();
      $chats = Auth::user()->allUserChats('all')->pluck('id')->toArray();
      $user_from = WelcomeMessage::where('website', $_SERVER['SERVER_NAME'])->whereNotIn('user_id', Auth::user()->lastUserChatsIds(1000))->inRandomOrder();

        if($user_from->exists()){
          $user_from = $user_from->firstOrFail();
          $user_from_db = User::where('id', $user_from->user_id)->firstOrFail();
          $bot_msg = new Message;
          $bot_msg->from_user = $user_from_db->id;
          $bot_msg->to_user = $user_to->id;
          $bot_msg->chatbot = 'true';
          $bot_msg->message = l($user_from->message);
          $bot_msg->save();
          $bot_chat = $user_to->chat($user_from_db->id);
          $bot_chat->last_msg = $bot_msg->id;
          $bot_chat->save();

          $bot_message_response[] = Message::where('id', $bot_msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail();

          $bot_response = ['user_name' => $user_from_db->name(), 'user_img' => $user_from_db->profile_image(), 'auth_img' => "", 'msg' => $bot_message_response, 'to_user' => $user_to->id, 'chat_bot' => 'true'];
          try {
            event(new NewMessage($bot_response));
          } catch (Throwable $throwable) {
            Log::error('Broadcast failed in ChatBotController@auto_message (welcome message already saved).', [
              'to_user' => $user_to->id,
              'from_user' => $user_from_db->id,
              'exception' => $throwable->getMessage(),
            ]);
          }
          return response()->json(true);
        }
        return response()->json(false);
    }

    /**
     * Generates an LLM reply from $user_to (an AI-enabled female profile) to $user_from's
     * message and saves/broadcasts it exactly like the keyword-bot path does. Returns false
     * (instead of throwing) on any failure, so the caller falls back to the legacy
     * keyword-match bot rather than leaving the message unanswered.
     */
    private function sendAiReply(AIOrchestratorService $orchestrator, AdminAlertService $adminAlert, PersonaPromptBuilder $persona, User $user_to, User $user_from, \App\Chat $bot_chat, string $text): bool
    {
        try {
            $reply = $orchestrator->generateTextReply(
                message: $text,
                systemPrompt: $persona->build($user_to),
                history: $this->recentHistory($user_to->id, $user_from->id),
            );
        } catch (Throwable $throwable) {
            Log::error('AI chat auto-reply generation failed.', [
                'to_user' => $user_to->id,
                'from_user' => $user_from->id,
                'exception' => $throwable->getMessage(),
            ]);

            $adminAlert->notify(
                key: 'ai_text_reply_failed',
                subject: 'AI chat auto-reply is failing',
                body: "The AI text auto-reply stopped working (falling back to the old keyword bot in the meantime).\n\n"
                    . "This usually means the OpenAI API key is invalid, out of quota/credit, or unreachable — check it on the AI Settings admin page.\n\n"
                    . "Error:\n" . $throwable->getMessage(),
            );

            return false;
        }

        $bot_msg = new Message;
        $bot_msg->from_user = $user_to->id;
        $bot_msg->to_user = $user_from->id;
        $bot_msg->chatbot = 'true';
        $bot_msg->ai_generated = true;
        $bot_msg->message = translate($reply, $user_from->lang);
        $bot_msg->save();

        $bot_chat->last_msg = $bot_msg->id;
        $bot_chat->save();

        $bot_message_response = [Message::where('id', $bot_msg->id)->select('id', 'from_user', 'to_user', 'status', 'message', 'created_at')->firstOrFail()];

        $bot_response = [
            'user_name' => $user_to->name(),
            'user_img' => $user_to->profile_image(),
            'auth_img' => "",
            'msg' => $bot_message_response,
            'to_user' => $user_from->id,
            'chat_bot' => 'true',
        ];

        sleep(rand(2, 5));
        try {
            event(new NewMessage($bot_response));
        } catch (Throwable $throwable) {
            Log::error('Broadcast failed in ChatBotController@sendAiReply (AI reply already saved).', [
                'to_user' => $user_from->id,
                'from_user' => $user_to->id,
                'exception' => $throwable->getMessage(),
            ]);
        }

        return true;
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function recentHistory(int $femaleUserId, int $maleUserId, int $limit = 20): array
    {
        return Message::query()
            ->where(function ($query) use ($femaleUserId, $maleUserId) {
                $query->where('from_user', $femaleUserId)->where('to_user', $maleUserId);
            })
            ->orWhere(function ($query) use ($femaleUserId, $maleUserId) {
                $query->where('from_user', $maleUserId)->where('to_user', $femaleUserId);
            })
            ->latest('id')
            ->limit($limit)
            ->get(['from_user', 'message'])
            ->reverse()
            ->map(fn (Message $message): array => [
                'role' => (int) $message->from_user === $femaleUserId ? 'assistant' : 'user',
                'content' => (string) $message->message,
            ])
            ->values()
            ->all();
    }

}