const imagesUpload = (function($) {

    let ui = {
        $uploadPhoto: $('.js-upload-photo'),
        $uploadArea: $('.js-upload-area'),
    };

    let isSortableActive = false;

    function init() {
        _bindHandlers();
    }

    function _bindHandlers() {
        ui.$uploadPhoto.on('change', _addPhotos);
        $(document).on('click', '.js-delete-photo', _deletePhoto);
    }

    function _deletePhoto() {
        let $this = $(this);
        $this.closest('.js-photo-preview').remove();
    }

    function _addPhotos() {
        let $this = $(this),
            photos = $this[0].files;
        
        photos = [...photos]

        photos.forEach(previewFile);
        if(!isSortableActive) UIkit.sortable('.js-upload-area', {});
    }

    function previewFile(photo) {
        let reader = new FileReader();
        reader.readAsDataURL(photo);
        reader.onloadend = function() {
            //let img = document.createElement('img');
            //img.src = reader.result;
            let img =
                '<li class="js-photo-preview">' +
                    '<div class="uk-cover-container uk-inline uk-height-small uk-panel js-photo-preview-card">' +
                        '<img src="' + reader.result + '" alt="">' +
                        '<div class="uk-position-top-right">' +
                            '<a href="javascript://" class="uk-padding-small uk-overlay-primary js-delete-photo" uk-close></a>' +
                        '</div>' +
                    '</div>' +
                    '<input type="hidden" name="to_upload[]" value="' + photo.name + '">' +
                '</li>';
            ui.$uploadArea.append(img);
            //document.getElementById('gallery').appendChild(img);
            console.log(photo);
        }
    }

    return {
        init: init
    }

})(jQuery);

jQuery(document).ready(imagesUpload.init);

/*
// ************************ Drag and drop ***************** //
let dropArea = document.getElementById("drop-area")

// Prevent default drag behaviors
;['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false)
    document.body.addEventListener(eventName, preventDefaults, false)
})

// Highlight drop area when item is dragged over it
;['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false)
})

;['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false)
})

// Handle dropped files
dropArea.addEventListener('drop', handleDrop, false)

function preventDefaults (e) {
    e.preventDefault()
    e.stopPropagation()
}

function highlight(e) {
    dropArea.classList.add('highlight')
}

function unhighlight(e) {
    dropArea.classList.remove('highlight')
}

function handleDrop(e) {
    var dt = e.dataTransfer
    var files = dt.files

    handleFiles(files)
}

let uploadProgress = []
let progressBar = document.getElementById('progress-bar')

function initializeProgress(numFiles) {
    progressBar.value = 0
    uploadProgress = []

    for(let i = numFiles; i > 0; i--) {
        uploadProgress.push(0)
    }
}

function updateProgress(fileNumber, percent) {
    uploadProgress[fileNumber] = percent
    let total = uploadProgress.reduce((tot, curr) => tot + curr, 0) / uploadProgress.length
    console.debug('update', fileNumber, percent, total)
    progressBar.value = total
}

function handleFiles(files) {
    files = [...files]
    initializeProgress(files.length)
    //files.forEach(uploadFile)
    files.forEach(previewFile)
}

function previewFile(file) {
    let reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onloadend = function() {
        let img = document.createElement('img')
        img.src = reader.result
        document.getElementById('gallery').appendChild(img)
    }
}

function uploadFile(file, i) {
    var url = 'https://api.cloudinary.com/v1_1/joezimim007/image/upload'
    var xhr = new XMLHttpRequest()
    var formData = new FormData()
    xhr.open('POST', url, true)
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')

    // Update progress (can be used to show progress indicator)
    xhr.upload.addEventListener("progress", function(e) {
        updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
    })

    xhr.addEventListener('readystatechange', function(e) {
        if (xhr.readyState == 4 && xhr.status == 200) {
            updateProgress(i, 100) // <- Add this
        }
        else if (xhr.readyState == 4 && xhr.status != 200) {
            // Error. Inform the user
        }
    })

    formData.append('upload_preset', 'ujpu6gyk')
    formData.append('file', file)
    xhr.send(formData)
}


 */
