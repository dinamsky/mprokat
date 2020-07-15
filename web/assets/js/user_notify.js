$( document ).ready(function() {


setInterval(function(){
    $.ajax({
        url: '/ajax_check_notify',
        type: 'POST',
        dataType: 'html',
        success: function(html){
            $('.user_image_block').find('.top_ord_notify').remove();
            $('.user_image_block').append(html);
            $('#mobile_notify_placeholder').html(html);
        }
    });
}, 15000);


});

