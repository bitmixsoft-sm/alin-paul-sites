var callingHandler = {};
callingHandler.obj = {};

callingHandler.setOption = function(name, value) {
    if (window.parent.callingObj != undefined) {
        window.parent.callingObj[name] = value;
    } else if (window.opener !=undefined && window.opener != null) {
        //opener is window
        window.opener.callingObj[name] = value;
    }
}
callingHandler.getOption = function(name) {
    if (window.parent.callingObj != undefined) {
        return window.parent.callingObj[name];
    } else if (window.opener !=undefined && window.opener != null) {
        //opener is window
        return window.opener.callingObj[name];
    }
}
callingHandler.getObject = function() {
    if (window.parent.callingObj != undefined) {
        return window.parent.callingObj;
    } else if (window.opener !=undefined && window.opener != null) {
        //opener is window
        return window.opener.callingObj;
    }
    return undefined;
}

var pusher = new Pusher(pusher_key, {
      cluster: 'eu',
      forceTLS: true,
      authEndpoint: '/pusher/auth',
	  auth: {
	    headers: {
	      'X-CSRF-Token': $('input[name="_token"]').val()
	    }
	  }
    });
    var auth_id = $("#auth_id").val();
    var channel = pusher.subscribe('private-messages.'+auth_id);
    channel.bind('pusher:subscription_succeeded', function(members) {
	    console.log('successfully subscribed!');
	});
var video = document.getElementById('myvideo');
var stream = false;
navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.mediaDevices;
var dat = '';
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};
if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
    // Firefox 38+ seems having support of enumerateDevicesx
    navigator.enumerateDevices = function(callback) {
        navigator.mediaDevices.enumerateDevices().then(callback);
    };
}

var MediaDevices = [];
var isHTTPs = location.protocol === 'https:';
var canEnumerate = false;

if (typeof MediaStreamTrack !== 'undefined' && 'getSources' in MediaStreamTrack) {
    canEnumerate = true;
} else if (navigator.mediaDevices && !!navigator.mediaDevices.enumerateDevices) {
    canEnumerate = true;
}

var hasMicrophone = false;
var hasSpeakers = false;
var hasWebcam = false;

var isMicrophoneAlreadyCaptured = false;
var isWebcamAlreadyCaptured = false;
window.stream = null;

function checkDeviceSupport(callback) {
    if (!canEnumerate) {
        return;
    }

    if (!navigator.enumerateDevices && window.MediaStreamTrack && window.MediaStreamTrack.getSources) {
        navigator.enumerateDevices = window.MediaStreamTrack.getSources.bind(window.MediaStreamTrack);
    }

    if (!navigator.enumerateDevices && navigator.enumerateDevices) {
        navigator.enumerateDevices = navigator.enumerateDevices.bind(navigator);
    }

    if (!navigator.enumerateDevices) {
        if (callback) {
            callback();
        }
        return;
    }

    MediaDevices = [];
    navigator.enumerateDevices(function(devices) {
        devices.forEach(function(_device) {
            var device = {};
            for (var d in _device) {
                device[d] = _device[d];
            }

            if (device.kind === 'audio') {
                device.kind = 'audioinput';
            }

            if (device.kind === 'video') {
                device.kind = 'videoinput';
            }

            var skip;
            MediaDevices.forEach(function(d) {
                if (d.id === device.id && d.kind === device.kind) {
                    skip = true;
                }
            });

            if (skip) {
                return;
            }

            if (!device.deviceId) {
                device.deviceId = device.id;
            }

            if (!device.id) {
                device.id = device.deviceId;
            }

            if (!device.label) {
                device.label = 'Please invoke getUserMedia once.';
                if (!isHTTPs) {
                    device.label = 'HTTPs is required to get label of this ' + device.kind + ' device.';
                }
            } else {
                if (device.kind === 'videoinput' && !isWebcamAlreadyCaptured) {
                    isWebcamAlreadyCaptured = true;
                }

                if (device.kind === 'audioinput' && !isMicrophoneAlreadyCaptured) {
                    isMicrophoneAlreadyCaptured = true;
                }
            }

            if (device.kind === 'audioinput') {
                hasMicrophone = true;
            }

            if (device.kind === 'audiooutput') {
                hasSpeakers = true;
            }

            if (device.kind === 'videoinput') {
                hasWebcam = true;
            }

            // there is no 'videoouput' in the spec.

            MediaDevices.push(device);
        });

        if (callback) {
            callback();
        }
    });
}
checkDeviceSupport(function() {
    if(hasWebcam && hasMicrophone){
        console.log('Has video, has audio');
    	navigator.getUserMedia({ video: true, audio: true }, function (stream) {

		  window.stream = stream;
          //setup parent window stream
          callingHandler.setOption('stream', stream);
		  window.dat = 'all';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
		    window.dat = 'none';
			console.error(err);
		});
    }
    if(!hasWebcam && hasMicrophone){
    	navigator.getUserMedia({ video: false, audio: true }, function (stream) {

		  window.stream = stream;
          //setup parent window stream
          callingHandler.setOption('stream', stream);
		  window.dat = 'audio';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
		    window.dat = 'none';
			//console.error(err);
		});
    }
    if(hasWebcam && !hasMicrophone){
    	navigator.getUserMedia({ video: true, audio: false }, function (stream) {
		  window.stream = stream;
          //setup parent window stream
          callingHandler.setOption('stream', stream);
		  window.dat = 'video';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
		    window.dat = 'none';
			//console.error(err);
		});
    }
    if(!hasWebcam && !hasMicrophone){
        console.log('no video, no audio');
    	navigator.getUserMedia({ video: false, audio: false }, function (stream) {
		  window.stream = stream;
          //setup parent window stream
          callingHandler.setOption('stream', stream);
		  window.dat = 'none';
          console.log('has stream');

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
			console.error(err);
		});
    }
});


