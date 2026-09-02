    $('a[data-open="register"]').click( function(e){
    	e.preventDefault();
		$('.nav-item a[href="#home"]').tab('show');
		history.pushState(null, null, '?page=register');
	});

	$('a[data-open="login"]').click( function(e){
		e.preventDefault();
		$('.nav-item a[href="#profile"]').tab('show');
		history.pushState(null, null, '?page=login');
	});
	$('.nav-item a[href="#profile"]').click( function(e){
		history.pushState(null, null, '?page=login');
	});
	$('.nav-item a[href="#home"]').click( function(e){
		history.pushState(null, null, '?page=register');
	});
	$('a[data-change="profile-status"]').click( function(e){
		e.preventDefault();
		$('#status-header').removeClass('online away disconected status-invisible');
		$.ajax({url: $(this).attr('href'),
				success: function(result){
			$('#status-header').addClass(result);
		}});
	});
	$('#moto-change').submit(function(e){
		e.preventDefault();
		var moto = $('input[name="moto"]').val();
		var CSRF_TOKEN = $('input[name="_token"]').val();
		$.ajax({
                    /* the route pointing to the post function */
                    url: '/profile-moto',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data: {_token: CSRF_TOKEN,
                    		moto: moto},
                    dataType: 'JSON',
                    /* remind that 'data' is the response of the AjaxController */
                    success: function (data) {
                        $("#moto-text").html(data);
                        $('input[name="moto"]').val(data);
                    }
                });
	});
	var image_role = 'normal';
	var image_size = {width: 125, height: 125};
	var set_image = false;
	$('a[data-image-upload="profile"]').click( function(){
		image_role = 'profile';
		if($crop){
			$crop.croppie('destroy');
		}
		$('#show-preview').hide();
		$('#show-preview').empty();
		$crop = $('#show-preview').croppie({
		enableExif: true,
		viewport: {
        	width: 125,
        	height: 125
    	},
		boundary: {
			width: 500,
			height: 500
		}
	}
	);
	image_size = {width: 125, height: 125};
	});
	var $crop = false;
	$('a[data-image-upload="cover"]').click( function(){
		image_role = 'cover';
		if($crop){
			$crop.croppie('destroy');
		}
		$('#show-preview').hide();
		$('#show-preview').empty();
		$crop = $('#show-preview').croppie({
		enableExif: true,
		viewport: {
        	width: 750,
        	height: 250
    	},
		boundary: {
			width: 750,
			height: 350
		}
	}
	);
	image_size = {width: 1920, height: 640};
	});



	$('#existent_photos').click(function(){
		if(image_role == 'profile'){
			var prp = $('#choose-from-my-photo .optionsRadios');
			$(prp).prop('type', 'radio');
			//console.log('ok');
		}
		if(image_role == 'cover'){
			var prp = $('#choose-from-my-photo .optionsRadios');
			$(prp).prop('type', 'radio');
			//console.log('ok');
		}
	});

	$('#choose-from-my-photo').on('hidden.bs.modal', function () {
	    var prp = $('#choose-from-my-photo .optionsRadios');
				$(prp).prop('type', 'checkbox');
	});
	$('#update-header-photo').on('hidden.bs.modal', function () {
	    image_role = 'normal';
	});
	$('a[data-imageupload="normal"]').click( function(){
		image_role = 'normal';
		image_size = {width: 1920, height: 640};
		$('#show-preview').empty();
		if($crop){
			$crop.croppie('destroy');
		}
	});
	$('#upload-link').click(function(e){
		e.preventDefault();
		$("#upload").trigger('click');
	});
	var image_to_upload = '';
	$('#upload').change(function(){
		var reader = new FileReader();
		if(image_role != 'normal'){
			$('#show-preview').show();
			reader.onload = function(e){
				$crop.croppie('bind', {
					url:e.target.result
				}).then(function(){
					//console.log('Bind complete');
				});
			}
		}else{
			reader.onload = function(e){
				$('#show-preview').html('<img id="preview-normal-image" src="'+e.target.result+'">');
				image_to_upload = e.target.result;
			}
		}
		reader.readAsDataURL(this.files[0]);
		$('#upload-preview').show();
	});
	$('#upload-photo-button').click(function(e) {
		if(image_role != 'normal'){
			$crop.croppie('result', {
				type: 'canvas',
				size: image_size,
				format: 'jpeg'
			}).then(function(result){
				var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
		                url: '/upload-image',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		image: result,
	                    		role: image_role
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
							window.location.href = data.route;
		            	}
		        });

			});
		}else{
			var CSRF_TOKEN = $('input[name="_token"]').val();
			$.ajax({
	                url: '/add-album-photo',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		image: image_to_upload
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
						window.location.href = data.route;
	            	}
	        });
		}
	});
	$('#update-header-photo').on('hidden.bs.modal', function () {
		if($crop){
			$crop.croppie('destroy');
		}
	});
	$('#userSearchInput').keyup( function(){
		var CSRF_TOKEN = $('input[name="_token"]').val();
		var value = $('#userSearchInput').val();
			$.ajax({
	                url: '/user-search',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		search: value
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
						$('.js-user-search').show();
						$('.js-user-search').html(data);
	            	}
	        });
	});
	$('#userSearchInputResponsive').keyup( function(){
		var CSRF_TOKEN = $('input[name="_token"]').val();
		var value = $('#userSearchInputResponsive').val();
			$.ajax({
	                url: '/user-search',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		search: value
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
						$('.js-user-search').show();
						$('.js-user-search').html(data);
	            	}
	        });
	});
	$('.js-user-search').hover(
		function() {
			$('.js-user-search').addClass('mouse_over');
		}, function() {
			$('.js-user-search').removeClass('mouse_over');
		}
	);
	$('#userSearchInput').blur( function(){
		if(!$('.js-user-search').hasClass('mouse_over')){
			$('.js-user-search').hide();
		}
	});
	$('#userSearchInput').focus( function(){
		$('.js-user-search').show();
	});
	$('.search-bar .form-group.with-button button').click( function(e){
		e.preventDefault();
	});
	$('.add-album-photo').click(function(e){
		e.preventDefault();
		$("#upload-album").trigger('click');
	});
	var album_uploaded = 0;
	var photo_counter = 0;
	var album_photos = new Array();
	var photo_descriptions = new Array();
	$('#upload-album').change(function(){
		var reader = new FileReader();
		var template = "";
		template += "<div id='uploader_"+album_uploaded+"' class='photo-album-item-wrap uploaded-photos-now col-3-width'><div class='photo-album-item' data-mh='album-item'><div class='form-group'>";
		template += "<div id='preview_"+album_uploaded+"' class='album-preview'></div>";
		template += "<textarea id='desc_"+album_uploaded+"' onKeyup='album_photo_desc(this, "+album_uploaded+")'  class='form-control album-photo-desc' placeholder='Write something about this photo...''></textarea><button id='del_"+album_uploaded+"' onClick='delete_image_album(this);' class='delete-album-photo btn btn-primary btn-sm' data-id='"+album_uploaded+"'>Delete</button></div></div></div>";
		if(photo_counter == 5){
			$('#add-album-photo-button').hide();
		}
		reader.onload = function(e){
			var select = "#preview_"+album_uploaded;
			var text = "#desc_"+album_uploaded;
			var del = "#del_"+album_uploaded;
			var CSRF_TOKEN = $('input[name="_token"]').val();
			$.ajax({
	                url: '/add-album-photo',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		image: e.target.result,
                    		album: true
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
					$(select).append(
						"<img src='/storage/images/"+data.name+"' alt='photo'>"
					);
					album_photos.push(data.id);
					$(text).attr('data-for', data.id);
					$(del).attr('data-for', data.id);
	            	}
	        });
				album_uploaded = album_uploaded+1;
				photo_counter = photo_counter+1;
		}
		reader.readAsDataURL(this.files[0]);
		$('#album-upload-wrapper').append(template);

	});
	$('#create-photo-album').on('hidden.bs.modal', function () {
		var CSRF_TOKEN = $('input[name="_token"]').val();
    	$.ajax({
	                url: '/delete-album-photo',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		images: album_photos
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
	                	$('.uploaded-photos-now').remove();
						album_uploaded = 0;
						photo_counter = 0;
						album_photos = [];
						photo_descriptions = [];
	            	}
	        });
	});
	$('#close-album-modal').click(function(e) {
		e.preventDefault();
	    $('#create-photo-album').modal('hide');
	});
	function delete_image_album(e){
		var data_id = $(e).attr('data-id');
		var uploader ='#uploader_'+data_id;
		var CSRF_TOKEN = $('input[name="_token"]').val();
		$.ajax({
	                url: '/delete-album-photo',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		images: album_photos[data_id]
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
						//console.log(data);
	            	}
	        });
		$(uploader).remove();
		delete album_photos[data_id];
		delete photo_descriptions[data_id];
		if(photo_counter == 6){
			$('#add-album-photo-button').show();
		}
		photo_counter = photo_counter-1;
	};
	function album_photo_desc(t,n){
		var photo_desc = $(t).val();
		var for_img = $(t).attr("data-for");
		photo_descriptions[n] = [for_img, photo_desc];
	};
	$("#post-new-album").click(function(e){
		e.preventDefault();
		if($(".uploaded-photos-now").length > 0){
			var CSRF_TOKEN = $('input[name="_token"]').val();
			var privacy = $('input[name="album-privacy"]').val();
			var name = $('input[name="album-name"]').val();
			$.ajax({
		                url: '/new-album',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		images: album_photos,
	                    		descriptions: photo_descriptions,
	                    		name: name,
	                    		privacy: privacy
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
							window.location.href = data;
		            	}
		        });
	    }
	});
	var show_image = new Array();
	var currently_showing = '';
	function get_album(t){
		this.event.preventDefault();
		currently_showing = $(t).attr('data-album');
		var CSRF_TOKEN = $('input[name="_token"]').val();
		if($(t).attr('data-protect')){
			var check = prompt("Password for album:");
		}


		$.ajax({
		        url: '/get-album',
		        type: 'POST',
		        data: {_token: CSRF_TOKEN,
	                    album: currently_showing,
	                    pass: check
	                    },
	            dataType: 'JSON',
		        success: function (data)
		            {
		            	if(data){
			            	$("#album-slides").html(data.tpl_slides);
				            $("#album-slides-pag").html(data.tpl_pags);
			            	$("#album-slides-pag").append("<svg class='btn-next olymp-popup-right-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-right-arrow'></use></svg><svg class='btn-prev olymp-popup-left-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-left-arrow'></use></svg>");
			            	show_image = data.show_image;
			            	init_swiper();
			            	$(".swiper-swiper-unique-id-0").append("<svg onclick='slide_next();' class='btn-next olymp-popup-right-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-right-arrow'></use></svg><svg onclick='slide_prev();' class='btn-prev olymp-popup-left-arrow'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-popup-left-arrow'></use></svg>");
			            	$('#album-item-user img').attr('src', '/storage/images/'+data.profile_image);
			            	$('#album-item-user .post__author-name').attr('href', '/profile/'+data.user_username);
			            	$('#album-item-user .post__author-name').html(data.user_name);
			            	$('#control-block-album a').attr('data-id', data.user_id);
			            	$('#control-block-album a').attr('data-original-title', 'Message '+data.user_name);
			            	$('#open-photo-popup-v2').modal();
			            }else{
			            	alert("Incorrect password");
			            }
		            }
		        });
	};

	$('#open-photo-popup-v2').on('hidden.bs.modal', function() {
		$("#album-slides").empty();
		$("#album-slides-pag").empty();
	});
	function update_slider_desc(x){
		$("#album-item-user .post__date time").html(show_image[x][1]);
		$('#delete-photo-inner').attr('data-id', x);
		$("#album-item-desc").html(show_image[x][0]);
	}
	$('#add-friend-btn').click( function(e){
		e.preventDefault();
		$.ajax({url: $(this).attr('href'),
				success: function(result){
					if(result.res != '/packages'){
			$('#add-friend-btn span').html(result.res);
			if(result.url != "/add-friend/"+result.id && result.url != "/delete-friend/"+result.id && result.url != "/delete-request/"+result.id){
				var counter = $('#fr-req-counter').html();
				$('li[data-id="'+result.id+'"]').remove();
				$('#fr-req-counter').html(parseInt(counter)-1);
			}
			$('#add-friend-btn').attr('href', result.url);
			$('#chat-users-small li[data-id="'+result.id+'"]').remove();
		}else{
			window.location.href = result.res;
		}
		}});
	});
	function acc_fr_req(t,e){
		e.preventDefault();
		$.ajax({url: $(t).attr('href'),
				success: function(result){
					if(result.res != '/packages'){
			$('#friend-requests-block li[tpl-id="'+result.id+'"]').remove();
			$('#friend-requests-block2 li[tpl-id="'+result.id+'"]').remove();
			if($('#add-friend-btn').attr('data-id') == result.id){
				$('#add-friend-btn span').html(result.res);
				$('#add-friend-btn').attr('href', result.url);
			}
			var counter = $('#fr-req-counter').html();
			$('#fr-req-counter').html(parseInt(counter)-1);
			var counter2 = $('#fr-req-counter2').html();
			$('#fr-req-counter2').html(parseInt(counter2)-1);
			if($('#chat-users-small li[data-id="'+result.id+'"]').length == 0){
				var ch_ic = "<li data-id='"+result.id+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
				ch_ic += "<div class='author-thumb'>";
				ch_ic += "<img alt='author' src='/storage/images/"+result.profile_image+"' class='avatar'>";
				ch_ic += "<span class='icon-status "+result.status+"'></span>";
				ch_ic += "</div>";
				ch_ic += "</li>";
				$("#chat-users-small").append(ch_ic);
			}
			if($('#friend-requests-block li').length == 0){
				$('#friend-requests-block').html("<span class='no-req'>No friend requests</span>");
			}
			if($('#friend-requests-block2 li').length == 0){
				$('#friend-requests-block2').html("<span class='no-req'>No friend requests</span>");
			}
		}else{
			window.location.href = result.res;
		}
		}});
	};
	function del_fr_req(t,e){
		e.preventDefault();
		$.ajax({url: $(t).attr('href'),
				success: function(result){
					if(result.res != '/packages'){
			$('#friend-requests-block li[tpl-id="'+result.id+'"]').remove();
			if($('#add-friend-btn').attr('data-id') == result.id){
				$('#add-friend-btn span').html(result.res);
				$('#add-friend-btn').attr('href', result.url);
			}
			var counter = $('#fr-req-counter').html();
			$('#fr-req-counter').html(result.count);
			if($('#friend-requests-block li').length == 0){
				$('#friend-requests-block').html("<span class='no-req'>No friend requests</span>");
			}
			}else{
			window.location.href = result.res;
		}
		}});
	};
	function seen_message(t){
		var id = $(t).attr('data-id');
		var from = $(t).attr('data-from');
				if(typeof from !== typeof undefined && from !== false){
					var from = $(t).attr('data-from');
				}else{
					var from = 'no';
				}
		var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/seen',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		id: id,
		                    		from: from
		                    	},
		                    dataType: 'JSON',
			                success: function (result)
			                {
			                	var counter = $('#chat-unread-counter').html();
								$('#chat-unread-counter').html(parseInt(counter)-result.counter);
								var counter2 = $('#chat-unread-counter2').html();
								$('#chat-unread-counter2').html(parseInt(counter2)-result.counter);
								var counter3 = $('#chat-unread-counter3').html();
								$('#chat-unread-counter3').html(parseInt(counter3)-result.counter);

								$('.js-chat-open[data-from="'+from+'"][data-id="'+id+'"] .unread-right[data-from="'+id+'"]').remove();

								$('#chat-messages-top li[data-id="'+id+'"]').removeClass('message-unread');
								$('#chat-messages-top2 li[data-id="'+id+'"]').removeClass('message-unread');
								$("#admin_account option[value='"+from+"']").text(result.user_name);
			            	}
			        });
	};
	var opened = function(){
		if($('#sidebar-right-listener').hasClass('open')){
			return true;
		}else{
			return false;
		}
	};
	$('.js-sidebar-open').click( function(e){
		if(opened()){
			$('.popup-chat').each(function( index, value ) {
				  var mg = parseInt($(this).css('right')) + 200;
				  $(this).css('right', mg+"px");
				});
		}else{
			$('.popup-chat').each(function( index, value ) {
				  var mg = parseInt($(this).css('right')) - 200;
				  $(this).css('right', mg+"px");
				});
		}
	});
	function register_guest(){
		var guest_name = $('#guest_chat_form input[name="guest_name"]').val();
		var guest_email = $('#guest_chat_form input[name="guest_email"]').val();
		var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/get-guest-session',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		param: 'add_guest',
		                    		name: guest_name,
		                    		email: guest_email
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	if(data.status == 'OK'){
			                		$("#chatModal").modal('hide');
			                		//console.log('register success!');
			                		user_reg = 'guest';
			                		$('.btn-open-chat').click();
			                	}
			                	if(data.status == 'FAIL'){
			                		$("#chatModal").modal('hide');
			                		//console.log('register failed!');
			                		user_reg = 'failed';
			                	}
			            	}
			        });
	}
	var chats = 0;
	var chat_mg = 75;
	var first_bar = false;
	var messages = new Array();
	var user_reg = 'auth';
	function chat_open(t,e, id = null, from_id = null){
		if ($(window).width() < 768) {
		   close_chats();
		}
		if(e != 0){
			e.preventDefault();
		}

		if($(t).attr('data-guest') == 'true'){

			//CHECK SESSION GUEST

			/*var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/get-guest-session',
			                type: 'POST',
			                async: false,
			                data: {_token: CSRF_TOKEN,
		                    		param: 'guest'
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	if(data.status == 'OK'){
			                		user_reg = 'guest';
			                		console.log(user_reg);
			                	}
			                	if(data.status == 'FAIL'){
			                		$("#chatModal").modal();
			                		user_reg = 'failed';
			                		console.log('session failed!');
			                	}
			            	}
			        });*/

		}
		if(user_reg == 'auth' || user_reg == 'guest'){
		$('.fixed-sidebar').removeClass('open');
		if(id != null){
			data_id = id;
		}else{
			data_id = $(t).attr('data-id');
		}
		if($('.popup-chat[data-id="'+data_id+'"]').length == 0){
			if(chats <3){
				if(from_id != null){
					var from = from_id;
				}else{
					var from = $(t).attr('data-from');
					if(typeof from !== typeof undefined && from !== false){
						var from = $(t).attr('data-from');
					}else{
							var from = auth_id;
					}
				}
				if(user_reg == 'guest'){
					var from = 'guest';
				}
				var sec_mg = parseInt($('.popup-chat').first().css("right"));
				$('.popup-chat').last().clone().prependTo(".chats");
				$('.popup-chat').first().show();
				$('.popup-chat #admin_account').first().val(from);
				$('.popup-chat').first().attr('data-id', data_id);
				$('.popup-chat').first().attr('data-from', from);
				$('.popup-chat .js-chat-close').first().attr('data-id', data_id);
				$('.popup-chat textarea').first().attr('data-id', data_id);
				$('.popup-chat textarea').first().attr('data-from', from);
				$('.popup-chat .more i').first().attr('data-call', data_id);
		        $('.popup-chat .videocallicon i').attr('data-from', from);
				chats = chats+1;
				if(chats > 1){
						chat_mg = sec_mg+325;
				$('.popup-chat').first().css( "right", chat_mg+"px" );
				}else{
				$('.popup-chat').first().css( "right", chat_mg+"px" );
				}
				var to = data_id;

		        var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/get-messages',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		to: to,
		                    		from: from
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	if(!data.status){
			                		if(global_users_online[to] == undefined){
			                			$('.popup-chat[data-id="'+to+'"] .icon-status').removeClass('online');
			                			$('.popup-chat[data-id="'+to+'"] .icon-status').addClass('disconected');
			                		}else{
			                			$('.popup-chat[data-id="'+to+'"] .icon-status').removeClass('disconected');
			                			$('.popup-chat[data-id="'+to+'"] .icon-status').addClass('online');
			                		}
			                	$('.popup-chat[data-id="'+to+'"] .title').html(data.user_name);
			                	$('.popup-chat[data-id="'+to+'"] .lang_for_user').val(data.lang);
			                	messages['u_'+to] = data.msg;
								$('.popup-chat[data-id="'+to+'"] .chat-message-field').html(display_messages(messages['u_'+to], to, data.user_img, data.auth_img));
								var scroll = $('.popup-chat[data-id="'+to+'"] .mCustomScrollbar');
								scroll.animate({ scrollTop: $(scroll).get(0).scrollHeight }, 500);
								var counter = $('#chat-unread-counter').html();
								$('#chat-unread-counter').html(parseInt(counter)-data.unread);
								var counter2 = $('#chat-unread-counter2').html();
								$('#chat-unread-counter2').html(parseInt(counter2)-data.unread);
								var counter3 = $('#chat-unread-counter3').html();
								$('#chat-unread-counter3').html(parseInt(counter3)-data.unread);
								$('.js-chat-open[data-from="'+from+'"][data-id="'+to+'"] .unread-right[data-from="'+to+'"]').remove();
								$('#chat-messages-top li[data-id="'+to+'"]').removeClass('message-unread');
								$('#chat-messages-top2 li[data-id="'+to+'"]').removeClass('message-unread');
								$('#chat').removeClass('active');
								if(data.bg_image != ''){
									$('.popup-chat[data-id="'+to+'"] .mCustomScrollbar').css('background', 'url("/storage/images/'+data.bg_image+'")');
								}
								if(data.video != null){
									$('.popup-chat[data-id="'+to+'"] .ubgvideo').html('<video autoplay muted loop><source src="'+window.location.origin+'/videos/'+data.video+'"></source></video>' );
								}
								}else{
									window.location.href = "/"+data.redirect;
								}
			            	}
			        });


			}
		}else{
			$('.popup-chat[data-id="'+data_id+'"]').removeClass('popup-chat-toggle');
			$('.popup-chat[data-id="'+data_id+'"]').animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100).animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100);
		}
		}
	};
	function change_user(t){
		var to = $(t).parent().attr('data-id');
		var from = $(t).val();
		$(t).parent().attr('data-from', $(t).val());
		$('.popup-chat[data-id="'+to+'"] textarea').attr('data-from', $(t).val());
		$('.popup-chat[data-id="'+to+'"] .more i').attr('data-from', $(t).val());
		$('.popup-chat[data-id="'+to+'"] .videocallicon i').attr('data-from', $(t).val());
		        var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/get-messages',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		to: to,
		                    		from: from
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	$('.popup-chat[data-id="'+to+'"] .title').html(data.user_name);
			                	messages['u_'+to] = data.msg;

									$('.popup-chat[data-id="'+to+'"] .chat-message-field').html(display_messages(messages['u_'+to], to, data.user_img, data.auth_img));

								var scroll = $('.popup-chat[data-id="'+to+'"] .mCustomScrollbar');
								scroll.animate({ scrollTop: $(scroll).get(0).scrollHeight }, 500);
								var counter = $('#chat-unread-counter').html();
								$('#chat-unread-counter').html(parseInt(counter)-data.unread);
								$('.js-chat-open[data-from="'+from+'"][data-id="'+to+'"] .unread-right[data-from="'+to+'"]').remove();
								$('#chat-messages-top li[data-id="'+to+'"]').removeClass('message-unread');
								$('#chat-messages-top2 li[data-id="'+to+'"]').removeClass('message-unread');
			            	}
			        });
	};
	function display_messages(msg, to, user_img, auth_img){
		var display = "";
		for (var i = 0; i < msg.length; i++) {
			if(parseInt(msg[i].from_user) != parseInt(to)){
				display += "<li class='me'>";
				display += "<div class='author-thumb'>";
				display += "<img src='/storage/images/"+auth_img+"'>";
				display += "</div>";
				display += "<div class='notification-event'>";
				display += "<span class='chat-message-item'>"+msg[i].message+"</span>";
				// display += "<span class='notification-date'><time class='entry-date updated' datetime='2004-07-24T18:18'>"+msg[i].created_at+"</time></span>";
				display += "</div>";
				display += "</li>";
			}else{
				display += "<li>";
				display += "<div class='author-thumb'>";
				display += "<img src='/storage/images/"+user_img+"'>";
				display += "</div>";
				display += "<div class='notification-event'>";
				display += "<span class='chat-message-item'>"+msg[i].message+"</span>";
				// display += "<span class='notification-date'><time class='entry-date updated' datetime='2004-07-24T18:18'>"+msg[i].created_at+"</time></span>";
				display += "</div>";
				display += "</li>";
			}
		}
		return display;
	};
	function close_chat(t){
		var mg = 75;
        chat_id = $(t).attr('data-id');
        if (
            window.in_call!=undefined
            &&
            window.in_call == true
            &&
            callingObj.inCallWith == chat_id
        ) {
            //should close the video call when chat is closed
            window.close_call(callingObj.inCallWith);
            window.in_call = false;
            setTimeout(
                function() {
                    //wait a little until the video is closed
                    //console.log('closing chat');
                    close_chat(t);
                }, 100
            );
        } else {
            //console.log('chat closed');
		    $('.popup-chat[data-id="'+$(t).attr('data-id')+'"]').remove();
            $($(".popup-chat").get().reverse()).each(function( index, value ) {
                if(index != 0){
                      $(this).css('right', mg+"px");
                      mg = mg+325;
                }
            });
            if ($('.popup-chat').length == 1) {
                chat_mg = 75;
            } else {
                chat_mg = chat_mg-325;
            }
            chats = chats-1;
        }
	};
	function close_chats(){
		$('.popup-chat[data-id]').remove();
		chat_mg = 75;
		chats = 0;
	};
	function toggle_chat(t){
		var winh = $(window).height();
		var popupwinh = $('.popup-chat').height();
		var bval;
		if($('#admin_account').length)
		{
			bval = 60;
		}
		else
		{
			bval = 55;
		}
		var mbottom = popupwinh - bval;
		if($(t).parent().hasClass('popup-chat-toggle')){
			$(t).parent().animate({bottom: '-15px'}, 500);
			$(t).parent().removeClass('popup-chat-toggle');
		}else{

			$(t).parent().animate({bottom: -mbottom + 'px'}, 500);
			$(t).parent().addClass('popup-chat-toggle');
		}
	};
	var new_message_sound = new Audio('/mp3/new_message.mp3');
	function get_response_msg(to, from, text){
		var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/chatbot/response',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		to: to,
		                    		from:from,
		                    		text: text
		                    	},
		                    dataType: 'JSON',
			                success: function (result)
			                {

			            	}
			        });
	}
	function send_msg(e,t, btn = false){
		var code = (e.keyCode ? e.keyCode : e.which);
	    if (code == 13 || btn) { //Enter keycode
	    	if(btn){
	    		t= $(t).parent().parent().find('textarea');
	    	}
	    	var text = $(t).val();
	    	if(text){
	    		var from = $(t).attr('data-from');
				if(typeof from !== typeof undefined && from !== false){
					var from = $(t).attr('data-from');
				}else{
					var from = 'no';
				}
	        e.preventDefault();
	        var to = $(t).attr('data-id');
	        var translate = $('.popup-chat[data-id="'+to+'"]').find('.auto_translate').val();

	        $(t).val("");
	        var CSRF_TOKEN = $('input[name="_token"]').val();
			$.ajax({
		                url: '/send-message',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		to: to,
	                    		from:from,
	                    		text: text,
	                    		translate: translate
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	if($.type(data) === 'string'){
		                		window.location.href = data;
		                	}else{
		                	get_response_msg(to, from, text);
		                	if(data.msg != ''){
								messages['u_'+to].push(data.msg);
								$('.popup-chat[data-id="'+to+'"] .chat-message-field').append(display_messages(data.msg, to, data.user_img, data.auth_img));
								var scroll = $('.popup-chat[data-id="'+to+'"] .mCustomScrollbar');
								scroll.animate({ scrollTop: $(scroll).get(0).scrollHeight }, 500);
								if(data.check_adm){
	  var tpl= "";
	  	tpl += "<li data-id='"+data.msg[0].to_user+"' data-from='"+data.msg[0].from_user+"' class='message-unread' onclick='chat_open(this,event);'>";
		tpl += "<div class='author-thumb'>";
		tpl += "<img src='/storage/images/"+data.user_img+"' alt='author'>";
		tpl += "</div>";
		tpl += "<div class='notification-event'>";
		tpl += "<a href='#' class='h6 notification-friend'>"+data.to_username+" - "+data.user_name+"</a>";
		tpl += "<span class='chat-message-item'>"+data.msg[0].message+"</span>";
		tpl += "</div>";
		tpl += "<span class='notification-icon'>";
		tpl += "<svg class='olymp-chat---messages-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-chat---messages-icon'></use></svg>";
		tpl += "</span>";
		tpl += "<div class='more'>";
		tpl += "<svg class='olymp-three-dots-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-three-dots-icon'></use></svg>";
		tpl += "</div>";
		tpl += "</li>";
	  $('#chat-messages-top li[data-id="'+data.to_user_account+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  $('#chat-messages-top2 li[data-id="'+data.to_user_account+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  	var tpl2 = '';
	  	tpl2 += '<div onclick="chat_open(this,event);" data-id="'+data.msg[0].to_user+'" data-from="'+data.msg[0].from_user+'" class="mess__item">';
        tpl2 += '<div class="image img-cir img-40">';
        tpl2 += '<img src="/storage/images/'+data.user_img+'" alt="Chat">';
        tpl2 += '</div>';
        tpl2 += '<div class="content">';
        tpl2 += '<h6>'+data.to_username+' - '+data.user_name+'</h6>';
        tpl2 += '<p>'+data.msg[0].message+'</p>';
        tpl2 += '</div>';
        tpl2 += '</div>';
        $('#chat-admin-cont div[data-id="'+data.to_user_account+'"][data-from="'+data.msg[0].from_user+'"]').remove();

        var tpl4= "";
        tpl4 += "<li data-id='"+data.msg[0].to_user+"' data-from='"+data.msg[0].from_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
		tpl4 += "<div class='author-thumb'>";
		tpl4 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
		tpl4 += "<span  data='status' class='icon-status online'></span>";
		tpl4 += "</div>";
		tpl4 += "<div class='author-status'>";
		tpl4 += "<a href='#' class='h6 author-name'>"+data.to_username+" - "+data.user_name+"</a>";
		tpl4 += "</div>";

		}else{
			var tpl= "";
	  	tpl += "<li data-id='"+data.msg[0].to_user+"' data-from='"+data.msg[0].from_user+"' class='message-unread' onclick='chat_open(this,event);'>";
		tpl += "<div class='author-thumb'>";
		tpl += "<img src='/storage/images/"+data.user_img+"' alt='author'>";
		tpl += "</div>";
		tpl += "<div class='notification-event'>";
		tpl += "<a href='#' class='h6 notification-friend'>"+data.user_name+"</a>";
		tpl += "<span class='chat-message-item'>"+data.msg[0].message+"</span>";
		tpl += "</div>";
		tpl += "<span class='notification-icon'>";
		tpl += "<svg class='olymp-chat---messages-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-chat---messages-icon'></use></svg>";
		tpl += "</span>";
		tpl += "<div class='more'>";
		tpl += "<svg class='olymp-three-dots-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-three-dots-icon'></use></svg>";
		tpl += "</div>";
		tpl += "</li>";
	  $('#chat-messages-top li[data-id="'+data.msg[0].to_user+'"]').remove();

	  	var tpl4= "";
        tpl4 += "<li data-id='"+data.msg[0].to_user+"' data-from='"+data.msg[0].from_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
		tpl4 += "<div class='author-thumb'>";
		tpl4 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
		tpl4 += "<span  data='status' class='icon-status online'></span>";
		tpl4 += "</div>";
		tpl4 += "<div class='author-status'>";
		tpl4 += "<a href='#' class='h6 author-name'>"+data.user_name+"</a>";
		tpl4 += "</div>";
		}
		var tpl3= "";
		tpl3 += "<li data-id='"+data.msg[0].to_user+"' data-from='"+data.msg[0].from_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
        tpl3 += "<div class='author-thumb'>";
        tpl3 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
        tpl3 += "<span class='icon-status online'></span>";
        tpl3 += "</div>";
        tpl3 += "</li>";


	  $('#chat-messages-top').prepend(tpl);
	  $('#chat-messages-top2').prepend(tpl);
	  $('#chat-admin-cont').prepend(tpl2);
	  $('#chat-users-small .js-chat-open[data-id="'+data.msg[0].to_user+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  $('#chat-users-small').prepend(tpl3);
	  $('#chat-users-large .js-chat-open[data-id="'+data.msg[0].to_user+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  $('#chat-users-large').prepend(tpl4);

	  $('#chat-users-small-responsive .js-chat-open[data-id="'+data.msg[0].to_user+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  $('#chat-users-small-responsive').prepend(tpl3);
	  $('#chat-users-large-responsive .js-chat-open[data-id="'+data.msg[0].to_user+'"][data-from="'+data.msg[0].from_user+'"]').remove();
	  $('#chat-users-large-responsive').prepend(tpl4);
		$('#credits_header').html(data.credits);
							}


						}
		            	}
		        });
			var id = $(t).attr('data-id');
		var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/seen',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		id: id,
		                    		from: from
		                    	},
		                    dataType: 'JSON',
			                success: function (result)
			                {
			                	var counter = $('#chat-unread-counter').html();
								$('#chat-unread-counter').html(parseInt(counter)-result.counter);
								$('.js-chat-open[data-from="'+from+'"][data-id="'+id+'"] .unread-right[data-from="'+id+'"]').remove();
								$('#chat-messages-top li[data-id="'+id+'"]').removeClass('message-unread');
	  							$("#admin_account option[value='"+from+"']").text(result.user_name);

			            	}
			        });
		        }
	    }
	};
    if (window.Pusher != undefined) {
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
            //console.log('Private Channel');
        });

        channel.bind('newMessage', function(data) {
          data = data.payload;
          if ($(window).width() > 768) {
              chat_open(0,0,data.msg[0].from_user, data.msg[0].to_user);
          }
          if(messages['u_'+data.msg[0].from_user]){
            messages['u_'+data.msg[0].from_user].push(data.msg);
          }
          var counter_unread = $('.js-chat-open[data-from="'+data.msg[0].to_user+'"][data-id="'+data.msg[0].from_user+'"] .unread-right[data-from="'+data.msg[0].from_user+'"]');
          if(counter_unread.length != 0){
            var ct = counter_unread.html();
            counter_unread.html(parseInt(ct)+1);
          }else{
            $('.js-chat-open[data-id="'+data.msg[0].from_user+'"][data-from="'+data.msg[0].to_user+'"] .author-thumb').append("<div data-from='"+data.msg[0].from_user+"' class='label-avatar unread-right bg-orange'>1</div>");
          }

          if($('.popup-chat[data-id="'+data.msg[0].from_user+'"]').length != 0){
            var block = $('.popup-chat[data-id="'+data.msg[0].from_user+'"]');
            var from = block.attr('data-from');
                    if(typeof from !== typeof undefined && from !== false){
                        var from = block.attr('data-from');
                    }else{
                        var from = 'no';
                    }
                if(from != 'no'){
                    if(block.attr('data-from') == data.msg[0].to_user)
              $('.popup-chat[data-id="'+data.msg[0].from_user+'"] .chat-message-field').append(display_messages(data.msg, data.msg[0].from_user, data.user_img, data.auth_img));
              var scroll = $('.popup-chat[data-id="'+data.msg[0].from_user+'"] .mCustomScrollbar');
              scroll.animate({ scrollTop: $(scroll).get(0).scrollHeight }, 500);
              var counter = $('#chat-unread-counter').html();
              $('#chat-unread-counter').html(parseInt(counter)+1);
              var counter2 = $('#chat-unread-counter2').html();
              $('#chat-unread-counter2').html(parseInt(counter2)+1);
              var counter3 = $('#chat-unread-counter3').html();
              $('#chat-unread-counter3').html(parseInt(counter3)+1);
              if($('.popup-chat[data-id="'+data.msg[0].from_user+'"]').hasClass('popup-chat-toggle')){
                $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').removeClass('popup-chat-toggle');
                $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100).animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100);
              }
            }else{
                $('.popup-chat[data-id="'+data.msg[0].from_user+'"] .chat-message-field').append(display_messages(data.msg, data.msg[0].from_user, data.user_img, data.auth_img));
              var scroll = $('.popup-chat[data-id="'+data.msg[0].from_user+'"] .mCustomScrollbar');
              scroll.animate({ scrollTop: $(scroll).get(0).scrollHeight }, 500);
              var counter = $('#chat-unread-counter').html();
              $('#chat-unread-counter').html(parseInt(counter)+1);
              var counter2 = $('#chat-unread-counter2').html();
              $('#chat-unread-counter2').html(parseInt(counter2)+1);
              var counter3 = $('#chat-unread-counter3').html();
              $('#chat-unread-counter3').html(parseInt(counter3)+1);
              if($('.popup-chat[data-id="'+data.msg[0].from_user+'"]').hasClass('popup-chat-toggle')){
                $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').removeClass('popup-chat-toggle');
                $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100).animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100);
              }
            }
          }else{
            var counter = $('#chat-unread-counter').html();
            $('#chat-unread-counter').html(parseInt(counter)+1);
            var counter2 = $('#chat-unread-counter2').html();
              $('#chat-unread-counter2').html(parseInt(counter2)+1);
              var counter3 = $('#chat-unread-counter3').html();
              $('#chat-unread-counter3').html(parseInt(counter3)+1);
            $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').removeClass('popup-chat-toggle');
            $('.popup-chat[data-id="'+data.msg[0].from_user+'"]').animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100).animate({bottom: '0px'}, 100).animate({bottom: '-15px'}, 100);
          }
          if(data.check_adm){
            var usr_from = data.to_username;
            $("#admin_account option[value='"+data.msg[0].to_user+"']").text(usr_from+" (mesaj nou)");
          var tpl= "";
            tpl += "<li data-id='"+data.msg[0].from_user+"' data-from='"+data.msg[0].to_user+"' class='message-unread' onclick='chat_open(this,event);'>";
            tpl += "<div class='author-thumb'>";
            tpl += "<img src='/storage/images/"+data.user_img+"' alt='author'>";
            tpl += "</div>";
            tpl += "<div class='notification-event'>";
            tpl += "<a href='#' class='h6 notification-friend'>"+data.user_name+" - "+data.to_username+"</a>";
            tpl += "<span class='chat-message-item'>"+data.msg[0].message+"</span>";
            tpl += "</div>";
            tpl += "<span class='notification-icon'>";
            tpl += "<svg class='olymp-chat---messages-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-chat---messages-icon'></use></svg>";
            tpl += "</span>";
            tpl += "<div class='more'>";
            tpl += "<svg class='olymp-three-dots-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-three-dots-icon'></use></svg>";
            tpl += "</div>";
            tpl += "</li>";
          $('#chat-messages-top li[data-id="'+data.msg[0].from_user+'"][data-from="'+data.to_user_account+'"]').remove();
          $('#chat-messages-top2 li[data-id="'+data.msg[0].from_user+'"][data-from="'+data.to_user_account+'"]').remove();
            var tpl2 = '';
            tpl2 += '<div onclick="chat_open(this,event);" data-id="'+data.msg[0].from_user+'" data-from="'+data.msg[0].to_user+'" class="mess__item">';
            tpl2 += '<div class="image img-cir img-40">';
            tpl2 += '<img src="/storage/images/'+data.user_img+'" alt="Chat">';
            tpl2 += '</div>';
            tpl2 += '<div class="content">';
            tpl2 += '<h6>'+data.user_name+' - '+data.to_username+'</h6>';
            tpl2 += '<p>'+data.msg[0].message+'</p>';
            tpl2 += '</div>';
            tpl2 += '</div>';
            $('#chat-admin-cont div[data-id="'+data.msg[0].from_user+'"][data-from="'+data.to_user_account+'"]').remove();

            var tpl4= "";
            tpl4 += "<li data-id='"+data.msg[0].from_user+"' data-from='"+data.msg[0].to_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
            tpl4 += "<div class='author-thumb'>";
            tpl4 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
            tpl4 += "<span  data='status' class='icon-status online'></span>";
            tpl4 += "</div>";
            tpl4 += "<div class='author-status'>";
            tpl4 += "<a href='#' class='h6 author-name'>"+data.user_name+" - "+data.to_username+"</a>";
            tpl4 += "</div>";

            }else{
                var tpl= "";
            tpl += "<li data-id='"+data.msg[0].from_user+"' class='message-unread' onclick='chat_open(this,event);'>";
            tpl += "<div class='author-thumb'>";
            tpl += "<img src='/storage/images/"+data.user_img+"' alt='author'>";
            tpl += "</div>";
            tpl += "<div class='notification-event'>";
            tpl += "<a href='#' class='h6 notification-friend'>"+data.user_name+"</a>";
            tpl += "<span class='chat-message-item'>"+data.msg[0].message+"</span>";
            tpl += "</div>";
            tpl += "<span class='notification-icon'>";
            tpl += "<svg class='olymp-chat---messages-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-chat---messages-icon'></use></svg>";
            tpl += "</span>";
            tpl += "<div class='more'>";
            tpl += "<svg class='olymp-three-dots-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-three-dots-icon'></use></svg>";
            tpl += "</div>";
            tpl += "</li>";
          $('#chat-messages-top li[data-id="'+data.msg[0].from_user+'"]').remove();

            var tpl4= "";
            tpl4 += "<li data-id='"+data.msg[0].from_user+"' data-from='"+data.msg[0].to_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
            tpl4 += "<div class='author-thumb'>";
            tpl4 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
            tpl4 += "<span  data='status' class='icon-status online'></span>";
            tpl4 += "</div>";
            tpl4 += "<div class='author-status'>";
            tpl4 += "<a href='#' class='h6 author-name'>"+data.user_name+"</a>";
            tpl4 += "</div>";
            }

            var tpl3= "";
            tpl3 += "<li data-id='"+data.msg[0].from_user+"' data-from='"+data.msg[0].to_user+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
            tpl3 += "<div class='author-thumb'>";
            tpl3 += "<img alt='author' src='/storage/images/"+data.user_img+"' class='avatar'>";
            tpl3 += "<span class='icon-status online'></span>";
            if(counter_unread.length != 0){
                var ct = counter_unread.html();
                var tpl3_counter = parseInt(ct)+1;
                tpl3 += "<div data-from='"+data.msg[0].from_user+"' class='label-avatar unread-right bg-orange'>"+tpl3_counter+"</div>";
            }else{
                tpl3 += "<div data-from='"+data.msg[0].from_user+"' class='label-avatar unread-right bg-orange'>1</div>";
            }
            tpl3 += "</div>";
            tpl3 += "</li>";


          $('#chat-messages-top').prepend(tpl);
          $('#chat-messages-top2').prepend(tpl);
          $('#chat-admin-cont').prepend(tpl2);
          $('#chat-users-small .js-chat-open[data-id="'+data.msg[0].from_user+'"][data-from="'+data.msg[0].to_user+'"]').remove();
          $('#chat-users-small').prepend(tpl3);
          $('#chat-users-large .js-chat-open[data-id="'+data.msg[0].from_user+'"][data-from="'+data.msg[0].to_user+'"]').remove();
          $('#chat-users-large').prepend(tpl4);

          $('#chat-users-small-responsive .js-chat-open[data-id="'+data.msg[0].from_user+'"][data-from="'+data.msg[0].to_user+'"]').remove();
          $('#chat-users-small-responsive').prepend(tpl3);
          $('#chat-users-large-responsive .js-chat-open[data-id="'+data.msg[0].from_user+'"][data-from="'+data.msg[0].to_user+'"]').remove();
          $('#chat-users-large-responsive').prepend(tpl4);

          new_message_sound.play();
        });
        channel.bind('newFriendRequest', function(data) {
            data = data.payload;
            if(data['type'] != 'delete'){
                var tpl= "";
                tpl += "<li tpl-id='"+data['user_data'].id+"'>";
                tpl += "<div class='author-thumb'>";
                tpl += "<img src='/storage/images/"+data['user_data'].profile_image+"' alt='author'>";
                tpl += "</div>";
                tpl += "<div class='notification-event'>";
                tpl += "<a href='/profile/"+data['user_data'].username+"' class='h6 notification-friend'>"+data['user_data'].name+"</a>";
                tpl += "<span class='chat-message-item'>Since: "+data['date']+"</span>";
                tpl += "</div>";
                tpl += "<span class='notification-icon'>";
                tpl += "<a href='/acc-friend/"+data['user_data'].id+"' onClick='acc_fr_req(this, event);' class='accept-request acc-fr-req'>";
                tpl += "<span class='icon-add without-text'>";
                tpl += "<svg class='olymp-happy-face-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-happy-face-icon'></use></svg>";
                tpl += "</span>";
                tpl += "</a>";
                tpl += "<a href='/delete-request/"+data['user_data'].id+"' onClick='del_fr_req(this, event);' class='accept-request request-del del-fr-req'>";
                tpl += "<span class='icon-minus'>";
                tpl += "<svg class='olymp-happy-face-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-happy-face-icon'></use></svg>";
                tpl += "</span>";
                tpl += "</a>";
                tpl += "</span>";
                tpl += "<div class='more'>";
                tpl += "<svg class='olymp-three-dots-icon'><use xlink:href='/svg-icons/sprites/icons.svg#olymp-three-dots-icon'></use></svg>";
                tpl += "</div>";
                tpl += "</li>";
                var counter = $('#fr-req-counter').html();
                $('#fr-req-counter').html(parseInt(counter)+1);
                var counter2 = $('#fr-req-counter2').html();
                $('#fr-req-counter2').html(parseInt(counter2)+1);
                if($('#friend-requests-block li').length == 0){
                    $('#friend-requests-block').html(tpl);
                }else{
                    $('#friend-requests-block').prepend(tpl);
                }
                if($('#friend-requests-block2 li').length == 0){
                    $('#friend-requests-block2').html(tpl);
                }else{
                    $('#friend-requests-block2').prepend(tpl);
                }
            }else{
                var counter = $('#fr-req-counter').html();
                var counter2 = $('#fr-req-counter2').html();
                $('#friend-requests-block li[tpl-id="'+data['request_data'].user_from+'"]').remove();
                $('#friend-requests-block2 li[tpl-id="'+data['request_data'].user_from+'"]').remove();
                $('#fr-req-counter').html(parseInt(counter)-1);
                $('#fr-req-counter2').html(parseInt(counter2)-1);
                if($('#friend-requests-block li').length == 0){
                    $('#friend-requests-block').html("<span class='no-req'>No friend requests</span>");
                }
                if($('#friend-requests-block2 li').length == 0){
                    $('#friend-requests-block2').html("<span class='no-req'>No friend requests</span>");
                }
            }

        });
        channel.bind('accFriendRequest', function(data){
            data = data.payload;
            var ch_ic = "<li data-id='"+data.from+"' onClick='chat_open(this,event);' class='inline-items js-chat-open'>";
                ch_ic += "<div class='author-thumb'>";
                ch_ic += "<img alt='author' src='/storage/images/"+data.profile_image+"' class='avatar'>";
                ch_ic += "<span class='icon-status "+data.status+"'></span>";
                ch_ic += "</div>";
                ch_ic += "</li>";
                $("#chat-users-small").append(ch_ic);
                if($('#add-friend-btn').attr('data-id') == data.from){
                    $('#add-friend-btn span').html(data.txt);
                    $('#add-friend-btn').attr('href', data.url);
                }
        });
    }
    function load_messages(t){
		var id = $(t).parent().attr('data-id');
		var scrollTop = $(t).scrollTop();

		var from = $(t).parent().attr('data-from');
				if(typeof from !== typeof undefined && from !== false){
					var from = $(t).parent().attr('data-from');
				}else{
					var from = 'no';
				}
		if(user_reg == 'guest'){
			var from = 'guest';
		}

		if(scrollTop <= 0){
				var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/load-messages',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		to: id,
		                    		from:from,
		                    		msg_count: messages['u_'+id].length
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	messages['u_'+id] = data.msg.concat(messages['u_'+id]);
			                	var h = $('.popup-chat[data-id="'+id+'"] .chat-message-field').outerHeight();
								$('.popup-chat[data-id="'+id+'"] .chat-message-field').prepend(display_messages(data.msg, id, data.user_img, data.auth_img));
								var a_h = $('.popup-chat[data-id="'+id+'"] .chat-message-field').outerHeight();
								$(t).scrollTop(a_h-h);
			            	}
			        });
		}
    };
    function find_friends_change(){
    	$("#load_more_find_friends").show();
    	var CSRF_TOKEN = $('input[name="_token"]').val();
    	var country = $('#find-friends-country').val();
    	var language = $('#find-friends-language').val();
    	//console.log(country);
				$.ajax({
			                url: '/find-friends',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		country: country,
		                    		language:language,
		                    		age: $('.range-slider-js').val()
		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	if(data.results != 0){
			                		$('#find_friends_results').html(data.tpl);
			                	}else{
			                		$('#find_friends_results').html(data.tpl);
			                		$("#load_more_find_friends").hide();
			                	}
			                	$('[data-toggle="tooltip"]').tooltip();
			            	}
			        });

    };
    $("#load_more_find_friends").click(function(e){
    	e.preventDefault();
    	var CSRF_TOKEN = $('input[name="_token"]').val();
    	var country = $('#find-friends-country').val();
    	var language = $('#find-friends-language').val();
				$.ajax({
			                url: '/find-friends',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		country: country,
		                    		language:language,
		                    		age: $('.range-slider-js').val(),
		                    		items: $('.friend-item').length		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	if(data.results != 0){
			                		$('#find_friends_results').append(data.tpl);
			                	}else{
			                		$('#find_friends_results').append(data.tpl);
			                		$("#load_more_find_friends").hide();
			                	}
			                	$('[data-toggle="tooltip"]').tooltip();
			            	}
			        });
    });
    function modal_hide(id){
    	this.event.preventDefault();
    	$('#'+id).modal('hide');
    };
    var album_items = new Array();
    $('#choose-from-my-photo-submit').click(function(e){
    	e.preventDefault();
    	if(image_role == 'profile'){

    		var photo_url = $(".optionsRadios:checked").attr('data-url');
    		$('#choose-from-my-photo').modal('toggle');
    		$('#show-preview').show();
				$crop.croppie('bind', {
					url:photo_url
				}).then(function(){
					//console.log('Bind complete');
				});
			$('#upload-preview').show();

    	}else if(image_role == 'cover'){

    		var photo_url = $(".optionsRadios:checked").attr('data-url');
    		$('#choose-from-my-photo').modal('toggle');
    		$('#show-preview').show();
				$crop.croppie('bind', {
					url:photo_url
				}).then(function(){
					//console.log('Bind complete');
				});
			$('#upload-preview').show();

    	}else{
    	var items = new Array();
    	$(".optionsRadios:checked").each(function() {
			items.push($(this).val());
		});
		album_items = items;
		if(album_items.length > 0){
			$('#choose-album-first').hide();
    		$('#choose-album-second').show();
    	}
    }
    });

    function load_update(t){
    	this.event.preventDefault();
    	var items = $('.choose-photo-item .checks').length;
    	var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/load-upload',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		items: items		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	$('#choose-photo-item-container').append(data.tpl);
			                	if(data.number < 12){
			                		$(t).hide();
			                	}
			            	}
			        });
    };
    function album_previous(){
    	this.event.preventDefault();
    	$('#choose-album-first').show();
    	$('#choose-album-second').hide();
    };
    var selected_album = '';
    function select_album(t){
    	$('.choose-photo-item').removeClass('border-selected');
    	$(t).addClass('border-selected');
    	selected_album = $(t).attr('data-id');
    	$('#add-from-my-photo-submit').removeClass('disabled');
    };

    $('#add-from-my-photo-submit').click(function(e){
    	e.preventDefault();
    	var CSRF_TOKEN = $('input[name="_token"]').val();
				$.ajax({
			                url: '/attach-to-album',
			                type: 'POST',
			                data: {_token: CSRF_TOKEN,
		                    		album: selected_album,
		                    		photos: album_items		                    	},
		                    dataType: 'JSON',
			                success: function (data)
			                {
			                	window.location.href = data;
			            	}
			        });
    });

    function delete_image(t, id = false) {
    	this.event.preventDefault();
		var CSRF_TOKEN = $('input[name="_token"]').val();
		if(id){
			var img = t;
			var check = true;
		}else{
			var img = $(t).attr('data-id');
			var check = confirm("Are you sure you want to delete this image?");

		}
		if(check == true){
    	$.ajax({
	                url: '/delete-album-photo',
	                type: 'POST',
	                data: {_token: CSRF_TOKEN,
                    		images: img,
                    		album: true
                    	},
                    dataType: 'JSON',
	                success: function (data)
	                {
	                	if(!data.deleted){
	                		var ind = swipers['swiper-swiper-unique-id-0'].activeIndex;
	                		swipers['swiper-swiper-unique-id-0'].removeSlide(ind-1);
	                		$('.slides-item[data-desc="'+data.image+'"]').remove();
	                		swipers['swiper-swiper-unique-id-0'].update();
	                		init_swiper();
	                		swipers['swiper-swiper-unique-id-0'].slideReset();
	                	}else{
	                		window.location.href = data.route;
	                	}
	            	}
	        });
	    }
	};
	function alert_delete_photo(id) {
		this.event.preventDefault();
		var check = confirm("Are you sure you want to delete this image?");
		if(check == true){
			delete_image(id, true);
			$('.photo-item[data-id="'+id+'"]').remove();
		}
	};
	var url = document.location.toString();
	if (url.match('#')) {
		$('.nav-item a[href="#' + url.split('#')[1] + '"]').tab('show');
	}


	function delete_album() {
		this.event.preventDefault();
		var CSRF_TOKEN = $('input[name="_token"]').val();
		var check = confirm("Are you sure you want to delete this album?\nNote: Images will NOT be deleted.");
		if(check == true){
	    	$.ajax({
		                url: '/delete-album',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		id: currently_showing
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                		window.location.href = data;
		            	}
		        });
	    }
	};
	function slide_next() {
		swipers['swiper-swiper-unique-id-0'].slideNext();
	};
	function slide_prev() {
		swipers['swiper-swiper-unique-id-0'].slidePrev();
	};
	$('.new-photo-popup').magnificPopup({
		type: 'image',
		removalDelay: 500, //delay removal by X to allow out-animation
		callbacks: {
			beforeOpen: function () {
				// just a hack that adds mfp-anim class to markup
				this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
				this.st.mainClass = 'mfp-zoom-in';
			}
		},
		closeOnContentClick: true,
		midClick: true});

