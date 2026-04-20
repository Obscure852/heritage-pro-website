{{-- Cropper.js CDN --}}
<link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">

<style>
    .crop-modal .modal-body {
        padding: 0;
        height: 70vh;
        overflow: hidden;
        background: #1a1a2e;
    }

    .crop-modal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 20px;
    }

    .crop-modal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 12px 20px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    #cropImage {
        display: block;
        max-width: 100%;
    }
</style>

{{-- Crop Modal --}}
<div class="modal fade crop-modal" id="cropModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-crop-alt me-2"></i>
                    <span id="cropModalTitleText">Crop Your Photo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img id="cropImage" src="" alt="Crop preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-loading" id="cropConfirmBtn">
                    <span class="btn-text"><i class="fas fa-check me-1"></i> Crop & Save</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    var CropHelper = (function() {
        var cropper = null;
        var cropModal = null;
        var cropImage = document.getElementById('cropImage');
        var cropConfirmBtn = document.getElementById('cropConfirmBtn');
        var cropModalTitleText = document.getElementById('cropModalTitleText');
        var currentCallback = null;
        var currentInput = null;
        var currentSourceFile = null;
        var processing = false;
        var cropCompleted = false;
        var MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
        var defaultOptions = {
            title: 'Crop Your Photo',
            aspectRatio: 1,
            outputWidth: 300,
            outputHeight: 300,
            outputMimeType: 'image/jpeg',
            outputQuality: 0.9,
            allowedTypes: ['image/jpeg', 'image/png'],
            fileTypeErrorMessage: 'Please select a JPEG or PNG image.',
            maxFileSize: MAX_FILE_SIZE,
            maxFileSizeErrorMessage: 'File size exceeds 10MB limit. Please select a smaller image.',
            cropperOptions: {}
        };
        var currentOptions = Object.assign({}, defaultOptions);

        function destroyCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            cropImage.src = '';
        }

        function setModalTitle(title) {
            if (cropModalTitleText) {
                cropModalTitleText.textContent = title || defaultOptions.title;
            }
        }

        function buildOptions(options) {
            var mergedOptions = Object.assign({}, defaultOptions, options || {});
            mergedOptions.cropperOptions = Object.assign({}, defaultOptions.cropperOptions, options && options.cropperOptions ? options.cropperOptions : {});
            return mergedOptions;
        }

        function resetButton() {
            processing = false;
            cropConfirmBtn.classList.remove('loading');
            cropConfirmBtn.disabled = false;
        }

        function clearInputCropState(fileInput) {
            if (!fileInput) {
                return;
            }

            fileInput._croppedFile = null;
            fileInput._cropAjaxFallback = false;
        }

        function extractErrorMessage(payload, fallbackMessage) {
            if (!payload) {
                return fallbackMessage;
            }

            if (payload.message) {
                return payload.message;
            }

            if (payload.errors) {
                for (var key in payload.errors) {
                    if (Object.prototype.hasOwnProperty.call(payload.errors, key) && payload.errors[key] && payload.errors[key][0]) {
                        return payload.errors[key][0];
                    }
                }
            }

            return fallbackMessage;
        }

        function setFormSubmitState(form, isLoading) {
            if (!form) {
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (!submitBtn) {
                return;
            }

            if (isLoading) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                return;
            }

            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }

        // Clean up on modal close
        document.getElementById('cropModal').addEventListener('hidden.bs.modal', function() {
            var inputToReset = currentInput;
            destroyCropper();
            resetButton();
            currentCallback = null;
            // Only clear the file input if the user cancelled (not after a successful crop)
            if (!cropCompleted && inputToReset) {
                inputToReset.value = '';
            }
            currentInput = null;
            currentSourceFile = null;
            currentOptions = Object.assign({}, defaultOptions);
            setModalTitle(defaultOptions.title);
            cropCompleted = false;
        });

        // Crop & Save button click
        cropConfirmBtn.addEventListener('click', function() {
            if (!cropper || !currentCallback || processing) return;

            processing = true;
            cropConfirmBtn.classList.add('loading');
            cropConfirmBtn.disabled = true;

            var canvas = cropper.getCroppedCanvas({
                width: currentOptions.outputWidth,
                height: currentOptions.outputHeight,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
                fillColor: currentOptions.fillColor
            });

            if (!canvas) {
                Swal.fire('Error', 'Failed to crop image. Please try again.', 'error');
                resetButton();
                return;
            }

            var cb = currentCallback;
            canvas.toBlob(function(blob) {
                if (!cb) return;
                if (!blob) {
                    Swal.fire('Error', 'Failed to process image. Please try again.', 'error');
                    resetButton();
                    return;
                }
                cropCompleted = true;
                cb(blob, {
                    input: currentInput,
                    sourceFile: currentSourceFile,
                    options: currentOptions
                });
            }, currentOptions.outputMimeType, currentOptions.outputQuality);
        });

        return {
            init: function(fileInput, onCropped, options) {
                if (!fileInput) {
                    return;
                }

                if (!cropModal) {
                    cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
                }

                fileInput.addEventListener('change', function() {
                    if (!this.files || !this.files[0]) return;

                    clearInputCropState(this);

                    var file = this.files[0];
                    currentOptions = buildOptions(options);
                    setModalTitle(currentOptions.title);

                    // Validate file type
                    if (currentOptions.allowedTypes.length && currentOptions.allowedTypes.indexOf(file.type) === -1) {
                        Swal.fire('Error', currentOptions.fileTypeErrorMessage, 'error');
                        this.value = '';
                        return;
                    }

                    // Validate file size (10MB for input, cropped output will be much smaller)
                    if (file.size > currentOptions.maxFileSize) {
                        Swal.fire('Error', currentOptions.maxFileSizeErrorMessage, 'error');
                        this.value = '';
                        return;
                    }

                    currentInput = this;
                    currentCallback = onCropped;
                    currentSourceFile = file;

                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // Destroy previous cropper instance only (not the callback)
                        destroyCropper();

                        var modalEl = document.getElementById('cropModal');

                        function initCropper() {
                            var modalBody = document.querySelector('#cropModal .modal-body');
                            cropper = new Cropper(cropImage, Object.assign({
                                aspectRatio: 1,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 0.8,
                                responsive: true,
                                restore: false,
                                guides: true,
                                center: true,
                                highlight: false,
                                cropBoxMovable: true,
                                cropBoxResizable: true,
                                toggleDragModeOnDblclick: false,
                                background: true,
                                minContainerWidth: modalBody.clientWidth,
                                minContainerHeight: modalBody.clientHeight
                            }, currentOptions.cropperOptions, {
                                aspectRatio: currentOptions.aspectRatio
                            }));
                        }

                        // Set image src, show modal, then init cropper once both are ready
                        cropImage.src = e.target.result;
                        cropModal.show();

                        // Always wait for shown.bs.modal to ensure modal has dimensions
                        var onShown = function() {
                            modalEl.removeEventListener('shown.bs.modal', onShown);
                            initCropper();
                        };
                        modalEl.addEventListener('shown.bs.modal', onShown);
                    };
                    reader.onerror = function() {
                        Swal.fire('Error', 'Failed to read the image file.', 'error');
                        fileInput.value = '';
                    };
                    reader.readAsDataURL(file);
                });
            },

            hideModal: function() {
                if (cropModal) {
                    cropModal.hide();
                }
            },

            attachFileToInput: function(fileInput, file) {
                if (!fileInput || !file) {
                    return false;
                }

                try {
                    if (typeof DataTransfer === 'undefined') {
                        throw new Error('DataTransfer is not available.');
                    }

                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    fileInput._croppedFile = file;
                    fileInput._cropAjaxFallback = false;
                    return true;
                } catch (error) {
                    fileInput.value = '';
                    fileInput._croppedFile = file;
                    fileInput._cropAjaxFallback = true;
                    return false;
                }
            },

            bindAjaxFallback: function(form, fieldName, fileInput, options) {
                if (!form || !fieldName || !fileInput || form.dataset.cropAjaxFallbackBound === '1') {
                    return;
                }

                form.dataset.cropAjaxFallbackBound = '1';

                form.addEventListener('submit', function(event) {
                    if (!fileInput._cropAjaxFallback || !fileInput._croppedFile) {
                        return;
                    }

                    event.preventDefault();

                    if (form.dataset.cropAjaxFallbackSubmitting === '1') {
                        return;
                    }

                    form.dataset.cropAjaxFallbackSubmitting = '1';
                    setFormSubmitState(form, true);

                    var formData = new FormData(form);
                    var croppedFile = fileInput._croppedFile;
                    formData.set(fieldName, croppedFile, croppedFile.name || fieldName);

                    fetch(form.action, {
                        method: (form.method || 'POST').toUpperCase(),
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(response) {
                        return response.json().catch(function() {
                            return {};
                        }).then(function(payload) {
                            if (!response.ok || payload.success === false) {
                                throw new Error(extractErrorMessage(payload, 'Upload failed. Please try again.'));
                            }

                            return payload;
                        });
                    })
                    .then(function(payload) {
                        if (options && typeof options.onSuccess === 'function') {
                            options.onSuccess(payload);
                            return;
                        }

                        window.location.reload();
                    })
                    .catch(function(error) {
                        if (options && typeof options.onError === 'function') {
                            options.onError(error);
                            return;
                        }

                        Swal.fire('Error', error.message || 'Upload failed. Please try again.', 'error');
                    })
                    .finally(function() {
                        form.dataset.cropAjaxFallbackSubmitting = '0';
                        setFormSubmitState(form, false);
                    });
                });
            }
        };
    })();
</script>
