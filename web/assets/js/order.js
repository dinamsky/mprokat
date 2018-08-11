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

    $('#owner_reject').on('click', function () {
        var id = $(this).val();

        $.ajax({
            url: '/ajax_owner_reject',
            type: 'POST',
            data: {id:id},
            dataType: 'html',
            success: function (html) {
                //console.log(html);
                document.location.href = window.location.href;
            }
        });
    });

    $('#owner_answer').on('click', function () {
        var id = $(this).val();
        var answer = $(this).siblings('textarea[name="answer"]').val();

        $.ajax({
            url: '/ajax_owner_answer',
            type: 'POST',
            data: {id:id,answer:answer},
            dataType: 'html',
            success: function (html) {
                //console.log(html);
                document.location.href = window.location.href;
            }
        });
    });

    $('#renter_answer').on('click', function () {
        var id = $(this).val();
        var answer = $(this).siblings('textarea[name="answer"]').val();

        $.ajax({
            url: '/ajax_renter_answer',
            type: 'POST',
            data: {id:id,answer:answer},
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

    $('.ord_toggler').on('click', function () {
        var id = $(this).data('id');
        var user_id = $(this).data('user_id');
        $('.ord_toggler').removeClass('active');
        $(this).addClass('active');
        $('.ord_content').addClass('uk-hidden');
        $('#ord_desc_'+id).removeClass('uk-hidden');
        $('.ord_sum').addClass('uk-hidden');
        $('#ord_sum_'+id).removeClass('uk-hidden');
        $.ajax({
            url: '/ajax_set_ord_active',
            type: 'POST',
            data: {id:id,user_id:user_id},
            dataType: 'html',
            success: function () {
                //document.location.href = window.location.href;
            }
        });
    });

    $('.ord_pat').on('click', function () {
        var c = $(this).html();
        $(this).parents('.msgb').find('textarea[name="answer"]').val(c).focus();
    });

    // var timer = '';
    //
    // if(!$('textarea[name="answer"]').is(":focus")){
    //
    //     console.log('start');
    // }

    var timer = setInterval(function(){
        var t = $('textarea[name="answer"]');
        if(!t.is(":focus") && t.val() === '') {
            //console.log('not in focus');
            window.location.reload(true);
        } else {
            //console.log('in focus');
        }

        }, 10000);




    // $('textarea[name="answer"]').on('focus', function () {
    //     clearTimeout(timer);
    //     console.log('stop');
    // });



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

