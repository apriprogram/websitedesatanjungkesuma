document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const formModal = document.getElementById('residentFormModal');
    const detailModal = document.getElementById('residentDetailModal');
    const deleteModal = document.getElementById('residentDeleteModal');
    const importModal = document.getElementById('residentImportModal');

    const residentForm = document.getElementById('residentForm');
    const methodField = residentForm?.querySelector('input[name="_method"]');
    const createAction = residentForm?.dataset.createAction || '';
    const residentIdField = residentForm?.querySelector('input[name="resident_id"]');
    const residentUpdateField = residentForm?.querySelector('input[name="resident_update_url"]');
    const formModeField = residentForm?.querySelector('input[name="form_mode"]');
    const formTitle = document.getElementById('residentFormTitle');
    const formSubtitle = document.getElementById('residentFormSubtitle');
    const submitLabel = document.getElementById('residentFormSubmitLabel');

    const detailFields = detailModal
        ? detailModal.querySelectorAll('[data-detail]')
        : [];
    const detailImageFields = detailModal
        ? detailModal.querySelectorAll('[data-detail-image]')
        : [];

    const deleteNameField = deleteModal?.querySelector('#residentDeleteName');
    const deleteConfirmBtn = document.getElementById('residentDeleteConfirm');
    let pendingDeleteForm = null;

    const filterToggle = document.getElementById('filterDropdownToggle');
    const filterMenu = document.getElementById('filterDropdownMenu');
    const exportToggle = document.getElementById('exportDropdownToggle');
    const exportMenu = document.getElementById('exportDropdownMenu');
    const resetFilterBtn = document.getElementById('resetFilterBtn');

    const fieldMap = residentForm
        ? {
              no_kk: residentForm.querySelector('[name="no_kk"]'),
              nik: residentForm.querySelector('[name="nik"]'),
              nama: residentForm.querySelector('[name="nama"]'),
              jenis_kelamin_id: residentForm.querySelector('[name="jenis_kelamin_id"]'),
              tempat_lahir: residentForm.querySelector('[name="tempat_lahir"]'),
              tanggal_lahir: residentForm.querySelector('[name="tanggal_lahir"]'),
              agama_id: residentForm.querySelector('[name="agama_id"]'),
              pendidikan_kk_id: residentForm.querySelector('[name="pendidikan_kk_id"]'),
              pendidikan_sedang_id: residentForm.querySelector('[name="pendidikan_sedang_id"]'),
              pekerjaan_id: residentForm.querySelector('[name="pekerjaan_id"]'),
              pekerjaan_custom: residentForm.querySelector('[name="pekerjaan_custom"]'),
              status_kawin_id: residentForm.querySelector('[name="status_kawin_id"]'),
              kk_level_id: residentForm.querySelector('[name="kk_level_id"]'),
              warganegara_id: residentForm.querySelector('[name="warganegara_id"]'),
              nama_ayah: residentForm.querySelector('[name="nama_ayah"]'),
              ayah_nik: residentForm.querySelector('[name="ayah_nik"]'),
              nama_ibu: residentForm.querySelector('[name="nama_ibu"]'),
              ibu_nik: residentForm.querySelector('[name="ibu_nik"]'),
              golongan_darah_id: residentForm.querySelector('[name="golongan_darah_id"]'),
              akta_lahir: residentForm.querySelector('[name="akta_lahir"]'),
              dokumen_pasport: residentForm.querySelector('[name="dokumen_pasport"]'),
              tanggal_akhir_paspor: residentForm.querySelector('[name="tanggal_akhir_paspor"]'),
              dokumen_kitas: residentForm.querySelector('[name="dokumen_kitas"]'),
              akta_perkawinan: residentForm.querySelector('[name="akta_perkawinan"]'),
              tanggal_perkawinan: residentForm.querySelector('[name="tanggal_perkawinan"]'),
              akta_perceraian: residentForm.querySelector('[name="akta_perceraian"]'),
              tanggal_perceraian: residentForm.querySelector('[name="tanggal_perceraian"]'),
              cacat_id: residentForm.querySelector('[name="cacat_id"]'),
              cara_kb_id: residentForm.querySelector('[name="cara_kb_id"]'),
              hamil: residentForm.querySelector('[name="hamil"]'),
              ktp_el: residentForm.querySelector('[name="ktp_el"]'),
              status_rekam_id: residentForm.querySelector('[name="status_rekam_id"]'),
              alamat: residentForm.querySelector('[name="alamat"]'),
              alamat_sekarang: residentForm.querySelector('[name="alamat_sekarang"]'),
              dusun_id: residentForm.querySelector('[name="dusun_id"]'),
              rw_id: residentForm.querySelector('[name="rw_id"]'),
              rt_id: residentForm.querySelector('[name="rt_id"]'),
              status_dasar_id: residentForm.querySelector('[name="status_dasar_id"]'),
              suku_id: residentForm.querySelector('[name="suku_id"]'),
              tag_id_card: residentForm.querySelector('[name="tag_id_card"]'),
              id_asuransi: residentForm.querySelector('[name="id_asuransi"]'),
              no_asuransi: residentForm.querySelector('[name="no_asuransi"]'),
          }
        : {};
    const photoPreviewMap = residentForm
        ? {
              foto_profil: residentForm.querySelector('[data-photo-preview="foto_profil"]'),
              foto_ktp: residentForm.querySelector('[data-photo-preview="foto_ktp"]'),
              foto_kk: residentForm.querySelector('[data-photo-preview="foto_kk"]'),
          }
        : {};
    const photoRemoveMap = residentForm
        ? {
              foto_profil: residentForm.querySelector('input[name="remove_foto_profil"]'),
              foto_ktp: residentForm.querySelector('input[name="remove_foto_ktp"]'),
              foto_kk: residentForm.querySelector('input[name="remove_foto_kk"]'),
          }
        : {};

    const importForm = importModal?.querySelector('form') ?? null;
    const importTrigger = importModal?.querySelector('[data-import-trigger]') ?? null;
    const importDropzone = importModal?.querySelector('[data-import-dropzone]') ?? null;
    const importFileInput = importModal?.querySelector('[data-import-input]') ?? null;
    const importFilename = importModal?.querySelector('[data-import-filename]') ?? null;
    const importSubmit = importModal?.querySelector('[data-import-submit]') ?? null;
    const importList = importModal?.querySelector('[data-import-file-list]') ?? null;
    const importTemplate = importModal?.querySelector('[data-import-file-template]') ?? null;
    const importStatus = importModal?.querySelector('[data-import-status]') ?? null;
    let importFile = null;
    let importUpload = null;
    let importRow = null;

    const setSubmitState = (state, label = 'Mulai Impor') => {
        if (!importSubmit) {
            return;
        }
        importSubmit.classList.remove('is-loading');
        importSubmit.disabled = false;
        importSubmit.removeAttribute('disabled');
        const labelNode = importSubmit.querySelector('[data-import-submit-text]');
        if (labelNode) {
            labelNode.textContent = label;
        }

        switch (state) {
            case 'loading':
                importSubmit.classList.add('is-loading');
                importSubmit.disabled = true;
                importSubmit.setAttribute('disabled', 'disabled');
                break;
            case 'disabled':
                importSubmit.disabled = true;
                importSubmit.setAttribute('disabled', 'disabled');
                break;
            default:
                break;
        }
    };

    const normaliseErrorValue = (value) => {
        if (value === null || value === undefined) {
            return [];
        }
        if (Array.isArray(value)) {
            return value.flatMap((item) => normaliseErrorValue(item));
        }
        if (typeof value === 'object') {
            return Object.values(value).flatMap((item) => normaliseErrorValue(item));
        }
        return [String(value)];
    };

    const normaliseErrors = (errors, fallbackMessage = null) => {
        const flattened = normaliseErrorValue(errors).filter(Boolean);
        const deduped = [...new Set(flattened)];
        if (deduped.length === 0 && fallbackMessage) {
            return [fallbackMessage];
        }
        return deduped;
    };

    const formatSummary = (summary) => {
        if (!summary || typeof summary !== 'object') {
            return '';
        }
        const toNumber = (value) => {
            if (value === null || value === undefined) {
                return 0;
            }
            const numeric = Number(value);
            return Number.isFinite(numeric) ? numeric : 0;
        };
        const created = toNumber(summary.created);
        const updated = toNumber(summary.updated);
        const skipped = toNumber(summary.skipped);
        return `Ditambahkan: ${created}, diperbarui: ${updated}, dilewati: ${skipped}.`;
    };

    const syncBodyModalState = () => {
        const visibleModal = document.querySelector('.dialog-backdrop.is-visible');
        if (visibleModal) {
            body.classList.add('modal-open');
        } else {
            body.classList.remove('modal-open');
        }
    };

    const focusFirstElement = (modal) => {
        const element = modal?.querySelector(
            'button:not([disabled]), a[href], input, select, textarea'
        );
        element?.focus({ preventScroll: true });
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-visible');
        syncBodyModalState();
        requestAnimationFrame(() => focusFirstElement(modal));
    };

    const resetImportState = () => {
        if (importUpload) {
            importUpload.abort();
            importUpload = null;
        }
        importFile = null;
        importRow = null;
        importDropzone?.classList.remove('is-active');
        if (importFileInput) {
            importFileInput.value = '';
        }
        if (importFilename) {
            importFilename.textContent = 'Belum ada berkas dipilih';
        }
        if (importList) {
            importList.innerHTML = '';
        }
        if (importStatus) {
            importStatus.innerHTML = '';
            importStatus.classList.remove('is-info', 'is-success', 'is-error');
        }
        setSubmitState('disabled');
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        if (modal === deleteModal) {
            pendingDeleteForm = null;
        }
        if (modal === importModal) {
            resetImportState();
        }
        syncBodyModalState();
    };

    const parseJSON = (raw) => {
        if (!raw) return {};
        try {
            return JSON.parse(raw);
        } catch (error) {
            return {};
        }
    };

    const setPhotoPreview = (key, url = '') => {
        const container = photoPreviewMap[key];
        if (!container) return;
        container.innerHTML = '';
        if (url) {
            const label = key.replace(/_/g, ' ');
            const img = document.createElement('img');
            img.src = url;
            img.alt = `Foto ${label}`;
            container.appendChild(img);
        } else {
            const placeholder = document.createElement('span');
            placeholder.className = 'media-preview__placeholder';
            placeholder.textContent = 'Belum ada foto.';
            container.appendChild(placeholder);
        }
    };

    const resetPhotoControls = () => {
        Object.keys(photoPreviewMap).forEach((key) => setPhotoPreview(key, ''));
        Object.values(photoRemoveMap).forEach((checkbox) => {
            if (checkbox) {
                checkbox.checked = checkbox.defaultChecked;
            }
        });
    };

    const resetForm = () => {
        if (!residentForm) return;
        residentForm.reset();
        resetPhotoControls();
        if (methodField) {
            methodField.disabled = true;
            methodField.value = 'PUT';
        }
        if (formModeField) {
            formModeField.value = 'create';
        }
        if (residentIdField) {
            residentIdField.value = '';
        }
        if (residentUpdateField) {
            residentUpdateField.value = '';
        }
        residentForm.action = createAction;
    };

    const formatBytes = (value) => {
        if (!value || Number.isNaN(value)) {
            return '0 KB';
        }
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = value;
        let index = 0;
        while (size >= 1024 && index < units.length - 1) {
            size /= 1024;
            index += 1;
        }
        return `${size.toFixed(size >= 10 || index === 0 ? 0 : 1)} ${units[index]}`;
    };

    const setImportStatus = (variant, message, errors = []) => {
        if (!importStatus) {
            return;
        }
        importStatus.classList.remove('is-info', 'is-success', 'is-error');
        if (variant) {
            importStatus.classList.add(`is-${variant}`);
        }
        if (errors.length > 0) {
            const items = errors.map((error) => `<li>${error}</li>`).join('');
            importStatus.innerHTML = `<p>${message}</p><ul>${items}</ul>`;
        } else {
            importStatus.textContent = message;
        }
    };

    const renderImportRow = (file) => {
        if (!importList || !importTemplate) {
            importRow = null;
            return;
        }
        importList.innerHTML = '';
        const fragment = importTemplate.content.cloneNode(true);
        const item = fragment.querySelector('[data-file-item]');
        if (!item) {
            importRow = null;
            return;
        }
        const nameNode = fragment.querySelector('[data-file-name]');
        const sizeNode = fragment.querySelector('[data-file-size]');
        const extNode = fragment.querySelector('[data-file-ext]');
        if (nameNode) {
            nameNode.textContent = file.name;
        }
        if (sizeNode) {
            sizeNode.textContent = formatBytes(file.size);
        }
        if (extNode) {
            const ext = file.name.includes('.') ? file.name.split('.').pop()?.toUpperCase() : 'FILE';
            extNode.textContent = ext || 'FILE';
        }
        importList.appendChild(fragment);
        importRow = importList.querySelector('[data-file-item]');
        const removeBtn = importRow?.querySelector('[data-file-remove]');
        removeBtn?.addEventListener('click', () => {
            if (importUpload) {
                importUpload.abort();
            }
            resetImportState();
        });
    };

    const updateImportProgress = (percent) => {
        if (!importRow) {
            return;
        }
        const progressBar = importRow.querySelector('[data-file-progress]');
        const progressValue = Math.max(0, Math.min(100, Math.round(percent)));
        if (progressBar) {
            const isActive = importRow.classList.contains('is-uploading') || importRow.classList.contains('is-pending');
            if (isActive && progressValue < 100) {
                progressBar.classList.add('is-animated', 'is-validating');
            } else {
                progressBar.classList.remove('is-animated', 'is-validating');
            }
            progressBar.style.width = `${progressValue}%`;
            progressBar.setAttribute('aria-valuenow', progressValue.toString());
        }
        const statusLabel = importRow.querySelector('[data-file-status]');
        if (statusLabel && importRow.classList.contains('is-uploading')) {
            statusLabel.textContent = `${progressValue}%`;
        }

        const iconContainer = importRow.querySelector('[data-file-status-icon]');
        if (iconContainer && importRow.classList.contains('is-uploading')) {
            iconContainer.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
    };

    const setRowState = (state, message) => {
        if (!importRow) {
            return;
        }
        const statusLabel = importRow.querySelector('[data-file-status]');
        if (statusLabel) {
            statusLabel.textContent = message;
        }
        importRow.classList.remove('is-uploading', 'is-success', 'is-error', 'is-pending', 'is-ready');
        importRow.classList.add(`is-${state}`);

        const iconContainer = importRow.querySelector('[data-file-status-icon]');
        if (iconContainer) {
            iconContainer.innerHTML = '';
            if (state === 'uploading' || state === 'pending') {
                iconContainer.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            } else if (state === 'success' || state === 'ready') {
                iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
            } else if (state === 'error') {
                iconContainer.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
            }
        }

        const progressBar = importRow.querySelector('[data-file-progress]');
        if (progressBar) {
            progressBar.classList.remove('is-animated', 'is-validating');
            if (state === 'pending') {
                progressBar.classList.add('is-animated', 'is-validating');
                progressBar.style.width = '35%';
                progressBar.setAttribute('aria-valuenow', '35');
            } else if (state === 'uploading') {
                progressBar.classList.add('is-animated', 'is-validating');
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', '0');
            } else if (state === 'success') {
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', '100');
            } else if (state === 'error') {
                progressBar.style.width = '0';
                progressBar.setAttribute('aria-valuenow', '0');
            }
        }
    };

    const markRowReady = () => {
        if (!importRow) {
            return false;
        }
        importRow.classList.remove('is-uploading', 'is-success', 'is-error', 'is-pending');
        importRow.classList.add('is-ready');
        const progressBar = importRow.querySelector('[data-file-progress]');
        if (progressBar) {
            progressBar.classList.remove('is-animated', 'is-validating');
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', '100');
        }
        const statusLabel = importRow.querySelector('[data-file-status]');
        if (statusLabel) {
            statusLabel.textContent = 'Siap diimpor';
        }

        const iconContainer = importRow.querySelector('[data-file-status-icon]');
        if (iconContainer) {
            iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
        }

        setImportStatus('info', 'Berkas siap diimpor. Klik "Mulai Impor" untuk memproses.');
        setSubmitState('ready', 'Mulai Impor');
        return true;
    };

    const handleSelectedFile = (file) => {
        if (!file) {
            return;
        }
        const extension = file.name.split('.').pop()?.toLowerCase() || '';
        if (!['xls', 'xlsx'].includes(extension)) {
            resetImportState();
            setImportStatus('error', 'Format berkas tidak didukung. Pilih file Excel (.xls atau .xlsx).');
            return;
        }
        importFile = file;
        renderImportRow(file);
        if (importFilename) {
            importFilename.textContent = file.name;
        }
        if (importDropzone) {
            importDropzone.classList.remove('is-active');
        }
        if (importFileInput) {
            importFileInput.value = '';
        }
        const ready = markRowReady();
        if (!ready) {
            setImportStatus('info', 'Berkas siap diimpor. Klik "Mulai Impor" untuk memproses.');
            setSubmitState('ready', 'Mulai Impor');
        }
    };

    const startImportUpload = () => {
        if (!importForm || !importFile || importUpload) {
            return;
        }
        const action = importForm.getAttribute('action');
        if (!action) {
            return;
        }

        const formData = new FormData();
        const tokenField = importForm.querySelector('input[name="_token"]');
        if (tokenField) {
            formData.append('_token', tokenField.value);
        }
        formData.append('file', importFile);

        const xhr = new XMLHttpRequest();
        importUpload = xhr;

        xhr.open('POST', action);
        xhr.responseType = 'json';
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        setRowState('uploading', 'Mengunggah...');
        setImportStatus('info', 'Mengunggah berkas, mohon tunggu...');
        setSubmitState('loading', 'Mengunggah...');

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) {
                return;
            }
            const percent = (event.loaded / event.total) * 100;
            updateImportProgress(percent);
        });

        xhr.addEventListener('load', () => {
            const response = xhr.response || {};
            importUpload = null;
            if (xhr.status >= 200 && xhr.status < 300) {
                setRowState('success', 'Selesai');
                updateImportProgress(100);
                const successErrors = normaliseErrors(response.errors);
                const summaryText = formatSummary(response.summary);
                let message = response.message ?? 'Impor selesai tanpa kendala.';
                if (summaryText) {
                    message = response.message ? `${response.message} ${summaryText}` : `Impor selesai. ${summaryText}`;
                }
                setImportStatus('success', message.trim(), successErrors);
                setSubmitState('ready', 'Selesai');
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else {
                const errors = normaliseErrors(response.errors, response.message ?? 'Impor gagal. Silakan coba lagi.');
                setRowState('error', 'Gagal');
                const message = response.message ?? 'Impor gagal diproses.';
                setImportStatus('error', message, errors);
                setSubmitState('ready', 'Coba Lagi');
            }
        });

        const handleFailure = () => {
            importUpload = null;
            setRowState('error', 'Gagal');
            setImportStatus('error', 'Terjadi kesalahan jaringan. Coba lagi.');
            setSubmitState('ready', 'Coba Lagi');
        };

        xhr.addEventListener('error', handleFailure);
        xhr.addEventListener('abort', handleFailure);

        xhr.send(formData);
    };
    const setFieldValue = (field, value) => {
        if (!field) return;
        if (value === undefined || value === null) {
            field.value = '';
            return;
        }
        if (typeof value === 'boolean') {
            field.value = value ? '1' : '0';
            return;
        }
        field.value = value;
    };

    const populateFields = (payload = {}) => {
        Object.entries(fieldMap).forEach(([key, field]) => {
            setFieldValue(field, payload[key]);
        });
    };

    const setFormMode = (mode, payload = {}, updateUrl = '') => {
        if (!residentForm) return;
        resetForm();

        const normalizedMode = mode === 'edit' ? 'edit' : 'create';

        if (normalizedMode === 'edit') {
            if (methodField) {
                methodField.disabled = false;
                methodField.value = 'PUT';
            }
            residentForm.action = updateUrl || createAction;
            if (residentIdField) {
                residentIdField.value = payload.id ?? payload.resident_id ?? '';
            }
            if (residentUpdateField) {
                residentUpdateField.value = updateUrl || '';
            }
            if (formModeField) {
                formModeField.value = 'edit';
            }
            populateFields(payload);
            setPhotoPreview('foto_profil', payload.foto_profil_url || '');
            setPhotoPreview('foto_ktp', payload.foto_ktp_url || '');
            setPhotoPreview('foto_kk', payload.foto_kk_url || '');
            Object.values(photoRemoveMap).forEach((checkbox) => {
                if (checkbox) {
                    checkbox.checked = false;
                }
            });
            if (formTitle) {
                formTitle.textContent = 'Edit Data Penduduk';
            }
            if (formSubtitle) {
                const subtitle = payload.nama
                    ? `Perbarui informasi penduduk <span class="resident-name-highlight">${payload.nama}</span>.`
                    : 'Perbarui informasi penduduk.';
                formSubtitle.innerHTML = subtitle;
            }
            if (submitLabel) {
                submitLabel.textContent = 'Simpan Perubahan';
            }
        } else {
            residentForm.action = createAction;
            if (formModeField) {
                formModeField.value = 'create';
            }
            if (Object.keys(payload || {}).length > 0) {
                populateFields(payload);
            }
            resetPhotoControls();
            if (formTitle) {
                formTitle.textContent = 'Tambah Data Penduduk';
            }
            if (formSubtitle) {
                formSubtitle.textContent = 'Lengkapi formulir untuk menambahkan penduduk baru ke sistem.';
            }
            if (submitLabel) {
                submitLabel.textContent = 'Simpan Data';
            }
        }
    };

    document.querySelectorAll('[data-modal-open="residentFormModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.formMode || 'create';
            if (mode === 'edit') {
                const payload = parseJSON(button.dataset.residentEdit);
                const updateUrl = button.dataset.updateUrl || createAction;
                setFormMode('edit', payload, updateUrl);
            } else {
                setFormMode('create');
            }
            openModal(formModal);
        });
    });

    document.querySelectorAll('[data-modal-open="residentDetailModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const payload = parseJSON(button.dataset.resident);
            detailFields.forEach((node) => {
                const key = node.dataset.detail;
                if (!key) return;
                const raw = payload[key];
                const isOptional = node.dataset.optional === 'true';
                const isLink = node.tagName.toLowerCase() === 'a';
                const hasValue = raw !== undefined && raw !== null && String(raw).trim() !== '' && raw !== '-';
                const fallback = isOptional ? 'Tidak ada data' : '-';

                if (isLink) {
                    if (hasValue) {
                        node.setAttribute('href', String(raw));
                        node.textContent = node.dataset.linkLabel || 'Lihat berkas';
                        node.setAttribute('target', '_blank');
                        node.setAttribute('rel', 'noopener');
                        node.classList.remove('is-empty');
                    } else {
                        node.removeAttribute('href');
                        node.textContent = fallback;
                        node.classList.add('is-empty');
                    }
                } else {
                    node.textContent = hasValue ? raw : fallback;
                    if (hasValue) {
                        node.classList.remove('is-empty');
                    } else {
                        node.classList.add('is-empty');
                    }
                }
            });
            detailImageFields.forEach((node) => {
                const key = node.dataset.detailImage;
                if (!key) return;
                const raw = payload[key];
                const isOptional = node.dataset.optional === 'true';
                const hasValue = raw !== undefined && raw !== null && String(raw).trim() !== '' && raw !== '-';
                if (hasValue) {
                    const url = String(raw);
                    node.innerHTML = `<a href="${url}" target="_blank" rel="noopener"><img src="${url}" alt="Foto ${key}"></a>`;
                    node.classList.remove('is-empty');
                } else {
                    node.innerHTML = '<span class="media-preview__placeholder">Belum ada foto.</span>';
                    if (isOptional) {
                        node.classList.add('is-empty');
                    } else {
                        node.classList.remove('is-empty');
                    }
                }
            });
            openModal(detailModal);
        });
    });
    document.querySelectorAll('[data-modal-open="residentImportModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            resetImportState();
            openModal(importModal);
        });
    });

    importTrigger?.addEventListener('click', (event) => {
        event.preventDefault();
        importFileInput?.click();
    });

    importFileInput?.addEventListener('change', (event) => {
        const input = event.target;
        if (!input || !input.files || input.files.length === 0) {
            return;
        }
        handleSelectedFile(input.files[0]);
    });

    if (importDropzone) {
        importDropzone.addEventListener('click', (event) => {
            if (event.target.closest('button')) {
                return;
            }
            importFileInput?.click();
        });

        const prevent = (event) => {
            event.preventDefault();
            event.stopPropagation();
        };

        ['dragenter', 'dragover'].forEach((eventName) => {
            importDropzone.addEventListener(eventName, (event) => {
                prevent(event);
                importDropzone.classList.add('is-active');
            });
        });

        ['dragleave', 'dragend'].forEach((eventName) => {
            importDropzone.addEventListener(eventName, (event) => {
                prevent(event);
                importDropzone.classList.remove('is-active');
            });
        });

        importDropzone.addEventListener('drop', (event) => {
            prevent(event);
            importDropzone.classList.remove('is-active');
            const files = event.dataTransfer?.files;
            if (files && files.length > 0) {
                handleSelectedFile(files[0]);
            }
        });
    }

    importModal?.addEventListener('dragover', (event) => {
        event.preventDefault();
    });

    importForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!importFile) {
            setImportStatus('error', 'Silakan pilih berkas Excel terlebih dahulu.');
            return;
        }
        startImportUpload();
    });

    document
        .querySelectorAll('[data-modal-close]')
        .forEach((button) => button.addEventListener('click', () => closeModal(button.closest('.dialog-backdrop'))));



    document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
        button.addEventListener('click', () => {
            pendingDeleteForm = button.closest('form');
            if (deleteNameField) {
                deleteNameField.textContent = button.dataset.deleteName ?? 'penduduk ini';
            }
            openModal(deleteModal);
        });
    });

    deleteConfirmBtn?.addEventListener('click', () => {
        if (pendingDeleteForm) {
            pendingDeleteForm.submit();
        }
        closeModal(deleteModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (formModal?.classList.contains('is-visible')) {
                closeModal(formModal);
            }
            if (detailModal?.classList.contains('is-visible')) {
                closeModal(detailModal);
            }

            if (importModal?.classList.contains('is-visible')) {
                closeModal(importModal);
            }
        }
    });

    // ─── PORTAL DROPDOWN (mobile: move menus to <body> to escape stacking contexts) ───
    const MOBILE_BREAKPOINT = 1024;
    let portalContainer = null;
    let portalBackdrop = null;

    const getOrCreatePortal = () => {
        if (!portalContainer) {
            portalContainer = document.createElement('div');
            portalContainer.id = 'residentPortalContainer';
            // Use massive Z-index to be above EVERYTHING (sidebar, header, etc)
            portalContainer.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:2000000000';
            document.body.appendChild(portalContainer);

            portalBackdrop = document.createElement('div');
            portalBackdrop.id = 'dropdownPortalBackdrop';
            portalBackdrop.style.cssText = [
                'position:absolute', 'inset:0', 'z-index:1', 'pointer-events:all',
                'background:rgba(0,0,0,0.5)', 'display:none', 'opacity:0', 'transition:opacity 0.2s ease'
            ].join(';');
            portalContainer.appendChild(portalBackdrop);
            
            portalBackdrop.addEventListener('click', (e) => {
                if (e.target === portalBackdrop) closeAllDropdowns();
            });
        }
        return { container: portalContainer, backdrop: portalBackdrop };
    };

    const attachPortal = (menu) => {
        if (!menu) return;
        const { container } = getOrCreatePortal();
        if (menu.parentElement !== container) {
            menu.dataset.originalParent = menu.parentElement?.id || '';
            container.appendChild(menu);
        }
        menu.style.cssText = [
            'position:fixed',
            'top:56px',
            'left:50%',
            'transform:translateX(-50%)',
            'width:calc(100vw - 24px)',
            'max-width:400px',
            'max-height:80vh',
            'overflow-y:auto',
            'z-index:2',
            'border-radius:16px',
            'box-shadow:0 24px 70px rgba(0,0,0,0.45)',
            'background-color:#ffffff',
            'pointer-events:auto',
            'scrollbar-width:none',
            '-ms-overflow-style:none',
            'margin:0',
        ].join(';');
        
        const styleId = 'portal-no-scrollbar-' + menu.id;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = '#' + menu.id + '::-webkit-scrollbar{display:none!important;width:0!important}';
            document.head.appendChild(style);
        }
    };

    // Put menu back to original parent (desktop)
    const detachPortal = (menu, originalParent) => {
        if (!menu) return;
        if (originalParent && menu.parentElement === document.body) {
            originalParent.appendChild(menu);
        }
        menu.style.cssText = '';
    };

    // Track original parent elements
    const filterMenuParent = filterMenu?.parentElement;
    const exportMenuParent = exportMenu?.parentElement;

    const closeAllDropdowns = () => {
        [
            { toggle: filterToggle, menu: filterMenu, parent: filterMenuParent },
            { toggle: exportToggle, menu: exportMenu, parent: exportMenuParent },
        ].forEach(({ toggle, menu, parent }) => {
            if (menu && toggle) {
                const wasVisible = menu.classList.contains('is-visible');
                menu.classList.remove('is-visible');
                toggle.setAttribute('aria-expanded', 'false');
                
                if (wasVisible) {
                    setTimeout(() => {
                        if (!menu.classList.contains('is-visible')) {
                            menu.hidden = true;
                            if (window.innerWidth <= MOBILE_BREAKPOINT) {
                                detachPortal(menu, parent);
                            }
                        }
                    }, 180);
                } else {
                    menu.hidden = true;
                    if (window.innerWidth <= MOBILE_BREAKPOINT) {
                        detachPortal(menu, parent);
                    }
                }
            }
        });
        
        if (portalBackdrop && portalBackdrop.style.display === 'block') {
            portalBackdrop.style.opacity = '0';
            setTimeout(() => {
                if (portalBackdrop.style.opacity === '0') {
                    portalBackdrop.style.display = 'none';
                }
            }, 200);
        }
        body.classList.remove('dropdown-open');
        document.documentElement.classList.remove('dropdown-open');
    };

    const toggleDropdown = (toggle, menu, parent) => {
        if (!toggle || !menu) return;
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        closeAllDropdowns();
        if (!isOpen) {
            const isMobile = window.innerWidth <= MOBILE_BREAKPOINT;
            if (isMobile) {
                attachPortal(menu);
                const { backdrop } = getOrCreatePortal();
                backdrop.style.display = 'block';
                requestAnimationFrame(() => {
                    backdrop.style.opacity = '1';
                });
                body.classList.add('dropdown-open');
                document.documentElement.classList.add('dropdown-open');
            }
            menu.hidden = false;
            requestAnimationFrame(() => menu.classList.add('is-visible'));
            toggle.setAttribute('aria-expanded', 'true');
        }
    };

    if (filterToggle && filterMenu) {
        filterToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(filterToggle, filterMenu, filterMenuParent);
        });
    }

    if (exportToggle && exportMenu) {
        exportToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(exportToggle, exportMenu, exportMenuParent);
        });
    }

    document.querySelectorAll('.resident-dropdown__menu').forEach((menu) => {
        menu.addEventListener('click', (e) => e.stopPropagation());
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth > MOBILE_BREAKPOINT) {
            if (!e.target.closest('.resident-dropdown__menu') && !e.target.closest('.resident-dropdown__toggle')) {
                closeAllDropdowns();
            }
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllDropdowns();
    });

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', () => {
            const form = document.getElementById('residentFilterForm');
            if (!form) return;
            form.querySelectorAll('select').forEach((select) => (select.selectedIndex = 0));
            const searchInput = form.querySelector('input[name="search"]');
            if (searchInput) searchInput.value = '';
            form.submit();
        });
    }

    const searchReset = document.querySelector('[data-search-reset]');
    if (searchReset) {
        searchReset.addEventListener('click', () => {
            const form = document.getElementById('residentFilterForm');
            if (!form) return;
            const searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.value = '';
            }
            form.submit();
        });
    }

    if (residentForm) {
        const hasErrors = residentForm.dataset.hasErrors === 'true';
        if (hasErrors) {
            const oldPayload = parseJSON(residentForm.dataset.oldPayload);
            const oldMode = residentForm.dataset.oldMode === 'edit' ? 'edit' : 'create';
            const oldUpdateUrl = residentForm.dataset.oldUpdateUrl || '';
            setFormMode(oldMode, oldPayload, oldUpdateUrl);
            openModal(formModal);
        }
    }

    // Toggle resident stats visibility (with animation)
    const statsToggleButton = document.getElementById('toggleStatsButton');
    const statsPanel = document.getElementById('residentStatsPanel');
    const statsViewport = statsPanel?.querySelector('.resident-stats__viewport');
    const statsToggleLabel = document.getElementById('toggleStatsLabel');
    const statsToggleIcon = document.getElementById('toggleStatsIcon');
    const prefersReducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;
    let statsExpanded = statsPanel ? !statsPanel.hasAttribute('hidden') : false;
    let statsAnimating = false;

    const setStatsA11yState = () => {
        if (!statsPanel || !statsToggleButton) {
            return;
        }
        statsPanel.setAttribute('aria-hidden', statsExpanded ? 'false' : 'true');
        statsToggleButton.setAttribute('aria-expanded', statsExpanded ? 'true' : 'false');
        if (statsPanel.id) {
            statsToggleButton.setAttribute('aria-controls', statsPanel.id);
        }
        if (statsToggleLabel) {
            const isMobile = window.innerWidth <= 1024;
            if (isMobile) {
                statsToggleLabel.textContent = 'Statistik';
            } else {
                statsToggleLabel.textContent = 'Statistik';
            }
        }
    };

    const applyCollapsedStyles = () => {
        if (!statsPanel) return;
        statsPanel.classList.add('is-collapsed');
        statsPanel.style.height = '0px';
        statsPanel.style.opacity = '0';
    };

    const finalizeStatsAnimation = (expanded) => {
        if (!statsPanel) return;
        statsPanel.classList.remove('is-animating');
        statsPanel.classList.toggle('is-collapsed', !expanded);
        statsPanel.style.height = expanded ? 'auto' : '';
        statsPanel.style.opacity = '';
        statsPanel.hidden = !expanded;
        statsAnimating = false;
    };

    const animateStatsPanel = () => {
        if (!statsPanel) {
            return;
        }
        if (prefersReducedMotion) {
            finalizeStatsAnimation(statsExpanded);
            return;
        }
        if (statsAnimating) {
            return;
        }
        statsAnimating = true;
        statsPanel.classList.add('is-animating');
        statsPanel.hidden = false;

        const contentHeight = statsViewport?.scrollHeight || statsPanel.scrollHeight || 0;
        const currentHeight = statsPanel.getBoundingClientRect().height || contentHeight;

        if (statsExpanded) {
            applyCollapsedStyles();
            requestAnimationFrame(() => {
                statsPanel.classList.remove('is-collapsed');
                statsPanel.style.height = `${contentHeight}px`;
                statsPanel.style.opacity = '1';
            });
        } else {
            statsPanel.classList.remove('is-collapsed');
            statsPanel.style.height = `${currentHeight}px`;
            statsPanel.style.opacity = '1';
            requestAnimationFrame(() => {
                applyCollapsedStyles();
            });
        }

        let fallbackTimer;
        const finish = (event) => {
            if (event.target !== statsPanel) {
                return;
            }
            statsPanel.removeEventListener('transitionend', finish);
            clearTimeout(fallbackTimer);
            finalizeStatsAnimation(statsExpanded);
        };
        fallbackTimer = window.setTimeout(() => {
            statsPanel.removeEventListener('transitionend', finish);
            finalizeStatsAnimation(statsExpanded);
        }, 480);

        statsPanel.addEventListener('transitionend', finish);
    };

    const setStatsState = (expanded) => {
        statsExpanded = expanded;
        setStatsA11yState();
        animateStatsPanel();
    };

    if (statsPanel && statsPanel.hasAttribute('hidden')) {
        applyCollapsedStyles();
    } else if (statsPanel) {
        statsPanel.style.height = 'auto';
    }
    setStatsA11yState();

    statsToggleButton?.addEventListener('click', () => {
        setStatsState(!statsExpanded);
    });

    // Dots action menus
    const actionMenus = Array.from(document.querySelectorAll('[data-action-menu]'));
    actionMenus.forEach((menu) => {
        menu.addEventListener('toggle', () => {
            if (menu.open) {
                actionMenus.forEach((other) => {
                    if (other !== menu) other.removeAttribute('open');
                });
            }
        });
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-action-menu]')) {
            actionMenus.forEach((menu) => menu.removeAttribute('open'));
        }
    });

    // Handle Search Input Enter Key
    const searchInput = document.getElementById('searchPenduduk');
    const filterForm = document.getElementById('residentFilterForm');
    const hiddenSearchInput = filterForm?.querySelector('input[name="search"]');

    if (searchInput && filterForm && hiddenSearchInput) {
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                hiddenSearchInput.value = searchInput.value;
                filterForm.submit();
            }
        });
    }
});














