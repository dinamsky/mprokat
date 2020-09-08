const userMessages = (function($) {

    let ui = {
        $contactsList: $('.js-contacts-list'),
        $dialogsList: $('.js-dialogs-list')
    };

    function init() {
        _bindHandlers();
        _scrollToBottom();
    }

    function _bindHandlers() {
        UIkit.util.on(ui.$dialogsList, 'show', _scrollToBottom);
    }

    function _scrollToBottom() {
        let chatContent = document.querySelectorAll('.chat-content');
        chatContent.forEach( el => {
            el.scrollTop = el.scrollHeight;
        });
    }



    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(userMessages.init);
