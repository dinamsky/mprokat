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
                        UIkit.modal($('#pass_recover_tel')).hide();
                        UIkit.modal($('#pass_recover_tel_code')).show();
                    } else {
                        $(form).find('#prtc1').removeClass('uk-hidden');
                        UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            $(form).find('#prtc1').removeClass('uk-hidden');
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };

    function prtf2(form) {
        var t = form;
        var regcode = $(form).find('input[name="regcode"]').val();

        $(form).find('#prtc2').addClass('uk-hidden');

        if(regcode!=='') {
            $.ajax({
                url: '/userRecoverTelCode',
                type: 'POST',
                data: {code: regcode},
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
            $(form).find('#prtc2').removeClass('uk-hidden');
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };


/// смена телефонного номера
    $("#modal-phone-edit form").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        phef1(form);
    });

    $("#modal-phone-edit_code form").submit(function(evt) {
        var form = event.target;
        event.preventDefault()
        phef2(form);
    });

    function phef1(form) {
        var t = form;
        var phone = $(form).find('.mp-country-code').text()+' '+$(form).find('input[name="phone"]').val();

        $(form).find('#btn-pes-1').addClass('uk-hidden');

        if(phone!=='') {
            $.ajax({
                url: '/e_ph_ajax_1',
                type: 'POST',
                data: {phone: phone},
                success: function (html) {
                    if(html ==='ok') {
                        UIkit.modal($('#modal-phone-edit')).hide();
                        UIkit.modal($('#modal-phone-edit_code')).show();
                    } else {
                        $(form).find('#btn-pes-1').removeClass('uk-hidden');
                        UIkit.notification('Не удалось отправить СМС, попробуйте указать другой номер.',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            $(form).find('#btn-pes-1').removeClass('uk-hidden');
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };

    function phef2(form) {
        var t = form;
        var regcode = $(form).find('input[name="regcode"]').val();

        $(form).find('#btn-pes-2').addClass('uk-hidden');

        if(regcode!=='') {
            $.ajax({
                url: '/e_ph_ajax_2',
                type: 'POST',
                data: {code: regcode},
                success: function (html) {
                    if(html ==='ok') {
                        document.location.href = window.location.href;
                    } else {
                        $(form).find('#btn-pes-2').removeClass('uk-hidden');
                        UIkit.notification('Код не совпал!',{status:'danger',timeout:100000});
                    }
                }
            });
        } else {
            $(form).find('#btn-pes-2').removeClass('uk-hidden');
            UIkit.notification('Все поля обязательны!',{status:'danger',timeout:100000});
        }
    };

});