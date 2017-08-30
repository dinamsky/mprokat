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

    $('.main_foto_button').on('click',function(){
        var t = $(this);
        var id = $(t).data('id');
        $.ajax({
            url: '/ajax/mainFoto',
            type: 'POST',
            data: {id:id},
            success: function(html){
                $(t).parents('.edit_foto').prependTo(".edit_fotos_block");
            }
        });
    });



    var uluru = {lat: $('#map').data('lat')-0, lng: $('#map').data('lng')-0 };
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: uluru
    });
    var marker = new google.maps.Marker({
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP,
        position: uluru
    });

    marker.addListener('dragend', function(element){
        console.log(marker.getPosition());
        var coords = marker.getPosition().toString();
        $('input[name="coords"]').val(coords.replace("(","").replace(")",""));
    });

});

