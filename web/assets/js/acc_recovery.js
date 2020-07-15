$( document ).ready(function() {

    $('#get_phone_code').on('click', function () {
        var phone = $('#phone').val();

        $.ajax({
            url: '/account_send_code',
            type: 'POST',
            data: {phone: phone},
            dataType: 'html',
            success: function (html) {
                $('#user_cards').html(html);
                $('#get_phone_code').before('<div class="uk-margin padding20 bordered">Код был успешно выслан на указанный Вами номер телефона!</div>');
                $('#get_phone_code').remove();
            }
        });
    });

});