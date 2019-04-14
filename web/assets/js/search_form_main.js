$( document ).ready(function() {
    
    var my_city_autoComplete_ms_input = new autoComplete({
        selector: 'input[name="ms_input_city"]',
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
            $('input[name="ms_input_city_id"]').val(item.getAttribute('data-header'));
            $('input[name="ms_input_city"]').val(item.getAttribute('data-header'));
        }
    });

    

});

