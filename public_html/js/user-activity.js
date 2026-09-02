function userActivityLog(name, event) {
    $.ajax({
            url: "/users/add-activity",
            type: 'POST',
            data:{
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: name,
                eventDetails: event
            },
        });
}