<div class="chats">
<div id="popup-chat" class="ui-block popup-chat">
                            <div class="ui-block-title" onclick="toggle_chat(this);">
                                <span class="icon-status online"></span>
                                <h6 class="title"></h6>
                                    <div class="msgtoggle"><i class="fas fa-eye-slash" onclick="show_hide_messages(this);"></i></div>
                                <div class="more">
                                    <span class="videocallicon"><i class="fas fa-video" onclick="send_call(this);"></i></span>
                                    <svg class="olymp-little-delete js-chat-close" onClick="close_chat(this);"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-little-delete"></use></svg>
                                </div>
                            </div>

                            @if(Auth::user()->isAdmin())
                            <select id="admin_account" onChange="change_user(this);">
                                <option value="{{Auth::id()}}">{{Auth::user()->name()}}</option>
                                @foreach(Auth::user()->getAccounts() as $acc)
                                <option value="{{$acc->getUser()->id}}">{{$acc->getUser()->name()}}</option>
                                @endforeach
                            </select>
                            @php

                              $langs = App\Lang::get();

                            @endphp
                            <div class="row m-0">
                            <div class="col-xs-6 w-50">
                            <select class="lang_for_user" onChange="change_user_lang(this);">
                                @foreach($langs as $lang)
                                <option value="{{$lang->code}}">{{$lang->name}}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="col-xs-6 w-50">
                            <select class="auto_translate">
                                <option selected value="yes">Traducere activa</option>
                                <option value="no">Traducere dezactivata</option>
                            </select>
                            </div>
                            </div>
                            @endif

                            <div class="ubgvideo"></div>
                            

                            <div onscroll="load_messages(this);" class="mCustomScrollbar ps ps--theme_default ps--active-y" data-mcs-theme="dark" data-ps-id="22d8324a-2107-44b7-ac86-3396f06ca414">

                                <ul class="notification-list chat-message chat-message-field">                                  
                            
                                </ul>

                            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__scrollbar-y-rail" style="top: 0px; height: 350px; right: 0px;"><div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 255px;"></div></div></div>
                        
                            <form class="chatform">
                        
                                <div class="form-group label-floating is-empty">
                                    <label class="control-label">{{l("Press enter to post...")}}</label>
                                    <textarea class="form-control chatbox" placeholder="" onfocus="seen_message(this);" onkeypress="send_msg(event, this);"></textarea>
                                    <div class="add-options-message">
                                        <i class="fas fa-paper-plane" onclick='send_msg(event, this, true);'></i>
                                    </div>
                                <span class="material-input"></span></div>
                        
                            </form>

                        
                        
                        </div>

                    </div>
                    <input id="auth_id" type="hidden" value="{{Auth::id()}}">


                    <!-- Modal -->
<div class="modal fade" id="videochat" tabindex="-1" role="dialog" aria-labelledby="videochatLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div id="video_container" class="modal-body">
        <div class="calling_now">
            <img id="profile_img_call" src="/storage/images/default.png">
            <span class="caller_name">Client</span>
            <span class="call_state">dialing...</span>    
        </div>
       <video id="myvideo" muted></video>
       <video id="peerVideo"></video>
       <span id="call_timer">0:00</span>
       <button id="mute_mic" type="button" onclick="mute_mic();"><i class="fas fa-microphone"></i></button>
       <button id="end_call" type="button" onclick="close_call();"><i class="fas fa-phone-slash"></i></button>
       <button id="remove_video" type="button" onclick="remove_video();"><i class="fas fa-video"></i></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="is_calling" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div id="caller_details" class="modal-body">
        <div class="caller_details">
            <img src="">
            <span class="caller_name">Client</span>
            <span class="call_state">is calling...</span>
        </div>
        <div class="call_options">
            <ul>
                <li><button id="answer_call"><i class="fas fa-phone-volume"></i></button></li>
                <li><button id="refuse_call"><i class="fas fa-phone-slash"></i></button></li>
            </ul>
        </div>
      </div>
    </div>
  </div>
</div>