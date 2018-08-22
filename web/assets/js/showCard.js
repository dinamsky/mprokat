$( document ).ready(function() {
    $('#cardTabs').on('shown', function (element) {
        if (element.target.id === 'card_tab') {
            var uluru = {lat: $('#map').data('lat') - 0, lng: $('#map').data('lng') - 0};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });
        }
    });

    $('.show_phone').on('click', function () {
        var card_id = $(this).data('card_id');
        var profile = $(this).data('profile');
        var type = 'profile';
        if (profile === 0) type = 'card';
        var t = $(this);
        $.ajax({
            url: '/ajax/showPhone',
            type: 'POST',
            data: {card_id: card_id, type: type},
            success: function (html) {
                $('.phone_block').html('<span class="opened_phone bg_green c_white uk-text-center"><i class="fa fa-phone"></i> ' + html + '</span>');
                $('.modal_phone').html(html).attr('href', 'tel:' + html);
                UIkit.modal('#user_phone_form').show();
                yaCounter43151009.reachGoal('PhoneClick', {phone: html, cardId: card_id});
            }
        });
    });

    $('.newbook .nbinner').on('click', function () {
        yaCounter43151009.reachGoal('BlueClick', {cardId: $(this).data('card_id')});
    });

    $('#bookMessageButton').on('click', function () {
        yaCounter43151009.reachGoal('BookMessage', {cardId: $(this).data('card_id')});
    });

    $('.go_pro').on('click', function () {
        var user_id = $(this).data('id');
        $.ajax({
            url: '/ajax/goPro',
            type: 'POST',
            data: {user_id: user_id},
            success: function (html) {
                document.location.href = '/user/' + user_id;
            }
        });
    });

    // $('.show_phone_big').on('click', function () {
    //     var card_id = $(this).data('card_id');
    //     var t = $(this);
    //     $.ajax({
    //         url: '/ajax/showPhone',
    //         type: 'POST',
    //         data: {card_id:card_id, type:'profile'},
    //         success: function(html){
    //             $('.phone_block').html('<span class="opened_phone c_grey uk-text-center"><i class="fa fa-phone"></i> '+html+'</span>');
    //             $('.modal_phone').html(html);
    //             UIkit.modal('#user_phone_form').show();
    //             yaCounter43151009.reachGoal('PhoneClick', {phone: html, cardId: card_id});
    //         }
    //     });
    // });

    $('.likes').on('click', function () {
        var card_id = $(this).data('card_id');
        var t = $(this);
        $.ajax({
            url: '/ajax/plusLike',
            type: 'POST',
            data: {card_id: card_id},
            success: function (html) {
                $(t).find('i').attr('class', 'fa fa-heart c_red');
                if (html === 'ok') {
                    var l = $('#card_likes').html() - 0;
                    $('#card_likes').html(l + 1);
                }
            }
        });
    });

    $('.star').on('mouseenter', function () {
        $('.star').removeClass('hover');
        $(this).addClass('hover');
        var i = $(this).data('star');
        for (var j = 1; j < i; j++) {
            $('.star[data-star="' + j + '"]').addClass('hover');
        }
    });

    $('.star').on('mouseleave', function () {
        $('.star').removeClass('hover');
    });

    $('.star').on('click', function () {
        var i = $(this).data('star');
        $('#stars').html(i);
        $('input[name="stars"]').val(i);
        $('.star').removeClass('active');
        $(this).addClass('active');
        for (var j = 1; j < i; j++) {
            $('.star[data-star="' + j + '"]').addClass('active');
        }
    });

    if ($('.card_cover').hasClass('share')) {
        UIkit.modal('#share_butons').show();
    }


    $('.show_full_content').on('click', function () {
        $('#card_content').css('max-height', 'none').css('overflow', 'auto');
    });


    //var dat = $(this).data('res').split("-");

    $('.datepicker-reserve').datepicker({
        minDate: new Date(document.getElementById('user_book_form').getAttribute('data-res'))
    });

});

function new_book_validate(){

    var message = [];

    var phone = $('#phone').val();
    if (!phone) message.push('<br>Заполните номер телефона!');

    if (message.length > 0){
        UIkit.notification('Заполните номер телефона!',{status:'danger',timeout:100000});
        return false;
    }
}