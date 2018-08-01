$( document ).ready(function() {



    $('#owner_accept').on('click', function () {
        var id = $(this).val();

        $.ajax({
            url: '/ajax_owner_accept',
            type: 'POST',
            data: {id:id},
            dataType: 'html',
            success: function (html) {
                //console.log(html);
                document.location.href = window.location.href;
            }
        });
    });


    $('#owner_pincode').on('click', function () {
        var id = $(this).val();
        var pincode = $('input[name="owner_pincode"]').val();

        $.ajax({
            url: '/ajax_owner_pincode',
            type: 'POST',
            data: {id:id,pincode:pincode},
            dataType: 'html',
            success: function (html) {
                //console.log(html);
                document.location.href = window.location.href;
            }
        });
    });

    // $('#owner_finish').on('click', function () {
    //     var id = $(this).val();
    //     $.ajax({
    //         url: '/ajax_owner_finish',
    //         type: 'POST',
    //         data: {id:id},
    //         dataType: 'html',
    //         success: function (html) {
    //             //console.log(html);
    //             document.location.href = window.location.href;
    //         }
    //     });
    // });
    //
    // $('#renter_finish').on('click', function () {
    //     var id = $(this).val();
    //     $.ajax({
    //         url: '/ajax_renter_finish',
    //         type: 'POST',
    //         data: {id:id},
    //         dataType: 'html',
    //         success: function (html) {
    //             console.log(html);
    //             //document.location.href = window.location.href;
    //         }
    //     });
    // });
});

