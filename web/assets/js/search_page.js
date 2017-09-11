$( document ).ready(function() {

    $('.view_select').on('click', function () {
        $('#main_search_button').attr('data-view',$(this).val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('#pager').find('button[name="page"]').on('click', function () {
        $('#main_search_button').attr('data-page',$(this).val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('#onpage').on('change', function () {
        $('#main_search_button').attr('data-onpage',$(this).find('option:selected').val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('#order').on('change', function () {
        $('#main_search_button').attr('data-order',$(this).find('option:selected').val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

});