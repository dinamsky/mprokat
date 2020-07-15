let alesha;
const transportChange = (function($) {

    let ui = {
        $typeSelect: $('.js-transport-type-select'),
        $groupSelect: $('.js-transport-group-select'),
        $modelSelect: $('.js-transport-model-select'),
        $markSelect: $('.js-transport-mark-select'),
        $markCheckbox: $('.js-mark-checkbox'),
        $markCollapse: $('.js-mark-collapse'),
        $modelCheckbox: $('.js-model-checkbox'),
        $modelCollapse: $('.js-model-collapse')
    };

    let allSubFields, carType;

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        _initModel();
        ui.$typeSelect.on('change', _changeType);
        ui.$groupSelect.on('change', _changeGroup);
        ui.$markSelect.on('change', _changeMark);
        ui.$modelSelect.on('change', _changeModel);
        ui.$markCheckbox.on('change', _toggleNewMark);
        ui.$modelCheckbox.on('change', _toggleNewModel);
    }

    function _initModel() {
        console.log('model init!');
        let markVal = ui.$markSelect.val();
        if(!markVal) ui.$modelSelect.prop('disabled', true).selectric('refresh');
    }

    function _toggleNewMark() {

        ui.$markCollapse.toggleClass('uk-hidden');

        let isCheckboxActive = ui.$markCheckbox.prop('checked');

        if(isCheckboxActive) {
            ui.$markSelect.prop('disabled', true).selectric('refresh');
            ui.$modelSelect.prop('disabled', true).selectric('refresh');
            ui.$modelCheckbox.prop('checked', true).prop('disabled', true);
            ui.$modelCollapse.removeClass('uk-hidden');
        } else {
            ui.$markSelect.removeAttr('disabled').selectric('refresh');
            ui.$modelSelect.removeAttr('disabled').selectric('refresh');
            ui.$modelCheckbox.prop('checked', false).removeAttr('disabled');
            ui.$modelCollapse.addClass('uk-hidden');
        }

        _initModel();

    }

    function _toggleNewModel() {

        ui.$modelCollapse.toggleClass('uk-hidden');

        let isCheckboxActive = ui.$modelCheckbox.prop('checked');
        if(isCheckboxActive) {
            ui.$modelSelect.prop('disabled', true).selectric('refresh');
        } else {
            ui.$modelSelect.removeAttr('disabled').selectric('refresh');
        }

        _initModel();
    }

    function _changeType() {
        carType = null;

        let $this = $(this),
            currentVal = $this.val() - 0;


        $.when(
            _getData('getGeneralTypeSecondLevel', {generalTypeTopLevelId: currentVal}),
            _getData('getCarType', {gt: currentVal}),
            _getData('getAllSubFields', {generalTypeId: currentVal})

        ).done(function(responseGroups, responseCarType, allSubFields) {

            console.log(allSubFields);
            $('.categories').html(allSubFields[0]);
            $('.categories').find('select').selectric();

            ui.$groupSelect
                .html(responseGroups[0])
                .prop('selectedIndex', 0)
                .selectric('refresh');

            ui.$modelSelect.prop('selectedIndex', 0).prop('disabled', true).selectric('refresh');
            ui.$markSelect.prop('selectedIndex', 0).prop('disabled', true).selectric('refresh');

            carType = responseCarType[0];

            if(!responseCarType[0].length) {
                ui.$groupSelect.find('option[value="0"]').remove();
                ui.$groupSelect.selectric('refresh');
            }

        });

    }

    function _changeGroup() {

        let $this = $(this),
            currentVal = $this.val() - 0;

        if(carType.length) {
            _getData('getMarks', {groupId: carType})
                .done(function(response) {
                    ui.$markSelect
                        .html(response)
                        .prop('selectedIndex', 0)
                        .prop('disabled', false)
                        .selectric('refresh');
            });

            return;
        }

        _getData('getCarType', {gt: currentVal}).done(function (groupId) {
            _getData('getMarks', {groupId: groupId}).done(function(response) {
                console.log(response);
                ui.$markSelect
                    .html(response)
                    .prop('selectedIndex', 0)
                    .prop('disabled', false)
                    .selectric('refresh');
            });
        });

    }


    function _changeMark() {

        let $this = $(this),
            currentVal = $this.val() - 0;

        _getData('getModels', {markId: currentVal}).done(function(response) {
            console.log(response);
            ui.$modelSelect
                .html(response)
                .prop('selectedIndex', 0)
                .prop('disabled', false)
                .selectric('refresh');
        });
    }


    function _changeModel() {

    }

    function _getData(method, data) {

        return $.ajax({
            url: '/ajax/' + method,
            type: 'POST',
            data: data,
        });

    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(transportChange.init);
