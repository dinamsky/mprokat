$( document ).ready(function() {
    $('#markGroupName').on('change',function(){
        var groupName = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getMarks',
            type: 'POST',
            data: {groupName:groupName},
            success: function(html){
                //alert(html);
                $('#markId').html(html);
            }
        });
    });

    $('#markId').on('change',function(){
        var markId = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getModels',
            type: 'POST',
            data: {markId:markId},
            success: function(html){
                $('#markModelId').html(html);
            }
        });
    });

    $('#generalTypeTopLevelId').on('change',function(){
        var generalTypeTopLevelId = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getGeneralTypeSecondLevel',
            type: 'POST',
            data: {generalTypeTopLevelId:generalTypeTopLevelId},
            success: function(html){
                $('#generalTypeId').html(html);
            }
        });
    });


    $.ajax({
        url: '/ajax/getAllSubFields',
        type: 'POST',
        data: {generalTypeId:$('form').data('general_type_id')},
        success: function(html){
            $('#subfields').html(html);
        }
    });




    $.ajax({
        url: '/ajax/getRegion',
        type: 'POST',
        data: {countryCode: $('form').data('country_code')},
        success: function(html){
            $('#regionId').html(html);
            $('#countryCode').find('option[value="'+$('form').data('country_code')+'"]').attr('selected','selected');
            $('#regionId').find('option[value="'+$('form').data('region_id')+'"]').attr('selected','selected');
        }
    });


    $.ajax({
        url: '/ajax/getCity',
        type: 'POST',
        data: {regionId: $('form').data('region_id')},
        success: function(html){
            $('#cityId').html(html);
            $('#cityId').find('option[value="'+$('form').data('city_id')+'"]').attr('selected','selected');

        }
    });

    $('#subfields').on('change', '.subFieldSelect', function(){
        var subId = $(this).children('option:selected').val();
        var fieldId = $(this).data('id');
        var element = $(this);
        $.ajax({
            url: '/ajax/getSubField',
            type: 'POST',
            data: {subId:subId, fieldId:fieldId},
            success: function(html){
                console.log(html);
                if (html) {
                    $(element).attr('name', 'old');
                    $(element).after(html);
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
});