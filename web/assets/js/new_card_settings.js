$( document ).ready(function() {

    $.ajax({
        url: '/ajax/getAllSubFields',
        type: 'POST',
        data: {generalTypeId:2},
        success: function(html){
            $('#subfields').html(html);
        }
    });

    $('#subfields').on('change', '.subFieldSelect', function(){
        var subId = $(this).children('option:selected').val();
        var fieldId = $(this).data('id');
        var element = $(this);
        $.ajax({
            url: '/ajax/getSubField',
            type: 'POST',
            data: {subId:subId, fieldId:fieldId},
            success: function(html){
                console.log(html);
                if (html) {
                    $(element).attr('name', 'old').attr('class','old_select');
                    $(element).next('select').remove();
                    $(element).after(html);
                }
                else {
                    //
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });

    $('#show_map').on('click', function(){
        var uluru = {lat: $('#map').data('lat') - 0, lng: $('#map').data('lng') - 0};
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

        marker.addListener('dragend', function (element) {
            console.log(marker.getPosition());
            var coords = marker.getPosition().toString();
            $('input[name="coords"]').val(coords.replace("(", "").replace(")", ""));
            var latlon = coords.replace("(", "").replace(")", "").split(',');
            $('#map').data('lat',latlon[0]);
            $('#map').data('lng',latlon[1]);
        });
    });
});