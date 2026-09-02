var pusher = new Pusher(pusher_key, {
    cluster: 'eu',
    forceTLS: true,
    authEndpoint: '/pusher/auth',
    auth: {
        headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
        }
    },
    //wsHost: "websockets.7lovestory7.com",
    //wsPort: 443,
    //wssPort: 443,
    //disableStats: true,
});
var global_users_online = false;

var CSRF_TOKEN = $('input[name="_token"]').val();
$.ajax({
    url: '/users/get-online',
    type: 'POST',
    data: {
        _token: CSRF_TOKEN,
        gender: 'all',
        count: 'all',
        skip: 0
    },
    dataType: 'JSON',
    success: function (data) {
        global_users_online = data;
    }
});
$.ajax({
    url: '/users/get-online',
    type: 'POST',
    data: {
        _token: CSRF_TOKEN,
        count: 'all',
        gender: 'female',
        skip: 0
    },
    dataType: 'JSON',
    success: function (data) {
        always_online = data;
    }
});

var auth_id = $("#auth_id").val();
var presenceChannel = pusher.subscribe('presence-online');
presenceChannel.bind('pusher:subscription_succeeded', function (members) {
    var date = new Date();
    global_users_online = $.extend(global_users_online, members.members);
    
    Object.keys(members.members).forEach((member_id) => {
       setFindFriendsUserState(member_id, 'online');
       setProfileUserState(member_id, 'online');
    });

    $('#chat-users-small li').each(function (index) {
        var span = $(this).find('span');
        var id = $(this).attr('data-id');
        if (global_users_online[id] != undefined) {
            if (!$(span).hasClass('away')) {
                $(span).removeClass();
                $(span).addClass('icon-status online');
            }
        } else {
            $(span).removeClass();
            $(span).addClass('icon-status disconected');
        }
    });
    $('.chat-users li').each(function (index) {
        var span = $(this).find('span[data="status"]');
        var id = $(this).attr('data-id');
        if (global_users_online[id] != undefined) {
            if (!$(span).hasClass('away')) {
                $(span).removeClass();
                $(span).addClass('icon-status online');
            }
        } else {
            $(span).removeClass();
            $(span).addClass('icon-status disconected');
        }
    });
});
presenceChannel.bind('pusher:member_added', function (member) {
    global_users_online[member.id] = member.info;
    setFindFriendsUserState(member.id, 'online');
    setProfileUserState(member.id, 'online');
    $('.popup-chat[data-id="' + member.id + '"] .icon-status').removeClass('disconected online');
    $('.popup-chat[data-id="' + member.id + '"] .icon-status').addClass('online');
    var span = $('#chat-users-small li[data-id="' + member.id + '"] span');
    if (!$(span).hasClass('away')) {
        $(span).removeClass();
        $(span).addClass('icon-status online');
    }
    var span2 = $('.chat-users li[data-id="' + member.id + '"] span[data="status"]');
    if (!$(span2).hasClass('away')) {
        $(span2).removeClass();
        $(span2).addClass('icon-status online');
    }
});
presenceChannel.bind('pusher:member_removed', function (member) {
    delete global_users_online[member.id];
    setFindFriendsUserState(member.id, 'offline');
    setProfileUserState(member.id, 'offline');
    $('.popup-chat[data-id="' + member.id + '"] .icon-status').removeClass('disconected online');
    $('.popup-chat[data-id="' + member.id + '"] .icon-status').addClass('disconected');
    var span = $('#chat-users-small li[data-id="' + member.id + '"] span');
    $(span).removeClass();
    $(span).addClass('icon-status disconected');
    var span2 = $('.chat-users li[data-id="' + member.id + '"] span[data="status"]');
    $(span2).removeClass();
    $(span2).addClass('icon-status disconected');
});

function setFindFriendsUserState(user_id, state = 'online'){
    if(!$('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"]').length && state == 'online'){
        $.ajax({
            url: '/get-find-friends-user/'+user_id,
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
            },
            dataType: 'JSON',
            success: function (data) {
                if(data.tpl){
                   var friend_item = $($.parseHTML(data.tpl));
                   friend_item.prependTo("#find_friends_results");
                }
            }
        });
    }
    $('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"] .find_friends_status').removeClass('span-online span-offline');
    $('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"] .find_friends_status').addClass('span-'+state);
    $('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"] .find_friends_status').html(state.charAt(0).toUpperCase() + state.slice(1).toLowerCase());
    if(state == 'online'){
    var element = $('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"]').clone();
    $('#find_friends_results .find_friends_item[data-user-id="' + user_id + '"]').remove();
    element.prependTo("#find_friends_results");
    }
}
function setProfileUserState(user_id, state = 'online'){
    if($('.profile_status[data-user-id="' + user_id + '"]').length){
        $('.profile_status[data-user-id="' + user_id + '"]').removeClass('span-online span-offline');
        $('.profile_status[data-user-id="' + user_id + '"]').addClass('span-'+state);
        $('.profile_status[data-user-id="' + user_id + '"]').html(state.charAt(0).toUpperCase() + state.slice(1).toLowerCase());
    }
}