$( document ).ready(function() {


    var my_city_autoComplete = new autoComplete({
        selector: 'input[name="input_city"]',
        source: function(term, response){
            $.ajax({
                url: '/ajax/getCityByInput',
                type: 'POST',
                dataType: 'json',
                data: {q: term},
                success: function(json){
                    console.log(json);
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
            return '<div class="autocomplete-suggestion city_block" data-url="' + res[2] + '" data-header="' + item + '" data-id="' + id + '" data-val = "' + id + '" >' + item.replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function(e, term, item){
            $('.city_block[data-id="'+term+'"]').click();
            $('input[name="input_city"]').val('');
        }
    });

});

