$( document ).ready(function() {

    $('.feature_item').on('change',function(){
        if($(this).prop('checked')) $(this).val('1').attr('checked','checked');
        else $(this).val('0').removeAttr('checked');
    });

    $('.delete_foto_button').on('click',function(){
        var t = $(this);
        var id = $(t).data('id');
        $.ajax({
            url: '/ajax/deleteFoto',
            type: 'POST',
            data: {id:id},
            success: function(html){
                $(t).parents('.edit_foto').remove();
            }
        });
    });

});