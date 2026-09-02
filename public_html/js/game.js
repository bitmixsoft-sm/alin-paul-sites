$(document).ready(function() {
	show_step(1);
});
function show_step(step, id=1){
	$('h2').hide();
	$('h2[data-step='+step+']').show();
	$('h1').hide();
	$('h1[data-step='+step+']').show();
	var attr = $('.step[data-step='+step+']').attr('data-id');
	if (typeof attr !== typeof undefined && attr !== false) {
		$('.step').fadeOut(600);
		$('.step[data-step='+step+'][data-id='+id+']').delay(600)
		  .queue(function (next) { 
		    $(this).css("display", "flex"); 
		    next(); 
		  });
		  switch_account(id);
	}else{
		$('.step').fadeOut(600);
		$('.step[data-step='+step+']').delay(600)
		  .queue(function (next) { 
		    $(this).css("display", "flex"); 
		    next(); 
		  });
	}
	if($('.step[data-step='+step+'] video').length != 0){
		$( "video" ).each(function() {
		  $( this ).get(0).play();
		});
	}
}
function switch_account(id){
	var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
	$.ajax({
            /* the route pointing to the post function */
            url: '/register-fake',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN,
                    id: id},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
            	$('.final_step .chat img').attr('src', '/storage/images/'+data.image);
            	$('.name').html(data.name); 
            	$('.acc-name').html(data.name);  
                $('.game-button').attr('href', '/autoregister-fake/'+data.id);
            }
    });
}
function final_step(){
	$("h1").hide();
	$("h2").hide();
	$('.step').fadeOut(600);
	$('.final_step').delay(600)
		  .queue(function (next) { 
		    $(this).show(); 
		    next(); 
	});
	$("#final_step_header").show();
	send_messages();
}
function send_messages(){
	setTimeout(function(){
		$('#message1').fadeIn(500);
	}, 2500);
	setTimeout(function(){
		$('#message2').fadeIn(500);
	}, 6500);
	setTimeout(function(){
		$('#message3').fadeIn(500);
	}, 9500);
	setTimeout(function(){
		$('.chat-box').fadeIn(500);
	}, 10500);
}