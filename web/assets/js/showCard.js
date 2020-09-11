

// window.addEventListener("DOMContentLoaded", function () {
$( document ).ready(function() {

    $("form[name=ver1]").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        qreg_1($(form).find('#qreg_1'));
    });

    $("form[name=ver2]").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        qreg_2($(form).find('#qreg_2'));
    });

    $('#cardTabs').on('shown', function (element) {
        if (element.target.id === 'card_tab') {
            var uluru = {lat: $('#map').data('lat') - 0, lng: $('#map').data('lng') - 0};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });
        }
    });

    $('.show_phone').on('click', function () {
        var card_id = $(this).data('card_id');
        var profile = $(this).data('profile');
        var type = 'profile';
        if (profile === 0) type = 'card';
        var t = $(this);
        $.ajax({
            url: '/ajax/showPhone',
            type: 'POST',
            data: {card_id: card_id, type: type},
            success: function (html) {
                $('.phone_block').html('<span class="opened_phone bg_green c_white uk-text-center"><i class="fa fa-phone"></i> ' + html + '</span>');
                $('.modal_phone').html(html).attr('href', 'tel:' + html);
                UIkit.modal('#user_phone_form').show();
                yaCounter43151009.reachGoal('PhoneClick', {phone: html, cardId: card_id});
            }
        });
    });

    $('.newbook .nbinner').on('click', function () {
        yaCounter43151009.reachGoal('BlueClick', {cardId: $(this).data('card_id')});
    });

    $('#bookMessageButton').on('click', function () {
        yaCounter43151009.reachGoal('BookMessage', {cardId: $(this).data('card_id')});
    });

    $('.go_pro').on('click', function () {
        var user_id = $(this).data('id');
        $.ajax({
            url: '/ajax/goPro',
            type: 'POST',
            data: {user_id: user_id},
            success: function (html) {
                document.location.href = '/user/' + user_id;
            }
        });
    });


    $('.rs_selector').on('mouseenter', function () {
        var s = $(this).data('star')-0;
        var t = $(this);
        $(this).parent('div').find('.rs_selector').removeClass('filled');
        $(this).parent('div').find('.rs_selector .rating-icon').removeClass('rating-icon_active');
        for (var i = 1; i <= s; i++) {
            t.parent('div').find('.star_'+i).addClass('filled');
            t.parent('div').find('.star_'+i).find('.rating-icon').addClass('rating-icon_active');
        }

    });

    $('.rs_selector').on('mouseleave', function () {
        var t = $(this);
        var s = t.parent('div').find('.rs_selector.stopped').data('star')-0;
        $(this).parent('div').find('.rs_selector').removeClass('filled');
        $(this).parent('div').find('.rs_selector .rating-icon').removeClass('rating-icon_active');
        for (var i = 1; i <= s; i++) {
            t.parent('div').find('.star_' + i).addClass('filled');
            t.parent('div').find('.star_' + i).find('.rating-icon').addClass('rating-icon_active');
        }
    });

    $('.rs_selector').on('click', function () {
        var t = $(this);
        var s = $(this).data('star')-0;

        t.parents('form').find('input[name="stars"]').val(s);
        t.parents('form').find('.js-stars-rating-list').text(s);
        t.parent('div').find('.rs_selector').removeClass('stopped');
        t.addClass('stopped');
        t.find('.rating-icon').addClass('rating-icon_active');
    });

    $('.star').on('mouseenter', function () {
        $('.star').removeClass('hover');
        $(this).addClass('hover');
        var i = $(this).data('star');
        for (var j = 1; j < i; j++) {
            $('.star[data-star="' + j + '"]').addClass('hover');
        }
    });

    $('.star').on('mouseleave', function () {
        $('.star').removeClass('hover');
    });

    $('.star').on('click', function () {
        var i = $(this).data('star');
        $('#stars').html(i);
        $('input[name="stars"]').val(i);
        $('.star').removeClass('active');
        $(this).addClass('active');
        for (var j = 1; j < i; j++) {
            $('.star[data-star="' + j + '"]').addClass('active');
        }
    });

    $('#user_phone_form label').on('click', function () {
        $('#user_phone_form label').removeClass('active');
        $(this).addClass('active');
        $('#rate_form').trigger('submit');
    });

    if ($('.card_cover').hasClass('share')) {
        UIkit.modal('#share_butons').show();
    }


    $('.show_full_content').on('click', function () {
        $('#card_content').css('max-height', 'none').css('overflow', 'auto');
    });


    //var dat = $(this).data('res').split("-");
    /*
    $('.datepicker-reserve').datepicker({
        minDate: new Date(document.getElementById('user_book_form').getAttribute('data-res')),
        autoClose: true
    });
    */
    
    function qreg_1(but) {
        var email = $('#nrf input[name="mail"]').val().trim();
        //var password = $('#nrf input[name="password"]').val().trim();
        var phone = $('#nrf .mp-country-code').text()+' '+$('#nrf input[name="phone"]').val();
        //if ((phone = (phone = String(phone.match(/[0-9]+/g))).replace(/,/g, "")) < 99999) alert("Введите телефон корректно");
        
        var back_url = $('#nrf input[name="back_url"]').val();
        var name = $('#nrf input[name="name"]').val().trim();
        var t = $(but);

        $(but).addClass('uk-hidden');

        if(phone!=='') {
            $.ajax({
                url: '/qreg_ajax_1',
                type: 'POST',
                data: {phone: phone,back_url:back_url,name: name,email: email},
                success: function (html) {
                    if(html ==='ok') {
                        $('.rb_1').addClass('uk-hidden');
                        $('.rb_2').removeClass('uk-hidden');
                    } else if (html ==='user') {
                        $('#nrf input[name="phone_req"]').val(phone);
                        $('.rb_1').addClass('uk-hidden');
                        $('.rb_2').removeClass('uk-hidden');
                        // t.removeClass('uk-hidden');
                        // $('#auth_module').removeClass('uk-hidden');
                        // phone_req
                        // UIkit.notification('Такой пользователь уже есть в базе!',{status:'danger',timeout:100000});
                    } else {
                        t.removeClass('uk-hidden');
                        $(but).removeClass('uk-hidden');
                        UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            $(but).removeClass('uk-hidden');
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };
    
    function qreg_2(but) {
        var regcode = $('#nrf input[name="regcode"]').val();
        var phone_req = $('#nrf input[name="phone_req"]').val();
        var t = $(but);

        $(but).addClass('uk-hidden');

        $.ajax({
            url: '/qreg_ajax_2',
            type: 'POST',
            data: {regcode: regcode, phone_req: phone_req},
            success: function (html) {
                if(html==='ok') {
                    $('.rb_2').addClass('uk-hidden');
                    $('.rb_3').removeClass('uk-hidden');
                } else {
                    // $('.rb_1').removeClass('uk-hidden');
                    $('.rb_2').removeClass('uk-hidden');
                    // $('#qreg_1').removeClass('uk-hidden');
                    $(but).removeClass('uk-hidden');
                    UIkit.notification('Код не совпал!',{status:'danger',timeout:100000});
                }
            }
        });
    };
});

function new_book_validate(){

    var message = [];

    var phone = $('#nbf_form #phone').val();
    if (!phone) message.push('<br>Заполните номер телефона!');

    if (message.length > 0){
        UIkit.notification('Заполните номер телефона!',{status:'danger',timeout:100000});
        return false;
    }
}
