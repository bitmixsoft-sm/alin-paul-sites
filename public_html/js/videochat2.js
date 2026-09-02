    var callingObj = {
        'inCallWith':'',
        'status':'',
        'myId':'',
        'source':'iframe',
        stream: null,
        mic_muted: false,
        video_remove: false,
        windowIsMaximized: false,
        mute_mic: function(event) {
		    event.stopPropagation();
            if (this.mic_muted) {
                if (this.stream != null && this.stream.getAudioTracks()[0]!=undefined) {
                    this.stream.getAudioTracks()[0].enabled = true;
                    this.mic_muted = false;
                    // $('#mute_mic').html('<i class="fas fa-microphone"></i>');
                    $('.popup-chat[data-id="'+this.inCallWith+'"] .mute_mic').html('<i class="fas fa-microphone" onclick="callingObj.mute_mic(event);"></i>');
                    console.log('show microphone');
                }
            } else {
                if (this.stream != null && this.stream.getAudioTracks()[0]!=undefined) {
                    this.stream.getAudioTracks()[0].enabled = false;
                    this.mic_muted = true;
                    // $('#mute_mic').html('<i class="fas fa-microphone-slash"></i>');
                    $('.popup-chat[data-id="'+this.inCallWith+'"] .mute_mic').html('<i class="fas fa-microphone-slash"" onclick="callingObj.mute_mic(event);"></i>');
                    console.log('hide microphone');
                }
            }
        },
        remove_video: function (event) {
		    event.stopPropagation();
            if (this.video_remove){
                if (this.stream != null && this.stream.getVideoTracks()[0]!=undefined) {
                    this.stream.getVideoTracks()[0].enabled = true;
                    this.video_remove = false;
                    // $('#remove_video').html('<i class="fas fa-video"></i>');
                    $('.popup-chat[data-id="'+this.inCallWith+'"] .remove_video').html('<i class="fas fa-video" onclick="callingObj.remove_video(event);"></i>');
                    console.log('show video');
                }

            } else {
                if (this.stream != null && this.stream.getVideoTracks()[0]!=undefined) {
                    this.stream.getVideoTracks()[0].enabled = false;
                    this.video_remove = true;
                    // $('#remove_video').html('<i class="fas fa-video-slash"></i>');
                    $('.popup-chat[data-id="'+this.inCallWith+'"] .remove_video').html('<i class="fas fa-video-slash" onclick="callingObj.remove_video(event);"></i>');
                    console.log('hide video');
                }
            }
        },
        fullScreen: function (event) {
            this.windowIsMaximized = true;
		    event.stopPropagation();
            $('.popup-chat[data-id="'+this.inCallWith+'"]').css(
                {
                    height: $(window).height(),
                    right: '0',
                    width: '100%',
                    'max-height': '100%'
                }
            );
            $('.popup-chat[data-id="'+this.inCallWith+'"]').addClass('full-screen');
            $('.popup-chat[data-id="'+this.inCallWith+'"] .video-size').html('<i class="fas fa-window-minimize" onclick="callingObj.normalScreen(event);"></i>');
            var formHeight = $('.popup-chat[data-id="'+this.inCallWith+'"] .chatform').outerHeight();
            var titleHeight = $('.popup-chat[data-id="'+this.inCallWith+'"] .ui-block-title').outerHeight();
            $('.popup-chat[data-id="'+this.inCallWith+'"] .notification-list').css('max-height', $(window).height()-formHeight-titleHeight-100);
        },
        normalScreen: function (event) {
            this.windowIsMaximized = false;
            if (event) {
		        event.stopPropagation();
            }
            $('.popup-chat[data-id="'+this.inCallWith+'"]').css(
                {
                    height: 'auto',
                    right: '75px',
                    width: 'auto',
                    'max-height': 'initial'
                }
            );
            $('.popup-chat[data-id="'+this.inCallWith+'"]').removeClass('full-screen');
            $('.popup-chat[data-id="'+this.inCallWith+'"] .video-size').html('<i class="fas fa-window-maximize" onclick="callingObj.fullScreen(event);"></i>');
            $('.popup-chat[data-id="'+this.inCallWith+'"] .notification-list').css('margin-bottom', 'auto');
        },
        onVideoClose: function () {
            if (this.windowIsMaximized == true) {
                this.normalScreen();
            }
            console.log('Closed video call');
        }
    };
    var in_call = false;
  	var inp = false;
  	var child = false;
    var removed_messages = false;
    function send_call(t){
		inp = $(t).attr('data-call');
        callingObj.inCallWith = inp;
        callingObj.status = 'send_call';
        //console.log(callingObj);
		this.event.stopPropagation();
		//CallTo(inp);
		// child = window.open("/videochat?call_to="+inp+"#init", "popupWindow", "width=800, height=500");
		$('.popup-chat[data-id="'+inp+'"] .ubgvideo').html('<iframe src="/videochat?call_to='+inp+'&source=iframe#init" frameborder="0" width="100%" height="100%"></iframe>');
		$('.popup-chat[data-id="'+inp+'"] .videocallicon').html('<i class="fas fa-phone-slash" onclick="close_video_call('+inp+', true);"></i>');
        $('.popup-chat[data-id="'+callingObj.inCallWith+'"] .video-controls').html('<span class="mute_mic"><i class="fas fa-microphone" onclick="callingObj.mute_mic(event);"></i></span><span class="remove_video"><i class="fas fa-video" onclick="callingObj.remove_video(event);"></i></span><span class="video-size"><i class="fas fa-window-maximize" onclick="callingObj.fullScreen(event);"></i></span>');
		//$('.popup-chat[data-id="'+inp+'"] .notification-list.chat-message').hide();
        /*if (msg_remove == false) {
		    $('.popup-chat[data-id="'+inp+'"] .msgtoggle').html('<i class="fas fa-eye-slash" onclick="show_hide_messages(this);"></i>');
		    removed_messages = true;
        }*/

		in_call = true;
	};
	data_caller = false;
	channel.bind('incomingCall', function(data) {
		if(in_call && outputClient != undefined && outputClient == 'audio' && data.payload.from == data_caller.from){
			data_caller = data.payload;
		}
		if(!in_call){
		    data_caller = data.payload;
			$('#caller_details .caller_name').html(data_caller.name);
			$('#caller_details .caller_details img').attr('src', '/storage/images/'+data_caller.profile_image);

				$('#is_calling').modal({
	    		backdrop: 'static',
	    		keyboard: false});

    		outputClient = data_caller.ready;
            callingObj.inCallWith = data_caller.name;
            if (callingObj.myId == '') {
                callingObj.myId = data.payload.id;
            }
            callingObj.status = 'incoming call';
            //console.log('incoming call', callingObj);
            if (data_caller) {
                $('#profile_img_call').attr('src', '/storage/images/'+data_caller.profile_image);
                $('#video_container .calling_now .caller_name').html(data_caller.name);
                $('#video_container .calling_now .call_state').html('Ongoing Call');
            }
    	}
	});
	$('#answer_call').click(function(){
		$('#is_calling').modal('hide');
        /*console.log(data_caller.from);*/
        callingObj.inCallWith = data_caller.from;
        callingObj.status = 'answer call';
		in_call = true;
        chat_open(null, 0, callingObj.inCallWith);
        setTimeout(function() {
            //wait a little and then check if chat is opened
            if ($('div.popup-chat[data-id='+data_caller.from+']').length!=0) {
                $('.popup-chat[data-id="'+data_caller.from+'"] .ubgvideo').html('<iframe src="/videochat?called_by='+data_caller.from+'&source=iframe#answer" frameborder="0" width="100%" height="100%"></iframe>');
                $('.popup-chat[data-id="'+data_caller.from+'"] .videocallicon').html('<i class="fas fa-phone-slash" onclick="close_video_call('+data_caller.from+', true);"></i>');
                $('.popup-chat[data-id="'+callingObj.inCallWith+'"] .video-controls').html('<span class="mute_mic"><i class="fas fa-microphone" onclick="callingObj.mute_mic(event);"></i></span><span class="remove_video"><i class="fas fa-video" onclick="callingObj.remove_video(event);"></i></span><span class="video-size"><i class="fas fa-window-maximize" onclick="callingObj.fullScreen(event);"></i></span>');
                /*$('.popup-chat[data-id="'+data_caller.from+'"] .notification-list.chat-message').hide();
                if (msg_remove == false) {
                    $('.popup-chat[data-id="'+inp+'"] .msgtoggle').html('<i class="fas fa-eye" onclick="show_hide_messages(this);"></i>');
                    removed_messages = true;
                }*/
            } else {
                child = window.open("/videochat?called_by="+data_caller.from+"#answer", "popupWindow", "width=800, height=500");
                callingObj.source = 'window';
            }
        }, 100);
        //console.log('answer call', callingObj);
	});
	$('#refuse_call').click(function(){
		$('#is_calling').modal('hide');
            callingObj.status = 'refuse call';
            callingObj.inCallWith = data_caller.from;
            //console.log('Refuse call button', callingObj);
		var CSRF_TOKEN = $('input[name="_token"]').val();
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/refuse',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		to: data_caller.from
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    		//console.log(result);
			                    }
			                });
	});
	channel.bind('refuseCall', function(data) {
        console.log('refused');
		data = data.payload;
        //console.log('Before Closed call', callingObj);
        if (callingObj.status != 'closing_call' && callingObj.status!='waiting') {
            try {
                callingObj.status = 'closed call';
                if (callingObj.inCallWith == '') {
                    callingObj.inCallWith = inp;
                }
                if (callingObj.myId == '') {
                    callingObj.myId = data.id;
                }
                //console.log('Closed call', callingObj);
                if ($('div.popup-chat[data-from='+data.id+']').length!=0) {
                    //do nothing
                    /*console.log('close video call popup for '+data.id);*/
                    if (inp!=undefined && inp != data.id) {
                        //inp is the id of the called user
                        //should close that call
                        close_video_call(inp, true);
                    } else {
                        close_video_call(data.id, true);
                    }
                }
            } catch(e) {
                //for some reason could not close the video
            }
        }
		in_call = false;
	});

	checkChild();
	function checkChild() {
	    if (child.closed) {
	        in_call = false;
	        setTimeout(checkChild, 1000);
	    }else{
	    	setTimeout(checkChild, 1000);
	    }
	}

	function close_video_call(uid, home)
	{
		home = home || false;
		if (home == true)
		{
			this.event.stopPropagation();
		}
        var propNameCheck = 'id';
        /*if (othercall == true) {
            propNameCheck = 'from';
        }*/
        callingObj.onVideoClose();
        if (in_call == true) {
            in_call = false;
            window.close_call(callingObj.inCallWith, false);
        } else {
            if (callingObj.inCallWith!='') {
                //console.log('Before close_video_ call', callingObj);
                if ($('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .ubgvideo').html()!='') {
                    $('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .ubgvideo').html('');
                    $('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .video-controls').html('');
                    $('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .videocallicon').html('<i class="fas fa-phone" onclick="send_call(this)" data-call="'+callingObj.inCallWith+'"></i>');
                    /*if (removed_messages == true) {
                        $('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .msgtoggle').html('<i class="fas fa-eye" onclick="show_hide_messages(this);"></i>');
                        $('.popup-chat[data-'+propNameCheck+'="'+callingObj.inCallWith+'"] .notification-list.chat-message').show();
                        removed_messages = false;
                    }*/
                }
                callingObj.inCallWith = '';
                callingObj.status = 'waiting';
                callingObj.stream = null;
                callingObj.mic_muted = false;
                callingObj.video_remove = false;
            }
        }
	}
