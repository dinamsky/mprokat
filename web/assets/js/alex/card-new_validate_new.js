const cardNewValidate = (function($) {

    let isGridInit = false;

    let ui = {
        $switchForm: $('.js-step-new-form'),
        $prevStep: $('.js-prev-step'),


        // Steps List
        $stepOne: $('.js-step-one'),
        $stepTwo: $('.js-step-two'),
        $stepThree: $('.js-step-three'),
        $stepFour: $('.js-step-four'),
        $stepFive: $('.js-step-five'),
        $stepSix: $('.js-step-six'),


        // Step Two Required Fields
        $countrySelect: $('.js-address-country-select'),
        $regionSelect: $('.js-address-region-select'),
        $citySelect: $('.js-address-city-select'),
        $addressTextarea: $('.js-address-textarea'),

        // Step Two Error Alerts
        $stepTwoError: $('.js-step-two-error'),
        $countrySelectError: $('.js-address-country-select-error'),
        $regionSelectError: $('.js-address-region-select-error'),
        $citySelectError: $('.js-address-city-select-error'),
        $addressTextareaError: $('.js-address-textarea-error'),


        // Step Three Required Fields
        $typeSelect: $('.js-transport-type-select'),
        $groupSelect: $('.js-transport-group-select'),
        $modelSelect: $('.js-transport-model-select'),
        $modelInputId: $('.js-transport-model-input-id'),

        $markSelect: $('.js-transport-mark-select'),
        $markInputId: $('.js-transport-mark-input-id'),

        $modelInput: $('.js-transport-model-input'),
        $markInput: $('.js-transport-mark-input'),
        $markCheckbox: $('.js-mark-checkbox'),
        $modelCheckbox: $('.js-model-checkbox'),

        // Step Three Error Alerts
        $stepThreeError: $('.js-step-three-error'),
        $typeSelectError: $('.js-transport-type-select-error'),
        $groupSelectError: $('.js-transport-group-select-error'),
        $modelSelectError: $('.js-transport-model-select-error'),
        $markSelectError: $('.js-transport-mark-select-error'),
        $modelInputError: $('.js-transport-model-input-error'),
        $markInputError: $('.js-transport-mark-input-error'),


        // Step Four Error Alerts
        $stepFourError: $('.js-step-four-error'),
        $previewPhotosError: $('.js-preview-photos-error'),
        $rentTermsError: $('.js-rent-terms-error'),


        // Step Five Required Fields
        $payPerHour: $('.js-pay-per-hour'),
        $payPerDay: $('.js-pay-per-day'),

        // Step Five Error Alerts
        $stepFiveError: $('.js-step-five-error'),

    };

    let stepTwoErrors = {
        country: false,
        region: false,
        city: false,
        address: false
    }

    let stepThreeErrors = {
        type: false,
        group: false,
        mark: false,
        model: false
    }

    let stepFourErrors = {
        photos: false,
        terms: false
    }

    let stepFiveErrors = {
        payHour: false,
        payDay: false,
    }

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {

        // Prev Step
        ui.$prevStep.on('click', _prevStep);

        // Step One - Next
        ui.$stepOne.on('click', '.js-next-step', function (e) {
            _nextStep();
            _scrollToElem(ui.$stepTwo);
        })

        // Step Two - Next
        ui.$stepTwo.on('click', '.js-next-step', function(e){
            if (!_validateStepTwo()) return false;
            _nextStep();
            _scrollToElem(ui.$stepThree);
        });

        // Step Three - Next
        ui.$stepThree.on('click', '.js-next-step', function(e){
            if (!_validateStepThree()) return false;
            _nextStep();
            _scrollToElem(ui.$stepFour);
        });

        // Step Four - Next
        ui.$stepFour.on('click', '.js-next-step', function(e){
            if (!_validateStepFour()) return false;
            _nextStep();
            _scrollToElem(ui.$stepFive);
        });

        // Step Five - Next
        ui.$stepFive.on('click', '.js-next-step', function(e){
            if (!_validateStepFive()) return false;
            _nextStep();
            _scrollToElem(ui.$stepSix);
        });

    }

    function _scrollToElem(elem) {
        $('html, body').stop().animate({
            scrollTop: elem.offset().top - 160
        }, 800);
    }

    // Валидация Пятого шага
    function _validateStepFive() {

        ui.$stepFiveError.hide();

        for (let error in stepFiveErrors) {
            stepFiveErrors[error] = false;
        }

        let perDayVal = ui.$payPerDay.val(),
            perHourVal = ui.$payPerHour.val();

        if (!perHourVal.length) stepFiveErrors.payHour = true;
        if (!perDayVal.length) stepFiveErrors.payDay = true;

        if(!perHourVal.length && !perDayVal.length) ui.$stepFiveError.show();

        if (!stepFiveErrors.payHour || !stepFiveErrors.payDay) {
            return true;
        }

        return false;
    }

    // Валидация Четвертого шага
    function _validateStepFour() {

        ui.$stepFourError.hide();

        for (let error in stepFourErrors) {
            stepFourErrors[error] = false;
        }

        let $previewPhotos = $('.js-photo-preview'),
            $rentTerms = $('.js-rent-terms'),
            rentTermsVal = $rentTerms.val();

        if (!$previewPhotos.length) {
            //stepFourErrors.photos = true;
            //ui.$previewPhotosError.show();
        }

        if (!rentTermsVal.length) {
            stepFourErrors.terms = true;
            ui.$rentTermsError.show();
        }

        if (!stepFourErrors.photos && !stepFourErrors.terms) {
            return true;
        }

        return false;

    }


    // Валидация Третьего шага
    function _validateStepThree() {

        ui.$stepThreeError.hide();

        for (let error in stepThreeErrors) {
            stepThreeErrors[error] = false;
        }

        let type = ui.$typeSelect.val(),
            group = ui.$groupSelect.val(),
            mark = (ui.$markInputId.val()) ? ui.$markInputId.val() : ui.$markInput.val(),
            model = (ui.$modelInputId.val()) ? ui.$modelInputId.val() : ui.$modelInput.val();

        if (!type) {
            stepThreeErrors.type = true;
            ui.$typeSelectError.show();
        }

        if (!group) {
            stepThreeErrors.group = true;
            ui.$groupSelectError.show();
        }

        if (!mark) {
            stepThreeErrors.mark = true;
            let isCheckboxActive = ui.$markCheckbox.prop('checked');

            if(isCheckboxActive) {
                ui.$markInputError.show();
            } else {
                ui.$markSelectError.show();
            }

        }

        if (!model) {
            stepThreeErrors.model = true;
            let isCheckboxActive = ui.$modelCheckbox.prop('checked');

            if(isCheckboxActive) {
                ui.$modelInputError.show();
            } else {
                ui.$modelSelectError.show();
            }
        }

        if (!stepThreeErrors.type && !stepThreeErrors.group && !stepThreeErrors.mark && !stepThreeErrors.model) {
            return true;
        }

        return false;
    }

    // Валидация Второго шага
    function _validateStepTwo() {

        ui.$stepTwoError.hide();

        for (let error in stepTwoErrors) {
            stepTwoErrors[error] = false;
        }

        let countryVal = ui.$countrySelect.val(),
            regionVal = ui.$regionSelect.val(),
            cityVal = ui.$citySelect.val(),
            addressVal = ui.$addressTextarea.val();

        if (!countryVal) {
            ui.$countrySelectError.show();
            stepTwoErrors.country = true;
        }

        if (!regionVal) {
            ui.$regionSelectError.show();
            stepTwoErrors.region = true;
        }

        if (!cityVal) {
            ui.$citySelectError.show();
            stepTwoErrors.city = true;
        }

        if (!addressVal) {
            ui.$addressTextareaError.show();
            stepTwoErrors.address = true;
        }

        if (!stepTwoErrors.country && !stepTwoErrors.region && !stepTwoErrors.city && !stepTwoErrors.address) {
            return true;
        }

        return false;
    }

    function _prevStep() {
        UIkit.switcher(ui.$switchForm).show('previous');
    }

    function _nextStep() {
        UIkit.grid('.js-step-new-form [uk-grid]', {});
        UIkit.switcher(ui.$switchForm).show('next');
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(cardNewValidate.init);
