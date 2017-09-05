$( document ).ready(function() {

    $('#regionId').on('change', function () {
        $('.city_selector').html($(this).find('option:selected').html());
    });

    $('#cityId').on('change', function () {
        $('.city_selector').html($(this).find('option:selected').html());
    });

    $('#generalTypeTopLevelId').on('change', function () {
        $('.general_selector').html($(this).find('option:selected').html());
    });

    $('#generalTypeId').on('change', function () {
        $('.general_selector').html($(this).find('option:selected').html());
    });

    $('#markGroupName').on('change', function () {
        $('.mark_selector').html($(this).find('option:selected').html());
    });

    $('#markId').on('change', function () {
        $('.mark_selector').html($(this).find('option:selected').html());
    });

    $('#markModelId').on('change', function () {
        $('.mark_selector').html($(this).find('option:selected').html());
    });
});