$( document ).ready(function() {

    $('#go_top_search').on('click', function () {
        var city = $('input[name="top_s_city"]').val();
        var gt = $('input[name="top_s_gt"]').val();
        var mark = $('input[name="top_s_mark"]').val();

        if(!mark) mark = '';

        document.location.href = '/rent/'+city+'/all/'+gt+'/'+mark;
    });


});

