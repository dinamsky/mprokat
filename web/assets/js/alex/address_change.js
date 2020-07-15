const addressChange = (function($) {

    let ui = {
        $countrySelect: $('.js-address-country-select'),
        $regionSelect: $('.js-address-region-select'),
        $citySelect: $('.js-address-city-select')
    };

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        ui.$countrySelect.on('change', _changeCountry);
        ui.$regionSelect.on('change', _changeRegion);
    }
    
    function _changeCountry() {

        let $this = $(this),
            currentVal = $this.val(),
            data = {
                countryCode: currentVal
            };

        $.ajax({
            url: '/ajax/getRegion',
            type: 'POST',
            data: data,
            success: function(response){
                ui.$regionSelect
                    .html(response)
                    .prop('selectedIndex', 0)
                    .selectric('refresh');
                ui.$citySelect
                    .prop('selectedIndex', 0)
                    .prop('disabled', true)
                    .selectric('refresh');
            }
        });
    }

    function _changeRegion() {

        let $this = $(this),
            currentVal = $this.val(),
            data = {
                regionId: currentVal
            };

        $.ajax({
            url: '/ajax/getCity',
            type: 'POST',
            data: data,
            success: function(response){
                ui.$citySelect
                    .html(response)
                    .prop('selectedIndex', 0)
                    .prop('disabled', false)
                    .selectric('refresh');
            }
        });

    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(addressChange.init);
