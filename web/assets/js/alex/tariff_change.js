const tariffChange = (function($) {

    let ui = {
        $tariffSelector: $('.js-tariff-selector'),
        $checkedTariffSelector: $('.js-tariff-selector:checked'),
        $currentName: $('.js-current-tariff-name'),
        $currentPrice: $('.js-current-tariff-price'),
        selectedName: '.js-tariff-name',
        selectedPrice: '.js-tariff-price',
    };

    function init() {
        _bindHandlers();
        _initTariff();
    }

    function _bindHandlers() {
        ui.$tariffSelector.on('change', _updateTariff);
    }

    function _initTariff() {
        let $checkedLabel = ui.$checkedTariffSelector.parent(),
            $selectedName = $checkedLabel.find(ui.selectedName),
            $selectedPrice = $checkedLabel.find(ui.selectedPrice);

        ui.$currentName.text($selectedName.text());
        ui.$currentPrice.text($selectedPrice.text());
    }

    function _updateTariff() {
        let $this = $(this),
            $label = $this.parent(),
            $selectedName = $label.find(ui.selectedName),
            $selectedPrice = $label.find(ui.selectedPrice);

        ui.$currentName.text($selectedName.text());
        ui.$currentPrice.text($selectedPrice.text());
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(tariffChange.init);
