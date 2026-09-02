<script language="Javascript" type="text/javascript">
$( ".pack" ).click(function() {
    /* save order attempt*/
    var fd = new FormData();
    fd.append('pack_id', $(this).data("pack"));
    fd.append('price', $(this).data("price"));
    fd.append('currency', $(this).data("currency"));

    $.ajax({
        type: 'POST',
        url: '{{route('packages.order.attemts.store')}}',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        data: fd,
        processData: false,
        contentType: false,
    })
    .done(function(data, textStatus){
        if(textStatus=="success") {
            console.log(data);
        }
    })
    .fail(function(data, textStatus, errorThrown){

    });

    return true;
});
</script>
