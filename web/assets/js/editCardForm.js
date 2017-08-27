$( document ).ready(function() {

    $('.feature_item').on('change',function(){
        if($(this).prop('checked')) $(this).val('1').attr('checked','checked');
        else $(this).val('0').removeAttr('checked');
    });

});