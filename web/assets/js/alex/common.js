const commonAlex = (function($) {

    let ui = {
        $mainAlert: $('.js-main-alert'),
        $citySearchInput: $('.js-city-search-input'),
        $citySearchList: $('.js-city-search-list')
    };

    let buffVal;

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
        $('.js-clear-city-search-input').on('click', _clearCitySearchInput);

        $('.js-form-group').on('focus', '.js-form-control', _formControlFocus);
        $('.js-form-group').on('blur', '.js-form-control', _formControlBlur);
        $('.js-select-lang').on('click', 'a', _changeTelMask);

        $('.js-search-modal-city-input').on('input', _searchCityModal);
        $('.js-search-model-category').on('click', _setSearchCat);
        $('.js-search-modal-mobile-category-select').on('change', _setSearchMobileCat)
        $('body').on('click', '.js-search-modal-city', _setSearchModalCity);
        $('.js-search-modal-button').on('click', _modalSearchButton)
    }

    function _modalSearchButton(e) {
        e.preventDefault();
        let go_href = getSelectorUrl() + getQueryVars();
        document.location.href = go_href;
    }

    function _setSearchModalCity() {
        $('.js-modal-search-city-search-list').empty().hide();
        $('.js-modal-search-city-search-list-preloader').hide();
        $('.js-modal-search-city-search-list-default').hide();

        let $this = $(this),
            currentHeader = $this.data('header'),
            currentHeaderCityOnly = currentHeader.split(',')[0],
            currentCityId = $this.data('id'),
            data = {cityId: currentCityId};

        $('.js-search-modal-city-input').val(currentHeaderCityOnly);
        $('.js-modal-search-selected-city').val(currentCityId);

        $('#cityId').val(currentCityId);
        $('#cityURL').val($(this).data('url'));
        $('.city_selector').html($(this).data('header'));

        $.ajax({
            url: '/ajax/setCity',
            type: 'POST',
            data: data,
            success: function(response){
                console.log(response);
                let go_href = getSelectorUrl() + getQueryVars();
                if($('body').hasClass('main_page')) go_href = '/';
                if($('body').hasClass('promo')) go_href = '/promo';
                //document.location.href = go_href;
                console.warn(go_href);
            }
        });
    }

    function getSelectorUrl(){
        let city = '/rus',
            generalType = '/alltypes',
            markModel = '';

        city = '/'+$('#cityURL').val();

        let generalParent = $('.js-modal-search-selected-category').val(),
            general = $('#gtURL').val(),
            body = $('.body_header').data('body'),
            mark = $('#markURL').val(),
            model = $('#modelURL').val(),
            service = '/all';

        if (generalParent) generalType = '/'+generalParent;
        if (general) generalType = '/'+general;
        if (mark && mark !== 'Любая марка') markModel = '/'+mark;
        if (mark && model && mark !== 'Любая марка' && model !== 'Любая модель') markModel = '/'+mark+'/'+model;

        if (service === '/all' && generalType === '/alltypes' && markModel === ''){
            service = '';
            generalType = '';
        }

        if (generalType === '/alltypes'){
            generalType = '';
        }

        let bodyType = '';
        if (body) bodyType = '/'+body;

        return '/rent'+city+service+generalType+markModel+bodyType;
    }

    function getQueryVars() {
        let view = $('#main_search_button').data('view'); view ? view = 'view='+view : view = '',
            page = $('#main_search_button').data('page'); page ? page = '&page='+page : page = '',
            onpage = $('#main_search_button').data('onpage'); onpage ? onpage = '&onpage='+onpage : onpage = '',
            order = $('#order').val(); order ? order = '&order='+ order : order = '';
        if (view+page+onpage+order) {
            return '?'+view+page+onpage+order;
        }
        else {
            return '';
        }
    }

    function _setSearchMobileCat(e) {
        e.preventDefault();
        let $this = $(this),
            selectedVal = $this.val();

        $('.js-modal-search-selected-category').val(selectedVal);
    }

    function _setSearchCat(e) {
        e.preventDefault();
        let $this = $(this),
            selectedCatUrl = $this.attr('data-url');

        $('.js-modal-search-selected-category').val(selectedCatUrl);

        $('.js-search-model-category').removeClass('active');
        $this.addClass('active');
    }

    function _clearCitySearchInput(e) {
        e.preventDefault();
        $('.js-city-search-input').val('');
        $('.js-city-search-list-default').show();
        $('.js-city-search-list-preloader').hide();
        $('.js-city-search-list').hide();
    }

    function _searchCityModal(e) {

        let $this = $(this),
            currentVal = $this.val(),
            clearVal = currentVal.replace(/\s+/g, ' ').trim(),
            lengthVal = clearVal.length;


        if(lengthVal > 0) {
            if(clearVal === buffVal) return false;

            buffVal = clearVal;
            //console.log(clearVal + ' Запрос отправлен');
            $.ajax({
                url: '/ajax/getCityByInput',
                type: "POST",
                dataType: "json",
                data: { q: clearVal },
                beforeSend: function() {
                    $('.js-modal-search-city-search-list-preloader').show();
                    $('.js-modal-search-city-search-list').hide();
                    $('.js-modal-search-city-search-list-default').hide();
                },
                success: function(response) {
                    let cityList = response;

                    if(cityList.length) {
                        $('.js-modal-search-city-search-list').empty().show();
                        $('.js-modal-search-city-search-list-preloader').hide();
                        $('.js-modal-search-city-search-list-default').hide();

                        let cityListMapped = cityList.map((city) => '<li class="js-search-modal-city" data-header="' + city.split('|')[0] + '" data-url="' + city.split('|')[2] + '" data-id="' + city.split('|')[1] + '" data-val="' + city.split('|')[1] + '">' + city.split('|')[0].split(',')[0] + '</li>');
                        let cityListSorted = cityListMapped.sort();

                        cityListSorted.forEach(function(entry) {
                            $('.js-modal-search-city-search-list').append(entry);
                        });
                    } else {
                        $('.js-modal-search-city-search-list-default').show();
                        $('.js-modal-search-city-search-list-preloader').hide();
                        $('.js-modal-search-city-search-list').hide();
                    }

                }
            });
        } else {
            $('.js-modal-search-city-search-list-default').show();
            $('.js-modal-search-city-search-list-preloader').hide();
            $('.js-modal-search-city-search-list').hide();
        }
    }

    function _searchCity(e) {


        let $this = $(this),
            currentVal = $this.val(),
            clearVal = currentVal.replace(/\s+/g, ' ').trim(),
            lengthVal = clearVal.length;




        if(lengthVal > 0) {
            if(clearVal === buffVal) return false;

            buffVal = clearVal;
            //console.log(clearVal + ' Запрос отправлен');
            $.ajax({
                url: '/ajax/getCityByInput',
                type: "POST",
                dataType: "json",
                data: { q: clearVal },
                beforeSend: function() {
                    $('.js-city-search-list-preloader').show();
                    $('.js-city-search-list').hide();
                    $('.js-city-search-list-default').hide();
                },
                success: function(response) {
                    let cityList = response;

                    if(cityList.length) {
                        $('.js-city-search-list').empty().show();
                        $('.js-city-search-list-preloader').hide();
                        $('.js-city-search-list-default').hide();

                        let cityListMapped = cityList.map((city) => '<li class="city_block" data-header="' + city.split('|')[0] + '" data-url="' + city.split('|')[2] + '" data-id="' + city.split('|')[1] + '" data-val="' + city.split('|')[1] + '">' + city.split('|')[0].split(',')[0] + '</li>');
                        let cityListSorted = cityListMapped.sort();

                        cityListSorted.forEach(function(entry) {
                            $('.js-city-search-list').append(entry);
                        });
                    } else {
                        $('.js-city-search-list-default').show();
                        $('.js-city-search-list-preloader').hide();
                        $('.js-city-search-list').hide();
                    }

                }
            });
        } else {
            $('.js-city-search-list-default').show();
            $('.js-city-search-list-preloader').hide();
            $('.js-city-search-list').hide();
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
