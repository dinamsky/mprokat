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
                $('#cityId').html(html);
            }
        });
    });

});