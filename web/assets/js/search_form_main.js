$( document ).ready(function() {
    
    var my_city_autoComplete_ms_input = new autoComplete({
        selector: 'input[name="ms_input_city"]',
        minChars: 1,
        offsetTop: 16,
        source: function(term, response){
            $.ajax({
                url: '/ajax/getCityByInput',
                type: 'POST',
                dataType: 'json',
                data: {q: term},
                success: function(json){
                    response(json);
                }
            });
        },
        renderItem: function (item, search){
            var res = item.split('|');
            item = res[0];
            var id = res[1];
            search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
            return '<div class="autocomplete-suggestion "  data-header="' + item + '" data-id="' + id + '" data-val = "' + id + '" >' + item.replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function(e, term, item){
            $('input[name="ms_input_city_id"]').val(item.getAttribute('data-id'));
            $('input[name="ms_input_city"]').val(item.getAttribute('data-header'));
        }
    });

    $('#ms_search_form').submit(function (e) {
        e.preventDefault();
        var message = [];

        var cityId = $('input[name="ms_input_city_id"]').val();
        if (!cityId) message.push('<br>Выберите город из выпадающего списка!');
    
        if (message.length > 0){
            UIkit.notification('Выберите город из выпадающего списка!',{status:'danger',timeout:100000});
            return false;
        }
        $.ajax({
            url: '/ajax/getCityOne',
            type: 'POST',
            dataType: 'json',
            data: {city_id: cityId},
            success: function (json) {
                var city = json.city_url;
                console.log(city);
                var gt = $('select[name="ms_car_type_in"]').val();
                console.log(gt);
                document.location.href = '/rent/'+city+'/all/'+gt;
            }
        });

    });


});

