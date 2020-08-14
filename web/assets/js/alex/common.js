const commonAlex = (function($) {

    let ui = {
        $mainAlert: $('.js-main-alert')
    }

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

    let _bestOwnersSwiperSettings = {
        direction: "horizontal",
        loop: true,
        centeredSlides: true,
        spaceBetween: 30,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },
        breakpoints: {
            320: {
                centeredSlides: true,
                slidesPerView: "auto",
                spaceBetween: 10
            },
            768: {
                centeredSlides: true,
                slidesPerView: 2,
                spaceBetween: 30
            },
            900: {
                centeredSlides: true,
                slidesPerView: 4,
                spaceBetween: 30
            }
        }
    };

    function init() {
        if(ui.$mainAlert.length) _checkMainAlerts();
        _bindHandlers();
    }

    function _bindHandlers() {
        let bestOwnersSwiper = new Swiper('.js-best-owners-swiper-container', _bestOwnersSwiperSettings);
        $('.js-form-group').on('focus', '.js-form-control', _formControlFocus);
        $('.js-form-group').on('blur', '.js-form-control', _formControlBlur);
        $('.js-select-lang').on('click', 'a', _changeTelMask);
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
