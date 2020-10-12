const transportEditChange = (function($) {

    let ui = {
        $typeSelect: $('.js-transport-type-select'),
        $groupSelect: $('.js-transport-group-select'),
        $modelSelect: $('.js-transport-model-select'),


        $markSelect: $('.js-transport-mark-select'),

        $markInputValue: $('.js-transport-mark-input-value'),
        $markInputId: $('.js-transport-mark-input-id'),
        $markDropdown: $('.js-mark-dropdown'),
        $markDropdownList: $('.js-mark-dropdown-list'),

        $modelInputValue: $('.js-transport-model-input-value'),
        $modelInputId: $('.js-transport-model-input-id'),
        $modelDropdown: $('.js-model-dropdown'),
        $modelDropdownList: $('.js-model-dropdown-list'),

        $markCheckbox: $('.js-mark-checkbox'),
        $markCollapse: $('.js-mark-collapse'),
        $modelCheckbox: $('.js-model-checkbox'),
        $modelCollapse: $('.js-model-collapse'),
        $subfieldsArea: $('.js-subfields-area'),
        $subfieldsSelect: $('.js-subfields-area select')
    };

    let isSubfieldsEmpty = window.isSubfieldsEmpty;

    let subFields, carType, markTestArr, modelTestArr;

    let prototypeReduce = Array.prototype.reduce;

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        _initModel();
        _putMarks();
        _putModels();

        if(isSubfieldsEmpty) _initSubfields();
        ui.$typeSelect.on('change', _changeType);
        ui.$groupSelect.on('change', _changeGroup);
        //ui.$markSelect.on('change', _changeMark);
        //ui.$modelSelect.on('change', _changeModel);
        ui.$markCheckbox.on('change', _toggleNewMark);
        ui.$modelCheckbox.on('change', _toggleNewModel);

        $('body').on('click', '.js-mark-link-select', _selectMarkDropdown);
        $('body').on('click', '.js-model-link-select', _selectModelDropdown);
        ui.$markInputValue.on('input', _markFill);
        ui.$markInputId.on('change', _changeMark);
        //ui.$markSelect.on('change', _changeMark);

        ui.$modelInputValue.on('input', _modelFill);
        ui.$modelInputId.on('change', _changeModel);
        //ui.$modelSelect.on('change', _changeModel);
    }

    function _initModel() {

        carType = ui.$typeSelect.val();
        if(ui.$groupSelect.val()) carType = ui.$groupSelect.val();

        let markVal = ui.$markSelect.val(),
            markText = ui.$markSelect.find('option:selected').text(),
            modelVal = ui.$modelSelect.val(),
            modelText = ui.$modelSelect.find('option:selected').text();

        ui.$markInputValue.val(markText);
        ui.$markInputId.val(markVal);
        ui.$modelInputValue.prop('disabled', false).val(modelText);
        ui.$modelInputId.val(modelVal);

        if(!markVal) {
            ui.$modelSelect.prop('disabled', true).selectric('refresh');
            ui.$modelInputValue.prop('disabled', true).val('');
            ui.$modelInputId.val('');

        }


    }

    function _initSubfields() {
        _getData('getAllSubFields', {generalTypeId:2}).done(function(response) {
            ui.$subfieldsArea.html(response);
            ui.$subfieldsArea.find('select').selectric({
                maxHeight: 200
            });
        });


    }

    function _selectMarkDropdown(e) {
        e.preventDefault();
        let $this = $(this),
            currId = $this.attr('href'),
            currValue = $this.text();

        ui.$markInputId.val(currId).change();
        ui.$markInputValue.val(currValue);
        UIkit.dropdown(ui.$markDropdown).hide();
    }

    function _selectModelDropdown(e) {
        e.preventDefault();
        let $this = $(this),
            currId = $this.attr('href') - 0,
            currValue = $this.text();

        ui.$modelInputId.val(currId).change();
        ui.$modelInputValue.val(currValue);

        UIkit.dropdown(ui.$modelDropdown).hide();
    }

    function _markFill(e) {
        e.preventDefault();
        let $this = $(this),
            inputVal = $this.val();

        let markTestArrTwo = markTestArr.filter(function(i) {
            let currVal = i.value,
                currValLowerCase = currVal.toLowerCase(),
                inputValLowerCase = inputVal.toLowerCase();

            return currValLowerCase.includes(inputValLowerCase);
            //mi.value.toLowerCase().includes(inputVal.toLowerCase())
        });

        ui.$markDropdownList.html('');
        $.each(markTestArrTwo, function(index, value){
            ui.$markDropdownList.append('<li><a href="' + value.id + '" class="js-mark-link-select">' + value.value + '</a></li>')
        });
    }

    function _modelFill(e) {
        e.preventDefault();
        let $this = $(this),
            inputVal = $this.val();

        let modelTestArrTwo = modelTestArr.filter(function(i) {
            let currVal = i.value,
                currValLowerCase = currVal.toLowerCase(),
                inputValLowerCase = inputVal.toLowerCase();

            return currValLowerCase.includes(inputValLowerCase);
            //mi.value.toLowerCase().includes(inputVal.toLowerCase())
        });

        ui.$modelDropdownList.html('');
        $.each(modelTestArrTwo, function(index, value){
            ui.$modelDropdownList.append('<li><a href="' + value.id + '" class="js-model-link-select">' + value.value + '</a></li>')
        });
    }

    function _putMarks() {
        let markOptions = ui.$markSelect.find('option');

        markTestArr = prototypeReduce.call(markOptions, function(markAcc, currentMark) {

            markAcc.push({id: currentMark.value, value: currentMark.textContent.trim()});

            return markAcc
        }, []);


        markTestArr = markTestArr.filter(i => i.id.length);
        //return markArray;

        //ui.$markDropdown.find('ul').html('');
        //markArray.each(function(index, value) {
        ui.$markDropdownList.html('');
        $.each(markTestArr, function(index, value){
            ui.$markDropdownList.append('<li><a href="' + value.id + '" class="js-mark-link-select">' + value.value + '</a></li>')
        });
    }

    function _putModels() {
        let modelOptions = ui.$modelSelect.find('option');

        modelTestArr = prototypeReduce.call(modelOptions, function(modelAcc, currentModel) {

            modelAcc.push({id: currentModel.value, value: currentModel.textContent.trim()});

            return modelAcc
        }, []);

        modelTestArr = modelTestArr.filter(i => i.id.length);
        //return markArray;

        //ui.$markDropdown.find('ul').html('');
        //markArray.each(function(index, value) {
        ui.$modelDropdownList.html('');
        $.each(modelTestArr, function(index, value){
            ui.$modelDropdownList.append('<li><a href="' + value.id + '" class="js-model-link-select">' + value.value + '</a></li>')
        });
    }

    function _toggleNewMark() {

        ui.$markCollapse.toggleClass('uk-hidden');

        //let isCheckboxActive = ui.$markCheckbox.prop('checked');

        /*
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
         */

        _initModel();

    }

    function _toggleNewModel() {

        ui.$modelCollapse.toggleClass('uk-hidden');

        let isCheckboxActive = ui.$modelCheckbox.prop('checked');

        /*
        if(isCheckboxActive) {
            ui.$modelSelect.prop('disabled', true).selectric('refresh');
        } else {
            ui.$modelSelect.removeAttr('disabled').selectric('refresh');
        }
         */

        _initModel();
    }

    function _changeType() {
        //carType = null;

        let $this = $(this),
            currentVal = $this.val() - 0;

        $.when(
            _getData('getGeneralTypeSecondLevel', {generalTypeTopLevelId: currentVal}),
            _getData('getCarType', {gt: currentVal}),
            _getData('getAllSubFields', {generalTypeId: currentVal})

        ).done(function(responseGroups, responseCarType, allSubFields) {

            //$('.categories').html(allSubFields[0]);
            //$('.categories').find('select').selectric();

            ui.$groupSelect
                .html(responseGroups[0])
                .prop('selectedIndex', 0)
                .selectric('refresh');

            ui.$modelSelect.prop('selectedIndex', 0).prop('disabled', true).selectric('refresh');
            ui.$modelInputValue.prop('disabled', true).val('');
            ui.$modelInputId.val('');

            ui.$markSelect.prop('selectedIndex', 0).prop('disabled', true).selectric('refresh');
            ui.$markInputValue.prop('disabled', true).val('');
            ui.$markInputId.val('');

            carType = responseCarType[0];

            subFields = allSubFields[0];

            ui.$subfieldsArea.html(subFields).find('select').selectric({
                maxHeight: 200
            });

            /*
            if(!responseCarType[0].length) {
                ui.$groupSelect.find('option[value="0"]').remove();
                ui.$groupSelect.selectric('refresh');
            }
             */

        });



    }

    function _changeGroup() {

        let $this = $(this),
            currentVal = $this.val() - 0;

        carType = currentVal;

        if(carType.length) {
            _getData('getMarks', {groupId: carType})
                .done(function(response) {
                    ui.$markSelect
                        .html(response)
                        .prop('selectedIndex', 0)
                        .prop('disabled', false)
                        .selectric('refresh');

                    _putMarks();

                    ui.$markInputValue.prop('disabled', false).val('');
                    ui.$markInputId.val('');

            });

            return;
        }

        _getData('getCarType', {gt: currentVal}).done(function (groupId) {
            _getData('getMarks', {groupId: groupId}).done(function(response) {
                ui.$markSelect
                    .html(response)
                    .prop('selectedIndex', 0)
                    .prop('disabled', false)
                    .selectric('refresh');

                _putMarks();

                ui.$markInputValue.prop('disabled', false).val('');
                ui.$markInputId.val('');

            });
        });

        _getData('getAllSubFields', {generalTypeId: currentVal}).done(function(response) {
            subFields = response;
            ui.$subfieldsArea.html(subFields).find('select').selectric({
                maxHeight: 200
            });
        });

        ui.$modelSelect.prop('selectedIndex', 0).prop('disabled', true).selectric('refresh');
        ui.$modelInputValue.prop('disabled', true).val('');
        ui.$modelInputId.val('');

    }


    function _changeMark() {

        let $this = $(this),
            currentVal = $this.val() - 0;

        _getData('getModels', {markId: currentVal}).done(function(response) {
            ui.$modelSelect
                .html(response)
                .prop('selectedIndex', 0)
                .prop('disabled', false)
                .selectric('refresh');

            _putModels();

            ui.$modelInputValue.prop('disabled', false).val('');
            ui.$modelInputId.val('');
        });

    }

    function _changeModel() {
        let $this = $(this),
            currentVal = $this.val() - 0;
        _getData('getPrices', {modelId: currentVal}).done(function(response) {
            console.log(response);
        });
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

jQuery(document).ready(transportEditChange.init);
