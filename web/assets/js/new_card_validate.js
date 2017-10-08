$( document ).ready(function() {



});

function new_card_validate(){
    var general_type = $('#generalTypeId').find('option:selected').val();
    var header = $('#new_card_form').find('input[name="header"]').val();
    var model = $('#markModelId').find('option:selected').val()-0;
    var city = $('form select[name="cityId"]').find('option:selected').val();
    var noMark = $('input[name="noMark"]').prop('checked');
    var ownMark = $('input[name="ownMark"]').val();


    var priceHour = $('input[name="price[1]"]').val();
    var priceDay = $('input[name="price[2]"]').val();



    var subfields = [];

    var message = [];

    if(!priceHour && !priceDay) message.push('\nОдна из цен - обязательна!');

    if($('#foto_upload').val() === ''){
        message.push('\nФотографии');
    }

    if (!general_type) message.push('\nТип транспорта');
    if (!header) message.push('\nЗаголовок');
    if (!model || model === 0) {
        if(noMark){
            if (!ownMark)  message.push('\nВпишите свою марку/модель');
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