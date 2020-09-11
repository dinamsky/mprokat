const userCards = (function($) {

    let ui = {
        $deleteLink: $('.js-delete-card')
    };

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        ui.$deleteLink.on('click', _deleteProduct);
    }

    function _deleteProduct(e) {
        e.preventDefault();
        e.target.blur();

        let $this = $(this),
            cardId = $this.attr('data-cardId'),
            returnUrl = $this.attr('data-returnUrl'),
            dropdown = $this.parents('.theme-dropdown');

        UIkit.dropdown(dropdown).hide(0);

        UIkit.modal.confirm('Вы уверены?').then(function () {
            _deleteConfirmed(cardId, returnUrl);
        }, function () {
            // Rejected
        });
    }

    function _deleteConfirmed(cardId, returnUrl) {

        let data = {
            cardId: cardId,
            return: returnUrl,
            delete: ''
        };

        $.ajax({
            url: '/card/update',
            type: 'POST',
            data: data,
            success: function(response){
                window.location.href = returnUrl;
            }
        });

    }


    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(userCards.init);
