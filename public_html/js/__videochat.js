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
    	navigator.getUserMedia({ video: true, audio: true }, function (stream) {

		  window.stream = stream;
		  window.dat = 'all';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
			//console.error(err);
		});
    }
    if(!hasWebcam && hasMicrophone){
    	navigator.getUserMedia({ video: false, audio: true }, function (stream) {

		  window.stream = stream;
		  window.dat = 'audio';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
			//console.error(err);
		});
    }
    if(hasWebcam && !hasMicrophone){
    	navigator.getUserMedia({ video: true, audio: false }, function (stream) {

		  window.stream = stream;
		  window.dat = 'video';

		  if(notInCall == 'call'){
		  	CallTo(inp);
		  }
		  if(notInCall == 'answer'){
		  	AnswerTo(data_caller.offer, userCalledBy);
		  }
		}, function(err){
			//console.error(err);
		});
    }
});


var Peer = false;

  function InitPeer(type){
  	var peer = new SimplePeer({
  		initiator: (type == 'init') ? true : false,
  		trickle: false,
  		stream: stream
  	});
  	peer.on('stream', function(stream){
  		CreateVideo(stream);
  	});
  	peer.on('close', function(){
  		console.log('Apel inchis!');
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
			                    		ready: dat
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
							    		in_call = true;
										                    }
			                });
  		});
  		Peer = peer;
  	}
  }
  function AnswerTo(offer, to_user){
  	var peer = InitPeer('notInit');
  	console.log(offer);
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
			                    		ready: dat
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    		in_call = true;
			                    }
			                });
  	});
  	peer.signal(JSON.parse(offer));
  	Peer = peer;
  	video.srcObject = stream;
	video.play();
  }

  function GetAnswer(answer){
  		video.srcObject = stream;
		video.play();
  		var peer = Peer;
  		console.log(answer);
  		peer.signal(JSON.parse(answer));
  		$('#video_container .calling_now .call_state').html('Ongoing Call');
  }
  function CreateVideo(stream){
  	if(outputClient != 'audio'){
	  	$('#video_container .calling_now').hide();
	  	$('#peerVideo').show();
	  }
	if(dat != 'audio'){
  		$('#myvideo').show();
  	}
  	var video2 = document.getElementById('peerVideo')

    video2.srcObject = stream;
    video2.play();
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
    	console.log(outputClient);
	});
	var outputClient = false;
	channel.bind('getAnswerCall', function(data) {
		data = data.payload;
		console.log('answered');
		outputClient = data.ready;
		GetAnswer(data.answer);
	});
	channel.bind('refuseCall', function(data) {
		data = data.payload;
		console.log('refused');
		var peer = Peer;
		if(peer != ''){
			peer.destroy();
		}
		timer_active = false;
  		totalSeconds = 0;
  		window.close();
	});
	function close_call(uid){
		var peer = Peer;
		if(peer != ''){
			peer.destroy();
		}
		in_call = false;
		if(inp){
			var refuse_to = inp;
		}else{
			var refuse_to = data_caller.from;
		}
		var CSRF_TOKEN = $('input[name="_token"]').val();
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/credits',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		credits: creditTake,
			                    		otherUser: data_caller.from
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    		creditTake = 0;
			                    		// console.log('credits');
										window.parent.close_video_call(uid);
			                    }
			                });
					$.ajax({
			                    /* the route pointing to the post function */
			                    url: '/videochat/refuse',
			                    type: 'POST',
			                    /* send the csrf-token and the input to the controller */
			                    data: {_token: CSRF_TOKEN,
			                    		to: refuse_to
			                    	},
			                    dataType: 'JSON',
			                    /* remind that 'data' is the response of the AjaxController */
			                    success: function (result) {
			                    	// window.close();
									window.parent.close_video_call(uid);
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
			                    		otherUser: data_caller.from
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

	var mic_muted = false;

	function mute_mic(){
		if(mic_muted){
			stream.getAudioTracks()[0].enabled = true;
			mic_muted = false;
			$('#mute_mic').html('<i class="fas fa-microphone"></i>');
		}else{
			stream.getAudioTracks()[0].enabled = false;
			mic_muted = true;
			$('#mute_mic').html('<i class="fas fa-microphone-slash"></i>');
		}
	}

	var video_remove = false;

	function remove_video(){
		if(video_remove){
			stream.getVideoTracks()[0].enabled = true;
			video_remove = false;
			$('#remove_video').html('<i class="fas fa-video"></i>');
			$('#myvideo').show();

		}else{
			stream.getVideoTracks()[0].enabled = false;
			video_remove = true;
			$('#remove_video').html('<i class="fas fa-video-slash"></i>');
			$('#myvideo').hide();
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
				data_caller = window.opener.data_caller;
				if(stream != ''){
					AnswerTo(data_caller.offer, userCalledBy);
				}else{
					notInCall = 'answer';
				}
			}
	}, 1000);
	});
	Peer.oniceconnectionstatechange = function() {
		if(Peer.iceConnectionState == 'disconnected'){
			close_call();
		}
	}
