const commonAlex = (function($) {

    let ui = {
        $mainAlert: $('.js-main-alert'),
        $citySearchInput: $('.js-city-search-input'),
        $citySearchList: $('.js-city-search-list')
    };

    let countriesList = {
        ru: {
            placeholder: '+7 (000) 000-00-00',
            val: '+7 ('
        },
        abh: {
            placeholder: '+7 (840) 000-00-00',
            val: '+7 (840) '
        },
        kaz: {
            placeholder: '+7 (000) 000-00-00',
            val: '+7 ('
        },
        ua: {
            placeholder: '+380 (00) 000-00-00',
            val: '+380 ('
        },
        usa: {
            placeholder: '+1 (000) 000-00-00',
            val: '+1 ('
        }
    };

    function init() {
        if(ui.$mainAlert.length) _checkMainAlerts();
        _bindHandlers();
    }

    function _bindHandlers() {
        $('.js-city-search-input').on('input', _searchCity);

        $('.js-form-group').on('focus', '.js-form-control', _formControlFocus);
        $('.js-form-group').on('blur', '.js-form-control', _formControlBlur);
        $('.js-select-lang').on('click', 'a', _changeTelMask);
    }
    
    function _searchCity(e) {
        let buffVal = '';

        let $this = $(this),
            currentVal = $this.val(),
            clearVal = currentVal.replace(/\s+/g, ' ').trim(),
            lengthVal = clearVal.length;

        buffVal = clearVal;

        if(lengthVal > 0) {
            console.log(clearVal + ' Запрос отправлен');
            $.ajax({
                url: '/ajax/getCityByInput',
                type: "POST",
                dataType: "json",
                data: { q: clearVal },
                success: function(response) {
                    $('.js-city-search-list').empty();

                    let cityList = response;
                    let cityListMapped = cityList.map((city) => '<li class="city_block" data-header="' + city.split('|')[0] + '" data-url="' + city.split('|')[2] + '" data-id="' + city.split('|')[1] + '" data-val="' + city.split('|')[1] + '">' + city.split('|')[0] + '</li>')

                    cityListMapped.forEach(function(entry) {
                        $('.js-city-search-list').append(entry);
                    });
                    
                    console.log(cityList.length);
                }
            });
        }
    }

    function _checkMainAlerts() {
        UIkit.modal(ui.$mainAlert).show();
        setTimeout(() => UIkit.modal(ui.$mainAlert).hide(), 3000);
    }

    function _changeTelMask(e) {
        e.preventDefault();

        UIkit.dropdown('.js-countries-dropdown').hide();

        let $this = $(this),
            $parentList = $(e.delegateTarget);
            $thisFlag = $this.find('.js-flag');
            thisFlagBackground = $thisFlag.css('background-image');
            currentCountry = $this.data('country')
            $currentFlag = $('.js-flag-chosen'),
            $telInput = $('.js-tel-mask-country');

        $currentFlag.css('background-image', thisFlagBackground);

        $telInput.mask(countriesList[currentCountry]['placeholder'], {
            placeholder: "+_(___)___ __ __"
        });

        $telInput.val(countriesList[currentCountry]['val']);

        $parentList.find('li').removeClass('uk-active');
        $this.parent().addClass('uk-active');

        $telInput.focus();
    }

    function _formControlFocus() {
        $(this).parent().addClass('theme-form-group-active');
    }

    function _formControlBlur() {
        if (!$(this).val().length) {
            $(this).parent().removeClass('theme-form-group-active');
        }
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(commonAlex.init);
