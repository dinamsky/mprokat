$( document ).ready(function() {
    $(".owl-carousel").each(function() {
        var items = $(this).data('items');
        var margin = 0;
        if(items>1) margin = 20;
        $(this).owlCarousel({
            'nav': true,
            'margin' : margin,
            'slideBy' : items,
            'navText': ['<i uk-icon="icon:chevron-left"></i>', '<i uk-icon="icon:chevron-right"></i>'],
            'autoHeight': true,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:items
                },
                1000:{
                    items:items
                },
                2000:{
                    items:items
                }
            }
        });
    });
});