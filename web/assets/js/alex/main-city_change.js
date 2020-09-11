const mainCityChange = (function($) {

    let ui = {
        $cityId: $('.js-main-city-id')
    };

    let options = {
        adjustWidth: false,
        url: function(q) {
            return "/ajax/getCityByInput";
        },

        getValue: function(element) {

            return element.split('|')[0];
        },

        list: {
            onClickEvent: function() {
                let value = $('.js-main-input-city').getSelectedItemData(),
                    valueArray = value.split('|'),
                    cityName = valueArray[0],
                    cityId = valueArray[1],
                    cityUrl = valueArray[2];

                $('.js-main-city-id').val(cityId)
            }
        },

        ajaxSettings: {
            dataType: "json",
            method: "POST",
            data: {
                dataType: "json"
            }
        },

        preparePostData: function(data) {
            data.q = $('.js-main-input-city').val();
            return data;
        },

        requestDelay: 400
    };

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        $('.js-main-input-city').easyAutocomplete(options);
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(mainCityChange.init);
