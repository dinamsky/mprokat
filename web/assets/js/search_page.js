$( document ).ready(function() {

    $('.view_select').on('click', function () {
        $('input[name="view"]').val($(this).val());
        $('#searchForm').submit();
    });

    $('#onpage').on('change', function () {
        $('#searchForm').submit();
    });

});