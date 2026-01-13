(function() {
    'use strict';

    window.SagasAdminImage = {
        initDropzone: function() {
            const dropzone = document.getElementById('sagaDropzone');
            const fileInput = document.getElementById('sagaImageFile');
            const preview = document.getElementById('sagaImagePreview');

            if (!dropzone || !fileInput) return;

            dropzone.addEventListener('click', function() {
                fileInput.click();
            });

            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });

            dropzone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    window.SagasAdminImage.preview(fileInput);
                }
            });

            fileInput.addEventListener('change', function() {
                window.SagasAdminImage.preview(this);
            });
        },

        preview: function(input) {
            if (input && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('sagaImagePreview');
                    const dropzoneContent = document.querySelector('.saga-dropzone-content');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (dropzoneContent) {
                        dropzoneContent.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    };
})();

