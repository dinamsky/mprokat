$( document ).ready(function() {
    $(".owl-carousel").each(function() {
        var items = $(this).data('items');
        var dots = $(this).data('dots');
        if(dots === 0) dots = false; else dots = true;
        var margin = 0;
        if(items>1) margin = 20;
        $(this).owlCarousel({
            'nav': true,
            'margin' : margin,
            'slideBy' : items,
            'navText': ['<i uk-icon="icon:chevron-left"></i>', '<i uk-icon="icon:chevron-right"></i>'],
            // 'autoHeight': true,
            'dots': dots,
            onInitialized: recount,
            responsive:{
                0:{
                    items:1,
                    stagePadding:50,
                    margin:20
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

    function recount()
    {
        $(".owl-carousel").find('div.owl-item').height($(".owl-carousel").height()+1);
    }

});
