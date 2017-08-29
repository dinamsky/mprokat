$( document ).ready(function() {

    $('.view_select').on('click', function () {
        $('input[name="view"]').val($(this).val());
        $('#searchForm').submit();
    });

});