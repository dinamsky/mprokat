const commonAlex = (function($) {

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
    }

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        $('.js-form-group').on('focus', '.js-form-control', _formControlFocus);
        $('.js-form-group').on('blur', '.js-form-control', _formControlBlur);
        $('.js-select-lang').on('click', 'a', _changeTelMask)
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
