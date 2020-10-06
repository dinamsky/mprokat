const cardEditValidate = (function($) {

    let ui = {
        $mainEditForm: $('.js-card-edit-form'),

        $transportType: $('.js-transport-type-select'),
        $transportGroup: $('.js-transport-group-select'),
        $transportMark: $('.js-transport-mark-select'),
        $transportModel: $('.js-transport-model-select'),

        $addressCountry: $('.js-address-country-select'),
        $addressRegion: $('.js-address-region-select'),
        $addressCity: $('.js-address-city-select'),
        $addressField: $('.js-address-textarea'),

        $payPerHour: $('.js-pay-per-hour'),
        $payPerDay: $('.js-pay-per-day'),

        $rentTerms: $('.js-rent-terms'),

        // Error Alerts
        $errorAlert: $('.js-error-alert'),

        $transportTypeError: $('.js-transport-type-select-error'),
        $transportGroupError: $('.js-transport-group-select-error'),
        $transportMarkError: $('.js-transport-mark-select-error'),
        $transportModelError: $('.js-transport-model-select-error'),

        $addressCountryError: $('.js-address-country-select-error'),
        $addressRegionError: $('.js-address-region-select-error'),
        $addressCityError: $('.js-address-city-select-error'),
        $addressFieldError: $('.js-address-textarea-error'),

        $payError: $('.js-pay-error'),

        $rentTermsError: $('.js-rent-terms-error'),
    };

    let errorsLog = {
        transportType: false,
        transportGroup: false,
        transportMark: false,
        transportModel: false,

        country: false,
        region: false,
        city: false,
        address: false,

        photos: false,
        terms: false,

        pay: false
    }

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        ui.$mainEditForm.on('submit', _sendForm);
    }

    function _sendForm(e) {
        if (!_checkForm()) return false;
    }

    function _checkForm() {

        ui.$errorAlert.hide();

        for (let error in errorsLog) {
            errorsLog[error] = false;
        }

        let countryVal = ui.$addressCountry.val(),
            regionVal = ui.$addressRegion.val(),
            cityVal = ui.$addressCity.val(),
            addressVal = ui.$addressField.val(),

            type = ui.$transportType.val(),
            group = ui.$transportGroup.val(),
            mark = ui.$transportMark.val(),
            model = ui.$transportModel.val(),

            rentTermsVal = ui.$rentTerms.val(),

            perDayVal = ui.$payPerDay.val(),
            perHourVal = ui.$payPerHour.val();

        if(!countryVal) {
            errorsLog.country = true;
            ui.$addressCountryError.show();
        }

        if(!regionVal) {
            errorsLog.region = true;
            ui.$addressRegionError.show();
        }

        if(!cityVal) {
            errorsLog.city = true;
            ui.$addressCityError.show();
        }

        if(!addressVal) {
            errorsLog.address = true;
            ui.$addressFieldError.show();
        }

        if(!type) {
            errorsLog.transportType = true;
            ui.$transportTypeError.show();
        }

        if(!group) {
            errorsLog.transportGroup = true;
            ui.$transportGroupError.show();
        }

        if(!mark) {
            errorsLog.transportMark = true;
            ui.$transportMarkError.show();
        }

        if(!model) {
            errorsLog.transportModel = true;
            ui.$transportModelError.show();
        }

        if(!rentTermsVal) {
            errorsLog.terms = true;
            ui.$rentTermsError.show();
        }

        if(!perDayVal.length && !perHourVal.length) {
            errorsLog.pay = true;
            ui.$payError.show();
        }

        if (!errorsLog.country && !errorsLog.region  && !errorsLog.city  && !errorsLog.address
            && !errorsLog.transportType && !errorsLog.transportGroup && !errorsLog.transportMark && !errorsLog.transportModel
            && !errorsLog.terms && !errorsLog.pay) {
            return true;
        }

        return false;
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(cardEditValidate.init);
