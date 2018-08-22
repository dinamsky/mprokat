$( document ).ready(function() {









    $('.ord_toggler').on('click', function () {
        var id = $(this).data('id');
        var user_id = $(this).data('user_id');

        $('.active_white').remove();

        $('.ord_toggler').removeClass('active');
        $(this).addClass('active').append('<div class="active_white"></div>');
        $('.ord_content').addClass('uk-hidden').removeClass('active');
        $('#ord_desc_'+id).removeClass('uk-hidden').addClass('active');
        $('.ord_sum').addClass('uk-hidden');
        $('#ord_sum_'+id).removeClass('uk-hidden');




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








    //console.log($(window).height());

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

