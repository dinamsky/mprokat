$( document ).ready(function() {
    var myInterval;

    $('.open_chat').on('click', function () {
        $(this).addClass('active');
        var user_id = $(this).data('user_id');
        var visitor_id = $(this).data('visitor_id');
        var card_id = $(this).data('card_id');

        $.ajax({
            url: '/ajax/get_chat_messages',
            type: 'POST',
            dataType: 'json',
            data: {user_id:user_id, visitor_id:visitor_id, card_id:card_id},
            success: function(json){
                $('#chat_block').html(json.messages);
                UIkit.modal('#chat_modal').show();
                myInterval = setInterval( refresh_chat, 5000 );
                //console.log('check');

            }
        });
    });

    $(document).on('hide', '#chat_modal', function() {
        $('.open_chat').removeClass('active');
        //console.log("it works");
        window.clearInterval(myInterval);
    });

    $(document).on('show', '#chat_modal', function() {
        $('body').attr('data-is_file',0);
        //console.log('show '+$('body').data('is_file'));
    });

    $('#send_chat_message').on('click', function () {
        var user_id = $('.open_chat.active').data('user_id');
        var visitor_id = $('.open_chat.active').data('visitor_id');
        var card_id = $('.open_chat.active').data('card_id');
        var message = $('#new_chat_message').val();
        var is_file = 0;
        if ($('input[type="file"]').val() !== '') is_file = 1;

        //console.log(is_file);

        $.ajax({
            url: '/ajax/send_chat_message',
            type: 'POST',
            data: {user_id:user_id, visitor_id:visitor_id, card_id:card_id, message:message, is_file:is_file},
            success: function(text){
                $('#new_chat_message').val('');


                if ($('input[type="file"]').val() !== '') {
                    $('#chat_upload_form').find('input[name="filename"]').val(text);
                    $('#chat_upload_form').submit();
                    $('input[type="file"]').val(null);
                    $('#chat_upload_form').find('input[name="filename"]').val('');
                    $('body').attr('data-is_file', 0);
                    //console.log('after_ajax ' + is_file);
                } else {
                    refresh_chat();
                }

            }
        });
    });

    $('input[name="chat_foto"]').on('change', function () {
        //$('body').attr('data-is_file',1);
        console.log($('input[name="chat_foto"]').val());
    });

});

function refresh_chat(){
    var user_id = $('.open_chat.active').data('user_id');
    var visitor_id = $('.open_chat.active').data('visitor_id');
    var card_id = $('.open_chat.active').data('card_id');

    $.ajax({
        url: '/ajax/get_chat_messages',
        type: 'POST',
        dataType: 'json',
        data: {user_id:user_id, visitor_id:visitor_id, card_id:card_id},
        success: function(json){
            $('#chat_block').html(json.messages);
            //console.log('refresh');
        }
    });
}