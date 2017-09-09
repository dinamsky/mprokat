$( document ).ready(function() {

    $('#countryCode').on('change',function(){
        var countryCode = $(this).find('option:selected').val();
        $.ajax({
            url: '/ajax/getRegion',
            type: 'POST',
            data: {countryCode:countryCode},
            success: function(html){
                $('#regionId').html(html);
                $('#cityId').html('');
            }
        });
    });

    $('#regionId').on('change',function(){
        $('.city_selector').html($(this).find('option:selected').html());
        var regionId = $(this).find('option:selected').val();
        $.ajax({
            url: '/ajax/getCity',
            type: 'POST',
            data: {regionId:regionId},
            success: function(html){
                $('#cityId').html(html);
            }
        });
    });

    $('#generalTypeTopLevelId').on('change',function(){
        var generalTypeTopLevelId = $(this).find('option:selected').val();
        $('.general_selector').html($(this).find('option:selected').html());
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
        $('.general_selector').html($(this).find('option:selected').html());
        $.ajax({
            url: '/ajax/getAllSubFields',
            type: 'POST',
            data: {generalTypeId:generalTypeId},
            success: function(html){
                $('#subfields').html(html);
            }
        });
    });

    $('#markGroupName').on('change',function(){
        var groupName = $(this).find('option:selected').val();
        $('.mark_selector').html($(this).find('option:selected').html());
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
        var markId = $(this).find('option:selected').val();
        $('.mark_selector').html($(this).find('option:selected').html());
        $.ajax({
            url: '/ajax/getModels',
            type: 'POST',
            data: {markId:markId},
            success: function(html){
                $('#markModelId').html(html);
            }
        });
    });

    $('#cityId').on('change', function () {
        $('.city_selector').html($(this).find('option:selected').html());
    });

    $('#markModelId').on('change', function () {
        $('.mark_selector').html($('#markId').find('option:selected').html()+' '+$(this).find('option:selected').html());
    });


    $('#main_search_button').on('click', function () {
        document.location.href = getSelectorUrl() + getQueryVars();;
    });

    // $('.hide_on_click_out').click(function (e){ // событие клика по веб-документу
    //     // e.preventDefault();
    //     e.stopPropagation();
    // });
    //
    $(document).click( function(event){
        if( $(event.target).closest('.hide_on_click_out').length || $(event.target).closest('.selector_opener').length)
            return;
        $('.hide_on_click_out').removeClass('is_open');
        event.stopPropagation();
    });


});

function getSelectorUrl(){
    var city = '';
    var generalType = '/alltypes';
    var service = '/all';
    var markModel = '';

    var country = $('#countryCode').find('option:selected').data('url');
    var regionId = $('#regionId').find('option:selected').data('url');
    var cityId = $('#cityId').find('option:selected').data('url');

    var generalParent = $('#generalTypeTopLevelId').find('option:selected').data('url');
    var general = $('#generalTypeId').find('option:selected').data('url');

    var mark = $('#markId').find('option:selected').data('url');
    var model = $('#markModelId').find('option:selected').data('url');


    if(country) city = '/'+country;
    if(regionId) city = '/'+regionId;
    if(cityId) city = '/'+cityId;

    if(generalParent) generalType = '/'+generalParent;
    if(general) generalType = '/'+general;

    if(mark) markModel = '/'+mark;
    if(model) markModel = '/'+mark+'/'+model;

    return '/show'+city+service+generalType+markModel;
}

function getQueryVars() {
    var view = $('#main_search_button').data('view'); view ? view = 'view='+view : '';
    var page = $('#main_search_button').data('page'); page ? page = '&page='+page : '';
    var onpage = $('#main_search_button').data('onpage'); onpage ? onpage = '&onpage='+ onpage : '';
    return '?'+view+page+onpage;
}

