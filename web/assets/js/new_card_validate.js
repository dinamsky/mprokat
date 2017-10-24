$( document ).ready(function() {



});

function new_card_validate(){
    var general_type = $('#generalTypeId').find('option:selected').val();
    var header = $('#new_card_form').find('input[name="header"]').val();
    var mark = $('#markId').find('option:selected').val()-0;
    var model = $('#markModelId').find('option:selected').val()-0;
    var city = $('form select[name="cityId"]').find('option:selected').val();
    var noMark = $('input[name="noMark"]').prop('checked');
    var ownMark = $('input[name="ownMark"]').val();

    var noModel = $('input[name="noModel"]').prop('checked');
    var ownModel = $('input[name="ownModel"]').val();

    var priceHour = $('input[name="price[1]"]').val();
    var priceDay = $('input[name="price[2]"]').val();







    var subfields = [];

    var message = [];

    if($('#new_card_form').hasClass('unknown')){
        var r_email = $('input[name="r_email"]').val();
        var r_password = $('input[name="r_password"]').val();
        var r_phone = $('input[name="r_phone"]').val();

        var l_email = $('input[name="l_email"]').val();
        var l_password = $('input[name="l_password"]').val();

        if(l_email !== ''){
            if(l_password === '') message.push('\nЗаполните пароль!');
            if(r_email !== '') message.push('\nВы можете или войти или зарегистрироваться! Оставьте ненужные поля пустыми!');
        }

        if(l_password !== ''){
            if(l_email === '') message.push('\nЗаполните email!');
        }

        if(r_email !== ''){
            if(r_password === '') message.push('\nЗаполните пароль!');
            if(r_phone === '') message.push('\nЗаполните номер телефона!');
            if(l_email !== '') message.push('\nВы можете или войти или зарегистрироваться! Оставьте ненужные поля пустыми!');
        }

        if(r_password !== ''){
            if(r_email === '') message.push('\nЗаполните email!');
            if(r_phone === '') message.push('\nЗаполните номер телефона!');
        }

        if(r_phone !== ''){
            if(r_email === '') message.push('\nЗаполните email!');
            if(r_password === '') message.push('\nЗаполните пароль!');
        }

        if(r_email === '' && r_password === '' && r_phone === '' && l_email === '' && l_password === ''){
            message.push('\nЗаполните или поля регистрации или входа!');
        }
    }



    if($('#new_card_form').hasClass('no_phone')){
        var phone = $('#phone').val();
        if (!phone) message.push('\nЗаполните номер телефона!');
    }

    if(!priceHour && !priceDay) message.push('\nОдна из цен - обязательна!');

    if($('#foto_upload').val() === ''){
        message.push('\nФотографии');
    }

    if (!general_type) message.push('\nТип транспорта');
    if (!header) message.push('\nЗаголовок');

    if (!mark || mark === 0) {
        if(noMark){
            if (!ownMark) message.push('\nВпишите свою марку');
        } else {
            message.push('\nМарка');
        }
    }

    if (!model || model === 0) {
        if(noModel){
            if (!ownModel)  message.push('\nВпишите свою модель');
            if (!mark && !noMark) message.push('\nВыберите марку');
        } else {
            message.push('\nМодель');
        }
    }




    if (!city || city === '0') message.push('\nГород');

    // $('.sub_field_field').each(function(){
    //     var field_id = $(this).data('id');
    //     var label = $('.subfield_label[data-id="'+field_id+'"]').html();
    //     if ($(this).hasClass('is_last')) {
    //         var val;
    //         if ($(this).hasClass('subFieldSelect')) {
    //             val = $(this).find('option:selected').val();
    //             if (!val || val === '0') message.push('\n'+label);
    //         } else {
    //             val = $(this).val();
    //             if (!val) message.push('\n'+label);
    //         }
    //     } else {
    //         message.push('\n'+label);
    //     }
    //     subfields.push(field_id);
    // });
    //
    // if (subfields.length === 0) message.push('Дополнительные поля транспорта\n');

    if (message.length > 0){
        alert('Заполните поля:\n'+message);
        return false;
    }
}