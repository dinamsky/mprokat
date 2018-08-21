$( document ).ready(function() {

    $('input[name="oferta_check"]').on('change', function () {
        var ok = $(this).prop('checked');
        if(ok) {
            $(this).parents('form').find('button').removeAttr('disabled').addClass('uk-button-primary');
        } else {
            $(this).parents('form').find('button').attr('disabled',true).removeClass('uk-button-primary');
        }
    });

});

