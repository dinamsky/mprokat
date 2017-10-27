$( document ).ready(function() {
    $('#markGroupName').on('change',function(){
        var groupId = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getMarks',
            type: 'POST',
            data: {groupId:groupId},
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

        $.ajax({
            url: '/ajax/getCarType',
            type: 'POST',
            data: {gt:generalTypeTopLevelId},
            success: function(groupId){
                $('#markGroupName').find('option:selected').removeAttr('selected');
                $('#markGroupName').find('option[value="'+groupId+'"]').attr('selected','true');
                $.ajax({
                    url: '/ajax/getMarks',
                    type: 'POST',
                    data: {groupId:groupId},
                    success: function(html){
                        $('#markId').html(html);
                    }
                });
            }
        });


    });

    $('#generalTypeId').on('change',function(){
        var generalTypeId = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getAllSubFields',
            type: 'POST',
            data: {generalTypeId:generalTypeId},
            success: function(html){
                $('#subfields').html(html);
            }
        });
        $.ajax({
            url: '/ajax/getCarType',
            type: 'POST',
            data: {gt:generalTypeId},
            success: function(groupId){
                $('#markGroupName').find('option:selected').removeAttr('selected');
                $('#markGroupName').find('option[value="'+groupId+'"]').attr('selected','true');
                $.ajax({
                    url: '/ajax/getMarks',
                    type: 'POST',
                    data: {groupId:groupId},
                    success: function(html){
                        $('#markId').html(html);
                    }
                });
            }
        });
    });

    $('#countryCode').on('change',function(){
        var countryCode = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getRegion',
            type: 'POST',
            data: {countryCode:countryCode},
            success: function(html){
                $('#regionId').html(html);
            }
        });
    });

    $('#regionId').on('change',function(){
        var regionId = $(this).children('option:selected').val();
        $.ajax({
            url: '/ajax/getCity',
            type: 'POST',
            data: {regionId:regionId},
            success: function(html){
                $('form select#cityId').html(html);
            }
        });
    });

    $('input[name="noMark"]').on('change',function(){
        var checked = $(this).prop('checked');
        if(checked) $('input[name="ownMark"]').removeClass('uk-hidden');
        else $('input[name="ownMark"]').addClass('uk-hidden');
    });

    $('input[name="noModel"]').on('change',function(){
        var checked = $(this).prop('checked');
        if(checked) $('input[name="ownModel"]').removeClass('uk-hidden');
        else $('input[name="ownModel"]').addClass('uk-hidden');
    });

});