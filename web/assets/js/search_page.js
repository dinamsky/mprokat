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

    $('#service').on('change', function () {
        $('#main_search_button').attr('data-service',$(this).find('option:selected').val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('.price_sort').on('click', function () {
        $('#order').val($(this).val());
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('.body_more').on('click', function () {
        $('.body_type_main').hide();
        $('.body_type_all').removeAttr('hidden');
        UIkit.update(event = 'update');
    });

    $('#filter_search').on('click', function () {
        document.location.href = getSelectorUrl() + getQueryVars() + '&' + $("#filter_form").serialize();
    });

    $('#filter_search_reset').on('click', function () {
        document.location.href = getSelectorUrl() + getQueryVars();
    });

    $('.filter_label').on('click', function () {
        if($(this).hasClass('active')){
            $(this).parent('.filter_container').find('.filter_content').removeClass('is_expanded').addClass('is_collapsed');
            $(this).removeClass('active');
        } else {
            $(this).parent('.filter_container').find('.filter_content').removeClass('is_collapsed').addClass('is_expanded');
            $(this).addClass('active');
        }
    });

});