$( document ).ready(function() {
    var inputsTel = document.querySelectorAll('input[type="tel"]');
    Inputmask({
        "mask": "(999) 999-99-99",
        showMaskOnHover: false
    }).mask(inputsTel);
    
    $('.country-select').on('change', function(){
        var code = $(this).find(':selected').data('code');
        $('.mp-country-code').text(code);
        var plshold = $(this).find(':selected').data('plshold');
        $('.mp-country-phone').attr('placeholder', plshold);
        var mask = $(this).find(':selected').data('filter');
        Inputmask({
            "mask": mask,
            showMaskOnHover: false
        }).mask(inputsTel);
    });

    $("#pass_recover_tel form").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        prtf1(form);
    });

    $("#pass_recover_tel_code form").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        prtf2(form);
    });
    
    function prtf1(form) {
        var t = form;
        var password1 = $(form).find('input[name="password1"]').val();
        var password2 = $(form).find('input[name="password2"]').val();
        var phone = $(form).find('.mp-country-code').text()+' '+$(form).find('input[name="phone"]').val();

        $(form).find('#prtc1').addClass('uk-hidden');

        if(phone!=='') {
            $.ajax({
                url: '/userRecoverTel',
                type: 'POST',
                data: {phone: phone,pass1:password1,pass2:password2},
                success: function (html) {
                    if(html ==='ok') {
                        $('#pass_recover_tel').addClass('uk-hidden');
                        $('#pass_recover_tel_code').removeClass('uk-hidden');
                    } else {
                        $(form).find('#prtc1').removeClass('uk-hidden');
                        UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };

    function prtf2(form) {
        var t = form;
        var regcode = $(form).find('input[name="regcode"]').val();

        $(form).find('#prtc2').addClass('uk-hidden');

        if(phone!=='') {
            $.ajax({
                url: '/userRecoverTelCode',
                type: 'POST',
                data: {regcode: regcode},
                success: function (html) {
                    if(html ==='ok') {
                        $(form).addClass('uk-hidden');
                        UIkit.notification('Пароль успешно изменен.',{status:'danger',timeout:100000});
                    } else {
                        $(form).find('#prtc2').removeClass('uk-hidden');
                        UIkit.notification('Код не совпал!',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };
});