function isOnScreen(elem) {
	// if the element doesn't exist, abort
	if( elem.length == 0 ) {
		return;
	}
	var $window = jQuery(window)
	var viewport_top = $window.scrollTop()
	var viewport_height = $window.height()
	var viewport_bottom = viewport_top + viewport_height
	var $elem = jQuery(elem)
	var top = $elem.offset().top
	var height = $elem.height()
	var bottom = top + height

	return (top >= viewport_top && top < viewport_bottom) ||
	(bottom > viewport_top && bottom <= viewport_bottom) ||
	(height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
}


	if($('#newsfeed-items-grid').length == 1){
		$(window).scroll(function(){
			if(isOnScreen($('#newsfeed_loader'))){
				var posts = $('#newsfeed-items-grid article').length;
		   		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		var from = $('#newsfeed-items-grid').attr('data-id');
		   		$.ajax({
		                url: '/load-posts',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		posts: posts,
	                    		from: from
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('#newsfeed-items-grid').append(data);
		                	$('.js-zoom-image').magnificPopup({
								type: 'image',
								removalDelay: 500, //delay removal by X to allow out-animation
								callbacks: {
									beforeOpen: function () {
										// just a hack that adds mfp-anim class to markup
										this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
										this.st.mainClass = 'mfp-zoom-in';
									}
								},
								closeOnContentClick: true,
								midClick: true
							});
		            	}
		        });
		   	}
		});
	};
	if($('#chat-users-small').length == 1){
		$('#chat-users-small').parent().scroll(function(){
			if(isOnScreen($('#chat_load_threshold'))){
				var chats = $('#chat-users-small .js-chat-open').length;
		   		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/chat/load-right',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		chats: chats
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('#chat-users-small').append(data.tpl_sm);
		                	$('#chat-users-large').append(data.tpl_lg);
		            	}
		        });
		   	}
		});
	};
	if($('#chat-users-large').length == 1){
		$('#chat-users-large').parent().scroll(function(){
			if(isOnScreen($('#chat_load_threshold_lg'))){
				var chats = $('#chat-users-large .js-chat-open').length;
		   		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/chat/load-right',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		chats: chats
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('#chat-users-small').append(data.tpl_sm);
		                	$('#chat-users-large').append(data.tpl_lg);
		            	}
		        });
		   	}
		});
	};
	if($('#chat-messages-top').length == 1){
		$('#chat-messages-top').parent().scroll(function(){
			if(isOnScreen($('#chat_load_threshold_top'))){
				var chats = $('#chat-messages-top .mess__item').length;
		   		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/chat/load-top',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		chats: chats
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('#chat-messages-top').append(data);
		            	}
		        });
		   	}
		});
	};
	function like_post(id){
		this.event.preventDefault();
		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/like',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		post: id
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
			               if(data.status == 'liked'){
				               	$('.post[data-post="'+data.post_id+'"] .likes').addClass('active');
				                $('.post[data-post="'+data.post_id+'"] .likes span').html(data.post_likes);
				            }else if(data.status == 'unliked'){
				                $('.post[data-post="'+data.post_id+'"] .likes').removeClass('active');
				                $('.post[data-post="'+data.post_id+'"] .likes span').html(data.post_likes);
				            }else if(data.status == 'packages'){
				            	window.location.href = "/"+data.status;
				            }
		            	}
		        });
	};
	function notifications_seen() {
		this.event.preventDefault();
		var CSRF_TOKEN = $('input[name="_token"]').val();
	    	$.ajax({
		                url: '/notification/seen',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		seen: 'all'
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                		$('#unread-notifications').html(data);
		                		$('.notification-list li').removeClass('un-read');
		            	}
		        });
	};
	$('.notification-link-btn').click(function(){
		var lk = $(this).attr('data-link');
		window.location.href = lk;
	});

	$("#profile_country").change(function(){

		var option = $('option:selected', this).attr('data-id');
		//console.log(option);
		var CSRF_TOKEN = $('input[name="_token"]').val();
	    	$.ajax({
		                url: '/settings/getField',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		option: option,
	                    		field: 'state'
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                		//console.log(data);
		                		$("#profile_state").empty().append(data);
		            	}
		        });
    });
    $("#profile_state").change(function(){

		var option = $('option:selected', this).attr('data-state');
		//console.log(option);
		var CSRF_TOKEN = $('input[name="_token"]').val();
	    	$.ajax({
		                url: '/settings/getField',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		option: option,
	                    		field: 'city'
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                		//console.log(data);
		                		$("#profile_city").empty().append(data);
		            	}
		        });
    });
    $('#closeResponsiveSearch').click(function(){
    	$('.js-user-search').hide();
    	$('.js-user-search').removeAttribute('style');
    });

    function search_friends(t){
    	var search = $(t).val();
    	var CSRF_TOKEN = $('input[name="_token"]').val();
	    	$.ajax({
		                url: '/friends/search',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		search: search
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                		$(".fixed-sidebar-right.sidebar--large .mCustomScrollbar .chat-users").empty().append(data);
		            	}
		        });
    }

    $('.open_online_users').click(function(e){
    	e.preventDefault();
    	if($(this).hasClass('active_online')){
    		$(this).removeClass('active_online');
	    	$('#online_users_left').hide();
    	}else{
	    	$(this).addClass('active_online');


	    	var CSRF_TOKEN = $('input[name="_token"]').val();
	    	$.ajax({
		                url: '/users/get-online',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		count: 5,
	                    		skip: 0
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('.online_users_left_content').html(data);
		                	$('#online_users_left').show();
		            	}
		        });

	    }
    });

    if($('#online_users_left').length == 1){
		$('#online_users_left').scroll(function(){
			if($('#online_users_left').scrollTop() + $('#online_users_left').innerHeight() >= $('#online_users_left')[0].scrollHeight){
				var skip = $('.online_users_left_content a').length;
		   		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/users/get-online',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN,
	                    		count: 5,
	                    		skip: skip
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	$('.online_users_left_content').append(data);
		                	$('#online_users_left').show();
		            	}
		        });
		   	}
		});
	};

	function get_discount(){
		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/get-discount',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	if(data != false){
			                	var discount_text = '-'+ data.value + '%';
			                	$('#discount_top'). removeClass('no_display');
			                	$('#discount_top span').html(discount_text);
			                	$('#discount_top_mobile'). removeClass('no_display');
			                	$('#discount_top_mobile span').html(discount_text);
			                }
		            	}
		        });
	}

