  var in_call = false;
  	var inp = false;
  	var child = false;
    var removed_messages = false;
    function send_call(t){
		inp = $(t).attr('data-call');
		this.event.stopPropagation();
		//CallTo(inp);
		// child = window.open("/videochat?call_to="+inp+"#init", "popupWindow", "width=800, height=500");
		$('.popup-chat[data-id="'+inp+'"] .ubgvideo').html('<iframe src="/videochat?call_to='+inp+'#init" frameborder="0" width="100%" height="100%"></iframe>');
		$('.popup-chat[data-id="'+inp+'"] .videocallicon').html('<i class="fas fa-video-slash" onclick="close_video_call('+inp+', true, false);"></i>');
		//$('.popup-chat[data-id="'+inp+'"] .notification-list.chat-message').hide();
        if (msg_remove == false) {
		    $('.popup-chat[data-id="'+inp+'"] .msgtoggle').html('<i class="fas fa-eye" onclick="show_hide_messages(this);"></i>');
		    removed_messages = true;
        }

		in_call = true;
	};
	data_caller = false;
	channel.bind('incomingCall', function(data) {
		console.log(in_call);
		console.log(data);
		if(in_call && outputClient == 'audio' && data.payload.from == data_caller.from){
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
    		if(data_caller){
		  	$('#profile_img_call').attr('src', '/storage/images/'+data_caller.profile_image);
			$('#video_container .calling_now .caller_name').html(data_caller.name);
			$('#video_container .calling_now .call_state').html('Ongoing Call');
		}
    	}
	});
	$('#answer_call').click(function(){
		$('#is_calling').modal('hide');
        /*console.log(data_caller.from);*/
        if ($('div.popup-chat[data-id='+data_caller.from+']').length!=0) {
            $('.popup-chat[data-id="'+data_caller.from+'"] .ubgvideo').html('<iframe src="/videochat?called_by='+data_caller.from+'#answer" frameborder="0" width="100%" height="100%"></iframe>');
            $('.popup-chat[data-id="'+data_caller.from+'"] .videocallicon').html('<i class="fas fa-video-slash" onclick="close_video_call('+data_caller.from+', true, true);"></i>');
            $('.popup-chat[data-id="'+data_caller.from+'"] .notification-list.chat-message').hide();
            if (msg_remove == false) {
                $('.popup-chat[data-id="'+inp+'"] .msgtoggle').html('<i class="fas fa-eye" onclick="show_hide_messages(this);"></i>');
                removed_messages = true;
            }
        } else {
		    child = window.open("/videochat?called_by="+data_caller.from+"#answer", "popupWindow", "width=800, height=500");
        }
		in_call = true;
	});
	$('#refuse_call').click(function(){
		$('#is_calling').modal('hide');
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
        if ($('div.popup-chat[data-from='+data.id+']').length!=0) {
            //do nothing
            /*console.log('close video call popup for '+data.id);*/
            close_video_call(data.id, true, true);
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

	function close_video_call(uid, home, othercall)
	{
		home = home || false;
		othercall = othercall || false;
		if(home == true)
		{
			this.event.stopPropagation();
		}
        var propNameCheck = 'id';
        if (othercall == true) {
            propNameCheck = 'from';
        }
        if (in_call == true) {
            window.close_call(uid);
        }
        if ($('.popup-chat[data-'+propNameCheck+'="'+uid+'"] .ubgvideo').html()!='') {
            $('.popup-chat[data-'+propNameCheck+'="'+uid+'"] .ubgvideo').html('');
            $('.popup-chat[data-'+propNameCheck+'="'+uid+'"] .videocallicon').html('<i class="fas fa-video" onclick="send_call(this)" data-call="'+uid+'"></i>');
            if (removed_messages == true) {
                $('.popup-chat[data-'+propNameCheck+'="'+uid+'"] .msgtoggle').html('<i class="fas fa-eye-slash" onclick="show_hide_messages(this);"></i>');
                $('.popup-chat[data-'+propNameCheck+'="'+uid+'"] .notification-list.chat-message').show();
                removed_messages = false;
            }
        }
	}
