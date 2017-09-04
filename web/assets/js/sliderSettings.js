$( document ).ready(function() {
    $(".owl-carousel").each(function() {
        var items = $(this).data('items');
        var margin = 0;
        if(items>1) margin = 20;
        $(this).owlCarousel({
            'items': items,
            'nav': true,
            'margin' : margin,
            'slideBy' : items,
            'navText': ['<i uk-icon="icon:chevron-left"></i>', '<i uk-icon="icon:chevron-right"></i>'],
            'autoHeight': true
        });
    });
});