if($('.discount-countdown').length ==1){
var dateString = $('.discount-countdown').attr('data-count');
var discount_deadline = new Date(dateString.replace(' ', 'T')).getTime();
var discount_x = setInterval(function() {
var discount_now = new Date().getTime();
var discount_t = discount_deadline - discount_now;
var discount_days = Math.floor(discount_t / (1000 * 60 * 60 * 24));
var discount_hours = Math.floor((discount_t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60));
var discount_minutes = Math.floor((discount_t % (1000 * 60 * 60)) / (1000 * 60));
var discount_seconds = Math.floor((discount_t % (1000 * 60)) / 1000);
document.getElementById("discount-countdown").innerHTML = discount_days + "d " + discount_hours + "h " + discount_minutes + "m " + discount_seconds + "s ";
//document.getElementById("discount-countdown").innerHTML = discount_hours + "h " + discount_minutes + "m " + discount_seconds + "s ";
    if (discount_t < 0) {
        clearInterval(discount_x);
        document.getElementById("discount-countdown").innerHTML = "EXPIRED";
    }
}, 1000);
}
var live_feed_time = 35000;
var live_feed_time_last = 7500;
function get_livefeed(){
		var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/get-livefeed',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	var display_type = randomNumber(0, 100);
		                	if(display_type < 25){
			                	$('#livefeed_container').css('visibility', 'visible');
			                	$('#livefeed_container').css('opacity', '1');
			                	$('#livefeed_name').attr('href', '/profile/'+data.user.username);
			                	$('#livefeed_name').html(data.user.firstname+' '+data.user.lastname);
			                	$('#livefeed_pack').attr('href', '/payments?pack_id='+data.pack.id);
			                	$('#livefeed_pack').html(data.pack.name);
			                	$('#livefeed_pack').show();
			                	$('#livefeed_img_link').attr('href', '/profile/'+data.user.username);
			                	$('#livefeed_img').attr('src', '/storage/images/'+data.user.profile_image);
			                	$('#livefeed_time').html(data.time+' '+data.time_unit);
			                	$('#livefeed_time_container').show();
			                	$('#livefeed_text1').html(' purchased a ');
			                	$('#livefeed_text2').show();
			                }else{
			                	$('#livefeed_container').css('visibility', 'visible');
			                	$('#livefeed_container').css('opacity', '1');
			                	$('#livefeed_name').attr('href', '/profile/'+data.user.username);
			                	$('#livefeed_name').html(data.user.firstname+' '+data.user.lastname);
			                	$('#livefeed_pack').attr('href', '/payments?pack_id='+data.pack.id);
			                	$('#livefeed_pack').html(data.pack.name);
			                	$('#livefeed_pack').hide();
			                	$('#livefeed_img_link').attr('href', '/profile/'+data.user.username);
			                	$('#livefeed_img').attr('src', '/storage/images/'+data.user.profile_image);
			                	$('#livefeed_time_container').hide();
			                	$('#livefeed_text1').html(' is now Online!');
			                	$('#livefeed_text2').hide();
			                }
		                	setTimeout(hide_livefeed, live_feed_time_last);

		            	}
		        });
	setTimeout(get_livefeed, live_feed_time);
	}
