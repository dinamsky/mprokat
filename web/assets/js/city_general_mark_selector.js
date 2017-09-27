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

    // $('#generalTypeTopLevelId').on('change',function(){
    //     var generalTypeTopLevelId = $(this).find('option:selected').val();
    //     $('.general_selector').html($(this).find('option:selected').html());
    //     $.ajax({
    //         url: '/ajax/getGeneralTypeSecondLevel',
    //         type: 'POST',
    //         data: {generalTypeTopLevelId:generalTypeTopLevelId},
    //         success: function(html){
    //             $('#generalTypeId').html(html);
    //         }
    //     });
    // });
    //
    // $('#generalTypeId').on('change',function(){
    //     var generalTypeId = $(this).children('option:selected').val();
    //     $('.general_selector').html($(this).find('option:selected').html());
    //     // $.ajax({
    //     //     url: '/ajax/getAllSubFields',
    //     //     type: 'POST',
    //     //     data: {generalTypeId:generalTypeId},
    //     //     success: function(html){
    //     //         $('#subfields').html(html);
    //     //     }
    //     // });
    // });

    $('#markGroupName').on('change',function(){
        var groupId = $(this).find('option:selected').val();
        $('.mark_selector').html($(this).find('option:selected').html());
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
        document.location.href = getSelectorUrl() + getQueryVars();
    });


    // $(document).click( function(event){
    //     if( $(event.target).closest('.hide_on_click_out').length || $(event.target).closest('.selector_opener').length)
    //         return;
    //     $('.hide_on_click_out').removeClass('is_open');
    //     event.stopPropagation();
    // });


    $('body').on('click', '.city_block', function () {
        $('#cityURL').val($(this).data('url'));
        $('#cityId').val($(this).data('id'));
        var gtURL = $('#gtURL').val();
        UIkit.modal('#city_popular').hide();
        var cityId = $('#cityId').val();
        $('.city_selector').html($(this).data('header'));
        $.ajax({
            url: '/ajax/getExistMarks',
            type: 'POST',
            data: {cityId:cityId,gtURL:gtURL},
            success: function(html){
                $('#mark_placement').html(html);
                $.ajax({
                    url: '/ajax/getExistModels',
                    type: 'POST',
                    data: {markId:$('#markURL').data('id'), cityId:cityId},
                    success: function(html){
                        $('#model_placement').html(html);
                    }
                });
            }
        });
    });

    $('body').on('click','.mark_block', function () {
        $('#markURL').val($(this).data('url')).attr('data-id',$(this).data('id'));
        UIkit.modal('#mark_popular').hide();
        $('.mark_selector').html($(this).data('header'));
        var id = $(this).data('id');
        var cityId = $('#cityId').val();
        $.ajax({
            url: '/ajax/getExistModels',
            type: 'POST',
            data: {markId:id, cityId:cityId},
            success: function(html){
                $('#model_placement').html(html);
            }
        });
    });

    $('body').on('click','.model_block', function () {
        $('#modelURL').val($(this).data('url'));
        UIkit.modal('#model_popular').hide();
        $('.model_selector').html($(this).data('header'));
    });


    $('.gt_selector').on('click', function () {
        // var gtURL = $(this).data('url');
        // var cityId = $('#cityId').val();
        // if ($("#gt_popular").length > 0) UIkit.modal('#gt_popular').hide();
        //
        // $('.pop_gt_selector').html($(this).data('header'));
        //
        // $.ajax({
        //     url: '/ajax/getExistMarks',
        //     type: 'POST',
        //     data: {cityId:cityId,gtURL:gtURL},
        //     success: function(html){
        //         //alert(html);
        //         if(html) {
        //             $('.gt_selector .inner').removeClass('active');
        //             $('.gt_selector[data-url="'+gtURL+'"] .inner').addClass('active');
        //             $('#gtURL').val(gtURL);
        //             $('#mark_placement').html(html);
        //             $.ajax({
        //                 url: '/ajax/getExistModels',
        //                 type: 'POST',
        //                 data: {markId: $('#markURL').data('id'), cityId: cityId},
        //                 success: function (html) {
        //                     $('#model_placement').html(html);
        //                 }
        //             });
        //         } else {
        //             //alert('Данного типа транспорта нет в выбранном городе!');
        //         }
        //     }
        // });
    });

});

function getSelectorUrl(){
    var city = '/rus';
    var generalType = '/alltypes';

    var markModel = '';

    // var country = $('#countryCode').find('option:selected').data('url');
    // var regionId = $('#regionId').find('option:selected').data('url');
    // var cityId = $('#cityId').find('option:selected').data('url');

    city = '/'+$('#cityURL').val();

    var generalParent = $('#generalTypeTopLevelId').find('option:selected').data('url');
    //var general = $('#generalTypeId').find('option:selected').data('url');
    var general = $('#gtURL').val();

    // var mark = $('#markId').find('option:selected').data('url');
    var mark = $('#markURL').val();
    //var model = $('#markModelId').find('option:selected').data('url');
    var model = $('#modelURL').val();

    var service = $('#service').find('option:selected').val();

    if (service) service = '/'+service;
    else service = '/all';

    // if(country) city = '/'+country;
    // if(regionId) city = '/'+regionId;
    // if(cityId) city = '/'+cityId;

    if (generalParent) generalType = '/'+generalParent;
    if (general) generalType = '/'+general;

    if (mark && mark !== 'Любая марка') markModel = '/'+mark;
    if (mark && model && mark !== 'Любая марка' && model !== 'Любая модель') markModel = '/'+mark+'/'+model;

    if (service === '/all' && generalType === '/alltypes' && markModel === ''){
        service = '';
        generalType = '';
    }

    return '/show'+city+service+generalType+markModel;
}

function getQueryVars() {
    var view = $('#main_search_button').data('view'); view ? view = 'view='+view : view = '';
    var page = $('#main_search_button').data('page'); page ? page = '&page='+page : page = '';
    var onpage = $('#main_search_button').data('onpage'); onpage ? onpage = '&onpage='+onpage : onpage = '';
    var order = $('#order').find('option:selected').val(); order ? order = '&order='+ order : order = '';
    if (view+page+onpage+order) return '?'+view+page+onpage+order;
    else return '';
}