var Peer = false;

  function InitPeer(type){
    if (type == 'init') {
        var peer = new SimplePeer({
            initiator: true,
            trickle: false,
            stream: (window.stream!=null)?window.stream:false,
            offerOptions: {
                offerToReceiveAudio: true,
                offerToReceiveVideo: true
            }
        });
    } else {
        var peer = new SimplePeer({
            initiator: false,
            stream: (window.stream!=null)?window.stream:false,
            trickle: false,
            offerOptions: {
                offerToReceiveAudio: true,
                offerToReceiveVideo: true
            }
        });
    }
  	peer.on('stream', function(stream){
        console.log('Create video');
  		CreateVideo(stream);
  	});
  	peer.on('close', function(){
  		console.log('Apel inchis!');
        callingHandler.setOption('status', 'closing_call');
        /*console.log(callingHandler.getObject());*/
        close_call(inp);
  		in_call = false;
  	});
  	return peer;
  }
  var in_call = false;
  function CallTo(to_user){
  	if(!in_call){
  		var peer = InitPeer('init');
  		peer.on('signal', function(data){
  				var CSRF_TOKEN = $('input[name="_token"]').val();
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/call',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		data: JSON.stringify(data),
			                    		to: to_user,
			                    		callFrom: callingHandler.getOption('callFrom'),
			                    		ready: dat
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
							    		in_call = true;
										                    }
			                });
  		});
  		peer.on('error', function(err){
            in_call = false;
            //should close the video
            if (callingHandler.getObject() != undefined) {
                close_call(callingHandler.getOption('inCallWith'));
            }
            console.log(err);
        });
  		Peer = peer;
        //the initiator should add stream to call
        //Peer.addStream(stream);
        /*if (window.stream!=false) {
            Peer.addStream(stream);
        }*/
  	}
  }
  function AnswerTo(offer, to_user){
  	var peer = InitPeer('notInit');
      /*console.log(offer);*/
  	peer.on('signal', (data) => {
  			var CSRF_TOKEN = $('input[name="_token"]').val();
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/answer',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		data: JSON.stringify(data),
			                    		to: to_user,
			                    		callFrom: callingHandler.getOption('callFrom'),
			                    		ready: dat
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    		in_call = true;
			                    }
			                });
  	});
    peer.on('error', function(err){
         in_call = false;
         //should close the video
         if (callingHandler.getObject() != undefined) {
            close_call(callingHandler.getOption('inCallWith'));
         }
        console.log(err);
    });
  	peer.signal(JSON.parse(offer));
  	Peer = peer;
    if (window.stream!=false && window.stream != null) {
        //alert('Add stream to the peer');
        //Peer.addStream(window.stream);
        //Peer.signal({renegotiate: true});
        video.srcObject = window.stream;
        video.play();
        //alert('Video added');
    }
    if (
      callingHandler.getOption('outputCaller') == 'none'
        ||
      callingHandler.getOption('outputCaller') == 'audio'
        ||
      callingHandler.getOption('outputCaller') == null
    ) {
        ShowMyVideo();
    }
  }

  function GetAnswer(answer){
  		video.srcObject = stream;
		video.play();
  		var peer = Peer;
      /*console.log(answer);*/
  		peer.signal(JSON.parse(answer));
  		$('#video_container .calling_now .call_state').html('Ongoing Call');
        if (callingHandler.getOption('outputCaller') == 'none') {
            ShowMyVideo();
        }

  }
  function CreateVideo(stream) {
  	if (outputClient != 'audio') {
	  	$('#video_container .calling_now').hide();
        $('#peerVideo').show();
	}
	if (dat == 'all' || dat == 'video') {
  		$('#myvideo').show();
  	}
    if (dat == 'none') {
        callingHandler.getObject().disableVideo();
        callingHandler.getObject().disableAudio();
    } else if (dat == 'audio') {
        callingHandler.getObject().disableVideo();
    } else if (dat == 'video') {
        callingHandler.getObject().disableAudio();
    }
  	var video2 = document.getElementById('peerVideo')

    video2.srcObject = stream;
    video2.play();

    timer_active = true;
    countTimer();
  }

  function ShowMyVideo() {
	if (dat != 'audio') {
  		$('#myvideo').show();
  	}
    $('#video_container .calling_now .call_state').html('Ongoing Call');
    timer_active = true;
    countTimer();
  }

  	var inp = false;
    function send_call(t){
		inp = $(t).attr('data-call');
		CallTo(inp);
		 //window.open("/videochat?call_to="+inp+"#init", "popupWindow", "width=800, height=500");
	};
	data_caller = false;
	channel.bind('incomingCall', function(data) {
        if (window.opener !=undefined && window.opener.data_caller != undefined) {
            data_caller = window.opener.data_caller;
        }
		if(in_call && outputClient == 'audio' && data.payload.from == data_caller.from){
			data_caller = data.payload;
			AnswerTo(data_caller.offer, data_caller.from);
		}
		if(!in_call){
		data_caller = data.payload;
			$('#caller_details .caller_name').html(data_caller.name);
			$('#caller_details .caller_details img').attr('src', '/storage/images/'+data_caller.profile_image);

    		outputClient = data_caller.ready;
    		if(data_caller){
		  	$('#profile_img_call').attr('src', '/storage/images/'+data_caller.profile_image);
			$('#video_container .calling_now .caller_name').html(data_caller.name);
			$('#video_container .calling_now .call_state').html('Ongoing Call');
		}
    	}
	});
	var outputClient = false;
	channel.bind('getAnswerCall', function(data) {
		data = data.payload;
        console.log('getAnswerCall', data);
        callingHandler.setOption('inCallWith', data.from);
        callingHandler.setOption('myId', data.id);
        callingHandler.setOption('status', 'answered');
        callingHandler.setOption('outputCaller', data.ready);
        /*console.log(callingHandler.getObject());*/
		outputClient = data.ready;
		GetAnswer(data.answer);
	});
	channel.bind('refuseCall', function(data) {
		data = data.payload;
		console.log('refused');
        /*console.log('Before refused 373', callingHandler.getObject());*/
        /*console.log(data);*/
        if (data.from!=undefined && callingHandler.getOption('inCallWith')=='') {
            callingHandler.setOption('inCallWith', data.from);
        }
        if (data.id!=undefined && callingHandler.getOption('myId')=='') {
            callingHandler.setOption('myId', data.id);
        }
        callingHandler.setOption('status', 'refused');
        /*console.log('After refused 377', callingHandler.getObject());*/
		var peer = Peer;
		if(peer != ''){
            try {
                peer.destroy();
            } catch(e) {
                /* handle error */
                console.log('Error on peer destroy');
            }
		}
		timer_active = false;
  		totalSeconds = 0;
  		window.close();
	});
	function close_call(uid, userAction){
		var peer = Peer;
        uid = uid || userCalledBy;
        userAction = userAction || false;
		if(peer != ''){
            try {
                peer.destroy();
            } catch(e) {
                /* handle error */
                console.log('Error on peer destroy');
            }
		}
		in_call = false;
        if (window.parent && window.parent.in_call != undefined) {
            window.parent.in_call = false;
        }
		if(inp){
			var refuse_to = inp;
		}else{
			var refuse_to = data_caller.from;
		}
        /*console.log('close call', uid, inp, userCalledBy);*/
        /*console.log('Before close_call 399', callingHandler.getObject());*/
        var CSRF_TOKEN = $('input[name="_token"]').val();
        $.ajax({
            /* the route pointing to the post function */
            url: '/videochat/credits',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN,
                credits: creditTake,
                otherUser: data_caller.from,
                callFrom: callingHandler.getOption('caller')
            },
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (result) {
                creditTake = 0;
                // console.log('credits');
                if (window.parent.close_video_call != undefined) {
                    if (userAction == false) {
                        //to avoid loop, if it is not from user action
                        window.parent.close_video_call(uid);
                    }
                } else if (window.opener !=undefined && window.opener != null) {
                    //opener is window
                    window.close();
                }
            },
            fail: function () {
                //should close the chat on failure as well
                if (window.parent.close_video_call != undefined) {
                    if (userAction == false) {
                        //to avoid loop, if it is not from user action
                        window.parent.close_video_call(uid);
                    }
                } else if (window.opener !=undefined && window.opener != null) {
                    //opener is window
                    window.close();
                }
            }
        });
        $.ajax({
            /* the route pointing to the post function */
            url: '/videochat/refuse',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN,
                to: refuse_to,
                callFrom: ((callingHandler.getOption('caller')!='')?'':callingHandler.getOption('callFrom'))
            },
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (result) {
                // window.close();
                if (window.parent.close_video_call != undefined) {
                    if (userAction == false) {
                        window.parent.close_video_call(uid);
                    }
                } else if (window.opener !=undefined && window.opener != null) {
                    //opener is window
                    window.close();
                }
            },
            fail: function () {
                //should close the chat on failure as well
                if (window.parent.close_video_call != undefined) {
                    if (userAction == false) {
                        window.parent.close_video_call(uid);
                    }
                } else if (window.opener !=undefined && window.opener != null) {
                    //opener is window
                    window.close();
                }
            }
        });
	};
    window.parent.close_call = close_call;
	// $(window).bind('beforeunload', function(){
	// 	close_call();
	// });

	var timer_active = false;
	var totalSeconds = 0;
	$('#call_timer').html("00:00");
	var creditTake = 0;
	function countTimer(){
		if(timer_active){
		++totalSeconds;
		var hour = Math.floor(totalSeconds / 3600);
		var minute = Math.floor((totalSeconds-hour*3600)/60);
		var seconds = totalSeconds - (hour*3600 + minute*60);
		if(seconds < 10){
			var seconds2 =('0' + seconds).slice(-2)
		}else{
			var seconds2 = seconds;
		}
		if(minute < 10){
			var minute2 =('0' + minute).slice(-2)
		}else{
			var minute2 = minute;
		}
		if(hour < 1){
			$('#call_timer').html(minute2 + ":" + seconds2);
		}else{
			$('#call_timer').html(hour + ":" + minute2 + ":" + seconds2);
		}
		creditTake = creditTake+1;

		if(creditTake >= 10){
			var CSRF_TOKEN = $('input[name="_token"]').val();
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/credits',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		credits: creditTake,
			                    		otherUser: data_caller.from,
                                        callFrom: callingHandler.getOption('callFrom')
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    		creditTake = 0;
			                    		console.log('credits');
			                    }
			                });
		}

		setTimeout(countTimer, 1000);
		}
	}

	var notInCall = false;
	var userCalledBy
	$(window).on('load', function() {
		setTimeout(function(){
			if(window.location.hash === '#init') {
				var userToCall = getUrlParameter('call_to');
				inp = userToCall;
				if(stream != ''){
					CallTo(inp);
				}else{
					notInCall = 'call';
				}
			}
			if(window.location.hash === '#answer') {
				userCalledBy = getUrlParameter('called_by');
                if (window.opener !=undefined && window.opener.data_caller != undefined) {
				    data_caller = window.opener.data_caller;
                }
                if (window.parent !=undefined && window.parent.data_caller != undefined) {
				    data_caller = window.parent.data_caller;
                }
				if(stream != ''){
					AnswerTo(data_caller.offer, userCalledBy);
				}else{
					notInCall = 'answer';
				}
			}
	}, 2500);
	});
	Peer.oniceconnectionstatechange = function() {
		if (Peer.iceConnectionState == 'disconnected') {
			close_call();
		}
	}
