const cardNewValidate = (function($) {

    let isGridInit = false;

    let ui = {
        $switchForm: $('.js-step-new-form'),
        $prevStep: $('.js-prev-step'),

        // Steps List
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
        $markSelect: $('.js-transport-mark-select'),
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

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {

        // Prev Step
        ui.$prevStep.on('click', _prevStep);

        // Step Two - Next
        ui.$stepTwo.on('click', '.js-next-step', function(e){
            //if (!_validateStepTwo()) return false;
            _nextStep();
        });

        // Step Three - Next
        ui.$stepThree.on('click', '.js-next-step', function(e){
            //if (!_validateStepThree()) return false;
            _nextStep();
        });

        // Step Four - Next
        ui.$stepFour.on('click', '.js-next-step', function(e){
            //if (!_validateStepFour()) return false;
            _nextStep();
        });

        // Step Five - Next
        ui.$stepFive.on('click', '.js-next-step', function(e){
            //if (!_validateStepFive()) return false;
            _nextStep();
        });

    }


    // Валидация Третьего шага
    function _validateStepThree() {

        ui.$stepThreeError.hide();

        for (let error in stepThreeErrors) {
            stepThreeErrors[error] = false;
        }

        let type = ui.$typeSelect.val(),
            group = ui.$groupSelect.val(),
            mark = (ui.$markSelect.val()) ? ui.$markSelect.val() : ui.$markInput.val(),
            model = (ui.$modelSelect.val()) ? ui.$modelSelect.val() : ui.$modelInput.val();

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
