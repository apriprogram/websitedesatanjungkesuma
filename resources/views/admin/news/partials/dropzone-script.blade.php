@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-dropzone-input]').forEach(function (input) {
                const body = input.closest('[data-dropzone-body]');
                if (!body) {
                    return;
                }
                const preview = body.querySelector('[data-dropzone-preview]');
                const placeholder = body.querySelector('[data-dropzone-placeholder]');
                const info = body.querySelector('.news-upload-card__status');
                const filenameEl = body.querySelector('[data-dropzone-filename]');
                const removeButtons = body.querySelectorAll('[data-dropzone-remove]');
                let currentUrl = null;
                const previewWrapper = body.querySelector('.news-upload-card__preview');
                const dropArea = body.querySelector('.news-upload-card');
                const dropButton = body.querySelector('[data-dropzone-open]');

                function resetPreview() {
                    if (preview) {
                        preview.hidden = true;
                        if (currentUrl) {
                            URL.revokeObjectURL(currentUrl);
                        }
                        preview.src = preview.dataset.placeholder || '';
                    }
                    if (info) {
                        info.hidden = true;
                        info.classList.remove('visible');
                    }
                    if (placeholder) {
                        placeholder.hidden = false;
                    }
                    filenameEl && (filenameEl.textContent = '');
                    input.value = '';
                    previewWrapper?.classList.remove('is-visible');
                }

                function updatePreview(file) {
                    if (!file) {
                        resetPreview();
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = () => {
                        if (preview) {
                            preview.src = reader.result;
                            preview.hidden = false;
                            previewWrapper?.classList.add('is-visible');
                            preview.classList.remove('is-loading');
                        }
                        if (info && filenameEl) {
                            info.hidden = false;
                            info.classList.add('visible');
                            filenameEl.textContent = file.name;
                        }
                        if (placeholder) {
                            placeholder.hidden = true;
                        }
                    };
                    preview?.classList.add('is-loading');
                    reader.readAsDataURL(file);
                }

                if (preview && preview.src && preview.src !== (preview.dataset.placeholder || '')) {
                    previewWrapper?.classList.add('is-visible');
                    if (placeholder) {
                        placeholder.hidden = true;
                    }
                    if (info) {
                        info.hidden = !(filenameEl && filenameEl.textContent.trim());
                        if (info.hidden) {
                            info.classList.remove('visible');
                        }
                    }
                }

                removeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        resetPreview();
                    });
                });

                input.addEventListener('change', function () {
                    updatePreview(this.files[0]);
                });

                dropButton?.addEventListener('click', () => input.click());

                const preventDefault = event => {
                    event.preventDefault();
                    event.stopPropagation();
                };

                const activateDrop = () => dropArea?.classList.add('is-drag-over');
                const deactivateDrop = () => dropArea?.classList.remove('is-drag-over');

                dropArea?.addEventListener('dragover', event => {
                    preventDefault(event);
                    activateDrop();
                });

                dropArea?.addEventListener('dragleave', event => {
                    preventDefault(event);
                    deactivateDrop();
                });

                dropArea?.addEventListener('drop', event => {
                    preventDefault(event);
                    deactivateDrop();
                    const file = event.dataTransfer?.files?.[0];
                    if (file) {
                        input.files = event.dataTransfer.files;
                        updatePreview(file);
                    }
                });
            });
        });
    </script>
@endpush