function hide_livefeed(){
	$('#livefeed_container').css('visibility', 'hidden');
	$('#livefeed_container').css('opacity', '0');
}

$('#livefeed_close').click(function(){
	hide_livefeed();
});

function friend_request_live(){
	var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/friend-request-live',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	setTimeout(friend_request_live, 70000);
		            	}
		        });
}
var autoMessageTime = 10000;
function auto_message(){
	var CSRF_TOKEN = $('input[name="_token"]').val();
		   		$.ajax({
		                url: '/chatbot/auto-message',
		                type: 'POST',
		                data: {_token: CSRF_TOKEN
	                    	},
	                    dataType: 'JSON',
		                success: function (data)
		                {
		                	setTimeout(auto_message, autoMessageTime);
		            	}
		        });
}

function randomNumber(min, max) {
  return Math.random() * (max - min) + min;
}
window.onload = function() {
	setTimeout(get_discount, 5000);
	setTimeout(friend_request_live, 15000);
  	setTimeout(get_livefeed, live_feed_time);
  	setTimeout(auto_message, autoMessageTime);
  //get_livefeed();
}
function change_user_lang(t){
	var CSRF_TOKEN = $('input[name="_token"]').val();
	$.ajax({
		    url: '/change-lang/'+$(t).parent().attr('data-id'),
		    type: 'POST',
		    data: {_token: CSRF_TOKEN,
		    		lang: $(t).val()
	              },
	        dataType: 'JSON',
		        success: function (data)
		        {
		        	//console.log(data);
		      	}
	});
}

var msg_remove = false;
function show_hide_messages(t, event){
	data_id = $(t).parent().parent().parent().attr('data-id');
	// console.log(data_id);
	event.stopPropagation();
	if(msg_remove){
		msg_remove = false;
		$('.popup-chat[data-id="'+data_id+'"] .msgtoggle').html('<i class="fas fa-eye-slash" onclick="show_hide_messages(this, event);"></i>');
		$('.popup-chat[data-id="'+data_id+'"] .notification-list.chat-message').show();

	}else{
		msg_remove = true;
		$('.popup-chat[data-id="'+data_id+'"] .msgtoggle').html('<i class="fas fa-eye" onclick="show_hide_messages(this, event);"></i>');
		$('.popup-chat[data-id="'+data_id+'"] .notification-list.chat-message').hide();
	}
}
