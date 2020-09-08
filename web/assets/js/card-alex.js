const cardApp = (function($) {

    let ui = {
        $datepickerInputIn: $('.js-date-in'),
        $datepickerInputOut: $('.js-date-out'),
        $likeButton: $('.js-like-button')
    };

    let datepicInputInSetting = {
        minDate: new Date(),
        autoClose: true,
        position: 'bottom right'
    };

    let datepicInputOutSetting = {
        minDate: new Date(),
        autoClose: true,
        position: 'bottom right'
    };

    let currentDate = new Date();

    function init() {
        _bindHandlers();
    }

    // Навешивам события
    function _bindHandlers() {
        _imagesCarousel();
        ui.$likeButton.on('click', _plusLike);
        ui.$datepickerInputIn.datepicker(datepicInputInSetting);
        ui.$datepickerInputOut.datepicker(datepicInputOutSetting);
        ui.$datepickerInputIn.data('datepicker').selectDate(new Date())
    }

    function _plusLike(e) {
        e.preventDefault();

        let $this = $(this);

        $.ajax({
            url: '/ajax/plusLike',
            type: 'POST',
            data: {card_id: window.cardId},
            success: function (response) {
                $this.addClass('entry-like__button_active');
            }
        });
    }

    function _imagesCarousel() {

        $('.js-entry-thumbs-slider').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            draggable:true,
            infinite: true,
            swipeToSlide: true,
            vertical: true,
            verticalSwiping: true,
            centerMode: false,
            arrows: false,
            focusOnSelect: true,
            asNavFor: '.js-entry-preview-slider'
        });

        $('.js-entry-preview-slider').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            swipeToSlide: true,
            mobileFirst: true,
            arrows: false,
            prevArrow: '<button type="button" class="uk-position-left uk-position-z-index uk-text-left uk-icon uk-preserve slider-preview__button"><svg width="23" height="52" viewBox="0 0 23 52" xmlns="http://www.w3.org/2000/svg"><path xmlns="http://www.w3.org/2000/svg" d="M22 1L2 26L22 51" stroke="#FAFAFA" stroke-width="2" stroke-linecap="round"></path></svg></button>',
            nextArrow: '<button type="button" class="uk-position-right uk-position-z-index uk-text-right uk-icon uk-preserve slider-preview__button"><svg width="23" height="52" viewBox="0 0 23 52" xmlns="http://www.w3.org/2000/svg"><path xmlns="http://www.w3.org/2000/svg" d="M1 1L21 26L1 51" stroke="#FAFAFA" stroke-width="2" stroke-linecap="round"></path></svg></button>',
            asNavFor: '.js-entry-thumbs-slider',
            responsive: [
                {
                    breakpoint: 639,
                    settings: {
                        arrows: true,
                    }
                }
            ]
        });

    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(cardApp.init);
