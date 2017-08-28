$( document ).ready(function() {

    tinymce.init({
        selector: '.visual_editor',
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak",
            "searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
            "table contextmenu template textcolor paste textcolor colorpicker textpattern autoresize"
        ],
        toolbar1: "removeformat | bold italic underline | alignleft aligncenter alignright alignjustify | formatselect | bullist numlist | link unlink",
        menubar: false,
        fontsize_formats: '10px 14px 16px 24px 32px',
        //file_browser_callback: RoxyFileBrowser,
        forced_root_block: false,
        force_p_newlines: false,
        remove_linebreaks: false,
        force_br_newlines: true,
        remove_trailing_nbsp: false,
        statusbar: false,
        verify_html: false,
        autoresize_bottom_margin: 16,
        language: 'ru'
    });

});