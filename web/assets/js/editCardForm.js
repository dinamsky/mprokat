$( document ).ready(function() {

    jQuery.fn.swap = function(b) {
        b = jQuery(b)[0];
        var a = this[0],
            a2 = a.cloneNode(true),
            b2 = b.cloneNode(true),
            stack = this;

        a.parentNode.replaceChild(b2, a);
        b.parentNode.replaceChild(a2, b);

        stack[0] = a2;
        return this.pushStack( stack );
    };

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
                    $(element).attr('name', 'old').attr('class','old_select uk-select');
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

    $('.feature_item').on('change',function(){
        if($(this).prop('checked')) $(this).val('1').attr('checked','checked');
        else $(this).val('0').removeAttr('checked');
    });

    $('body').on('click', '.delete_foto_button', function(){
        var t = $(this);
        var id = $(t).data('id');

        $.ajax({
            url: '/ajax/deleteFoto',
            type: 'POST',
            data: {id:id},
            success: function(html){
                if(html==='stop'){
                    alert('Нельзя удалить единственное фото!');
                } else {
                    t.parents('.template-photo-grid__item').remove();
                }
            }
        });

    });

    $('body').on('click', '.rotate_foto_button', function () {
        let $this = $(this),
            fotoId = $this.attr('data-id'),
            fotoAngle = $this.data('rotate') + 0,
            parentBlock = $this.parents('.template-photo-grid__item'),
            fotoBlock = parentBlock.find('.template-photo-grid__photo'),
            data = {id: fotoId, rotate: 'r90'};

        fotoBlock.fadeOut(100);
        console.log(fotoAngle);

        $.ajax({
            url: '/ajax/rotateFoto',
            type: 'POST',
            data: data,
            success: function(response){
                console.log('Photo rotated');
                let newRotate = fotoAngle + 1;
                let angleToRotate = 90 * newRotate;
                $this.data('rotate', newRotate);
                fotoBlock.css({'transform': 'rotate(-' + angleToRotate + 'deg)'});

                setTimeout(() => fotoBlock.fadeIn(600), 1000);
            }
        });
    });

    $('body').on('click', '.main_foto_button', function(){
        var t = $(this);
        var id = $(t).data('id');
        let tblock = t.parents('.js-photo-grid-item'),
            firstBlock = $('.template-photo-grid .js-photo-grid-item')[0];

        $.ajax({
            url: '/ajax/mainFoto',
            type: 'POST',
            data: {id:id},
            success: function(html){
                //$(t).parents('.edit_foto').prependTo(".edit_fotos_block");
                $('.main_foto_button.uk-button-primary').removeClass('uk-button-primary').addClass('uk-button-default');
                t.addClass('uk-button-primary').removeClass('uk-button-default');

                tblock.swap(firstBlock);
            }
        });
    });


    $('#show_map').on('click', function() {
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
        });
    });


    $('#tariff_changer').on('click',function(){
        $('.hide_on_change').toggleClass('uk-hidden');
        if($('.hide_on_change').hasClass('uk-hidden')) {
            $(this).html('Отменить смену тарифа');
            $.ajax({
                url: '/user_controller_ajax_tariff_cancel',
                type: 'POST'
            });
        }
        else $(this).html('Сменить тариф');
    });

    $('.service_selector button').on('click', function(){
        $('input[name="serviceTypeId"]').val($(this).val());
        $('.service_selector button').removeClass('uk-button-primary');
        $(this).addClass('uk-button-primary');
    });

});

