$( document ).ready(function() {
    $('#cardTabs').on('shown', function (element) {
        if(element.target.id === 'card_tab'){
            var uluru = {lat: $('#map').data('lat')-0, lng: $('#map').data('lng')-0 };
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

    $(".owl-carousel").owlCarousel({
        'items': 1,
        'nav': true,
        'navText': ['<i uk-icon="icon:chevron-left"></i>','<i uk-icon="icon:chevron-right"></i>'],
        'autoHeight': true
    });
});