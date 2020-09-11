const mobileUserOrderPage = (function($) {

    let ui = {
        $messageInputField: $('.js-message-input-field')
    };

    let messengerBody = document.querySelector('.messenger__body');

    function init() {
        _bindHandlers();
        messengerBody.scrollTop = messengerBody.scrollHeight;
    }

    function _bindHandlers() {
        ui.$messageInputField.on('input', _autoResizeField);
    }

    function _autoResizeField(e) {

        if(!$(this).attr('rows') < 3) {
            this.style = '';
            return;
        }

        this.style.height = '1px';
        this.style.height = (this.scrollHeight + 6) + 'px';
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(mobileUserOrderPage.init);
