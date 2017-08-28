$( document ).ready(function() {



});

function new_card_validate(){
    var general_type = $('#generalTypeId').find('option:selected').val();
    var header = $('input[name="header"]').val();
    var model = $('#markModelId').find('option:selected').val();
    var city = $('#cityId').find('option:selected').val();
    var subfields = [];

    var message = [];
    if (!general_type) message.push('general');
    if (!header) message.push('header');
    if (!model) message.push('model');
    if (!city || city === '0') message.push('city');

    $('.sub_field_field').each(function(){
        var field_id = $(this).data('id');
        if ($(this).hasClass('is_last')) {
            var val;
            if ($(this).hasClass('subFieldSelect')) {
                val = $(this).find('option:selected').val();
                if (!val || val === '0') message.push('subfield_'+field_id);
            } else {
                val = $(this).val();
                if (!val) message.push('subfield_'+field_id);
            }
        } else {
            message.push('subfield_'+field_id);
        }
        subfields.push(field_id);
    });

    if (subfields.length === 0) message.push('subfields');

    if (message.length > 0){
        alert(message);
        return false;
    }
}