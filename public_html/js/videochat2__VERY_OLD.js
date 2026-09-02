  var in_call = false;
  	var inp = false;
  	var child = false;
    function send_call(t){
		inp = $(t).attr('data-call');
		//CallTo(inp);
		child = window.open("/videochat?call_to="+inp+"#init", "popupWindow", "width=800, height=500");
		in_call = true;
	};
	data_caller = false;
	channel.bind('incomingCall', function(data) {
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
		child = window.open("/videochat?called_by="+data_caller.from+"#answer", "popupWindow", "width=800, height=500");
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
		data = data.payload;
		console.log('refused');
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