const imagesUpload = (function($) {

    let ui = {
        $uploadPhoto: $('.js-upload-photo-main'),
        $uploadArea: $('.js-upload-area'),
        $previewPhotosError: $('.js-preview-photos-error'),
    };

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        _updSortableGrid();
        ui.$uploadPhoto.on('change', _addPhotos);
        $(document).on('click', '.js-delete-photo', _deletePhoto);
    }

    function _updSortableGrid() {
        UIkit.grid(ui.$uploadArea);
        UIkit.sortable(ui.$uploadArea);
    }

    function _deletePhoto() {
        let $this = $(this);
        $this.closest('.js-photo-preview').remove();
    }

    function _addPhotos() {
        let $this = $(this),
            fileClone = $(this).clone(),
            photos = $this[0].files,
            photosLength = photos.length;

        console.log(photosLength);

        fileClone.attr('name', 'fotos[]').addClass('uk-hidden').removeClass('js-upload-photo-main');

        if(photosLength > 1) {
            photos = [...photos];
            _addFileInput(fileClone);
            photos.forEach(_previewFile);
        } else {
            _addNewPreview(fileClone, photos[0]);
        }

        ui.$previewPhotosError.hide();
        _updSortableGrid();
        _clearUploadInput();
        //if(!photos.length) ui.$previewPhotosError.show();
    }

    function _addFileInput(fileHtml) {
        ui.$uploadArea.append(fileHtml);
    }

    function _addNewPreview(fileHtml, photo) {
        let reader = new FileReader();

        reader.readAsDataURL(photo);

        let photoSize = formatBytes(photo.size);
        reader.onloadend = function() {

            let img = '<li class="js-photo-preview">';
            img += '<div class="uk-cover-container uk-inline uk-height-small uk-panel js-photo-preview-card">' +
                '<img src="' + reader.result + '" alt="">' +
                '</div>' +
                '<div class="uk-text-truncate uk-text-small uk-text-bold uk-text-left uk-margin-small-top uk-margin-small-bottom">' + photo.name + '</div>' +
                '<div class="uk-flex uk-flex-middle uk-flex-between">' +
                '<span class="uk-text-meta">' + photoSize + '</span>' +
                '<a href="javascript:;" class="js-delete-photo button-outline-primary" uk-icon="icon: trash"></a>' +
                '</div>'
            ;
            img += '<input type="hidden" name="to_upload[]" value="' + photo.name + '">';
            img += '</li>'
            ui.$uploadArea.append(fileHtml);
            ui.$uploadArea.append(img);
        }
    }

    function _clearUploadInput() {
        ui.$uploadPhoto.val('');
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function _previewFile(photo) {
        let reader = new FileReader();
        reader.readAsDataURL(photo);

        let photoSize = formatBytes(photo.size);
        reader.onloadend = function() {
            let img =
                '<li class="js-photo-preview">' +
                    '<div class="uk-cover-container uk-inline uk-height-small uk-panel js-photo-preview-card">' +
                        '<img src="' + reader.result + '" alt="">' +
                    '</div>' +
                    '<div class="uk-text-truncate uk-text-small uk-text-bold uk-text-left uk-margin-small-top uk-margin-small-bottom">' + photo.name + '</div>' +
                    '<div class="uk-flex uk-flex-middle uk-flex-between">' +
                        '<span class="uk-text-meta">' + photoSize + '</span>' +
                        '<a href="javascript:;" class="js-delete-photo button-outline-primary" uk-icon="icon: trash"></a>' +
                    '</div>' +
                    '<input type="hidden" name="to_upload[]" value="' + photo.name + '">' +
                '</li>';
            ui.$uploadArea.append(img);
        }
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(imagesUpload.init);
