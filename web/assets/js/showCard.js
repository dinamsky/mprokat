

// window.addEventListener("DOMContentLoaded", function () {
$( document ).ready(function() {
    // var inputsTel = document.querySelectorAll('input[type="tel"]');
    // Inputmask({
    //     "mask": "(999) 999-99-99",
    //     showMaskOnHover: false
    // }).mask(inputsTel);
    
    // var inputsMail = document.querySelectorAll('input[type="mail"]');
    // Inputmask({
    //     "mask": "(999) 999-99-99",
    //     showMaskOnHover: false
    // }).mask(inputsMail);

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

    // $('.country-select').on('change', function(){
    //     var code = $(this).find(':selected').data('code');
    //     $('.mp-country-code').text(code);
    //     var plshold = $(this).find(':selected').data('plshold');
    //     $('.mp-country-phone').attr('placeholder', plshold);
    //     var mask = $(this).find(':selected').data('filter');
    //     Inputmask({
    //         "mask": mask,
    //         showMaskOnHover: false
    //     }).mask(inputsTel);
    // });

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

    // $('.show_phone_big').on('click', function () {
    //     var card_id = $(this).data('card_id');
    //     var t = $(this);
    //     $.ajax({
    //         url: '/ajax/showPhone',
    //         type: 'POST',
    //         data: {card_id:card_id, type:'profile'},
    //         success: function(html){
    //             $('.phone_block').html('<span class="opened_phone c_grey uk-text-center"><i class="fa fa-phone"></i> '+html+'</span>');
    //             $('.modal_phone').html(html);
    //             UIkit.modal('#user_phone_form').show();
    //             yaCounter43151009.reachGoal('PhoneClick', {phone: html, cardId: card_id});
    //         }
    //     });
    // });

    $('.likes').on('click', function () {
        var card_id = $(this).data('card_id');
        var t = $(this);
        $.ajax({
            url: '/ajax/plusLike',
            type: 'POST',
            data: {card_id: card_id},
            success: function (html) {
                $(t).find('i').attr('class', 'fa fa-heart c_red');
                if (html === 'ok') {
                    var l = $('#card_likes').html() - 0;
                    $('#card_likes').html(l + 1);
                }
            }
        });
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

    $('.datepicker-reserve').datepicker({
        minDate: new Date(document.getElementById('user_book_form').getAttribute('data-res')),

        autoClose: true
    });

    function qreg_1(but) {
        var email = $('#nrf input[name="mail"]').val().trim();
        //var password = $('#nrf input[name="password"]').val().trim();
        var phone = $('#nrf .mp-country-code').text()+' '+$('#nrf input[name="phone"]').val();
        console.log(phone);
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
                        t.removeClass('uk-hidden');
                        $('#auth_module').removeClass('uk-hidden');
                        UIkit.notification('Такой пользователь уже есть в базе!',{status:'danger',timeout:100000});
                    } else {
                        t.removeClass('uk-hidden');
                        $(but).removeClass('uk-hidden');
                        UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };
    
    // $('#qreg_1').on('click', function () {
    //     var email = $('#nrf input[name="email"]').val().trim();
    //     //var password = $('#nrf input[name="password"]').val().trim();
    //     var phone = $('#nrf input[name="phone"]').val().trim();
    //     //if ((phone = (phone = String(phone.match(/[0-9]+/g))).replace(/,/g, "")) < 99999) alert("Введите телефон корректно");
        
    //     var back_url = $('#nrf input[name="back_url"]').val();
    //     var name = $('#nrf input[name="name"]').val().trim();
    //     var t = $(this);

    //     $(this).addClass('uk-hidden');

    //     if(phone!=='') {
    //         $.ajax({
    //             url: '/qreg_ajax_1',
    //             type: 'POST',
    //             data: {phone: phone,back_url:back_url,name: name,email: email},
    //             success: function (html) {
    //                 if(html ==='ok') {
    //                     $('.rb_1').remove();
    //                     $('.rb_2').removeClass('uk-hidden');
    //                 } else if (html ==='user') {
    //                     t.removeClass('uk-hidden');
    //                     UIkit.notification('Такой пользователь уже есть в базе!',{status:'danger',timeout:100000});
    //                 } else {
    //                     t.removeClass('uk-hidden');
    //                     UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
    //                 }
    //             }
    //         });
    //     } else {
    //         UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
    //     }
    // });

    function qreg_2(but) {
        var regcode = $('#nrf input[name="regcode"]').val();
        var t = $(but);

        $(but).addClass('uk-hidden');

        $.ajax({
            url: '/qreg_ajax_2',
            type: 'POST',
            data: {regcode: regcode},
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

    // $('#qreg_2').on('click', function () {
    //     var regcode = $('#nrf input[name="regcode"]').val();
    //     var t = $(this);

    //     $(this).remove();

    //     $.ajax({
    //         url: '/qreg_ajax_2',
    //         type: 'POST',
    //         data: {regcode: regcode},
    //         success: function (html) {
    //             if(html==='ok') {
    //                 $('.rb_2').remove();
    //                 $('.rb_3').removeClass('uk-hidden');
    //             } else {
    //                 UIkit.notification('Код не совпал!',{status:'danger',timeout:100000});
    //             }
    //         }
    //     });
    // });




});

// });
function new_book_validate(){

    var message = [];

    var phone = $('#nbf_form #phone').val();
    if (!phone) message.push('<br>Заполните номер телефона!');

    if (message.length > 0){
        UIkit.notification('Заполните номер телефона!',{status:'danger',timeout:100000});
        return false;
    }
}