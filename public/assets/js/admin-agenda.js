document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const formModal = document.getElementById('agendaFormModal');
    const deleteModal = document.getElementById('agendaDeleteModal');

    const form = document.getElementById('agendaForm');
    const titleEl = document.getElementById('agendaFormTitle');
    const subtitleEl = document.getElementById('agendaFormSubtitle');
    const submitLabel = document.getElementById('agendaFormSubmitLabel');
    const methodField = form?.querySelector('input[name="_method"]');
    const createAction = form?.dataset.createAction || '';

    const priorityInputs = form ? Array.from(form.querySelectorAll('input[name="priority"]')) : [];

    const inputMap = form
        ? {
              title: form.querySelector('input[name="title"]'),
              description: form.querySelector('textarea[name="description"]'),
              descriptionEditor: form.querySelector('[data-content-editor]'),
              dueDate: form.querySelector('input[name="due_date"]'),
          }
        : null;

    const deleteNameEl = document.getElementById('agendaDeleteName');
    const deleteConfirmBtn = document.getElementById('agendaDeleteConfirm');
    let pendingDeleteForm = null;

    const syncBodyModalState = () => {
        const anyOpen = document.querySelector('.dialog-backdrop.is-visible');
        if (anyOpen) {
            body.classList.add('modal-open');
        } else {
            body.classList.remove('modal-open');
        }
    };

    const focusFirstField = (modal) => {
        const focusable = modal?.querySelector(
            'input:not([type="hidden"]), textarea, select, button:not([type="hidden"])'
        );
        focusable?.focus({ preventScroll: true });
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-visible');
        syncBodyModalState();
        requestAnimationFrame(() => focusFirstField(modal));
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        if (modal === deleteModal) {
            pendingDeleteForm = null;
        }
        syncBodyModalState();
    };

    const updatePriorityAppearance = () => {
        priorityInputs.forEach((input) => {
            const wrapper = input.closest('.priority-option');
            if (wrapper) {
                wrapper.classList.toggle('is-selected', input.checked);
            }
        });
    };

    priorityInputs.forEach((input) => {
        input.addEventListener('change', () => {
            updatePriorityAppearance();
        });
        // Also listen for click events
        input.addEventListener('click', () => {
            updatePriorityAppearance();
        });
    });

    const setFormMode = (mode, dataset = {}, options = {}) => {
        if (!form || !inputMap) {
            openModal(formModal);
            return;
        }

        const { preserveValues = false } = options;
        const isEdit = mode === 'edit';
        const desiredPriority = dataset.priority || 'medium';

        form.action = isEdit && dataset.updateUrl ? dataset.updateUrl : createAction;
        if (methodField) {
            methodField.disabled = !isEdit;
            if (isEdit) {
                methodField.value = 'PUT';
            }
        }

        titleEl.textContent = isEdit ? 'Perbarui Agenda Desa' : 'Tambah Agenda Desa';
        submitLabel.textContent = isEdit ? 'Simpan Perubahan' : 'Simpan Agenda';
        
        if (subtitleEl) {
            const subtitleText = isEdit ? form.dataset.editSubtitle : form.dataset.createSubtitle;
            subtitleEl.textContent = subtitleText || '';
            subtitleEl.style.display = subtitleText ? 'block' : 'none';
        }

        if (!preserveValues) {
            if (inputMap.title) {
                inputMap.title.value = dataset.title || '';
            }
            if (inputMap.description) {
                inputMap.description.value = dataset.description || '';
            }
            if (inputMap.descriptionEditor) {
                inputMap.descriptionEditor.innerHTML = dataset.description || '';
            }
            if (inputMap.dueDate) {
                inputMap.dueDate.value = dataset.dueDate || '';
            }
            // Set priority - always ensure one is selected
            priorityInputs.forEach((input) => {
                input.checked = input.value === desiredPriority;
            });
            // Fallback: if no radio button matched, select medium as default
            const anyChecked = priorityInputs.some(input => input.checked);
            if (!anyChecked) {
                const mediumInput = priorityInputs.find(input => input.value === 'medium');
                if (mediumInput) {
                    mediumInput.checked = true;
                }
            }
        }

        if (dataset.id) {
            form.dataset.agendaId = dataset.id;
        } else {
            delete form.dataset.agendaId;
        }

        form.dataset.hasErrors = 'false';
        updatePriorityAppearance();
        openModal(formModal);
    };

    document.querySelectorAll('[data-modal-open="agendaFormModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.formMode || 'create';
            const dataset = {
                id: button.dataset.agendaId || '',
                title: button.dataset.agendaTitle || '',
                description: button.dataset.agendaDescription || '',
                dueDate: button.dataset.agendaDueDate || '',
                priority: button.dataset.agendaPriority || 'medium',
                updateUrl: button.dataset.updateUrl || '',
            };
            setFormMode(mode, dataset);
        });
    });

    const viewModal = document.getElementById('agendaViewModal');
    const viewNameEl = document.getElementById('agendaViewName');
    const viewDescEl = document.getElementById('agendaViewDescription');
    const viewDueDateEl = document.getElementById('agendaViewDueDate');
    const viewCreatedEl = document.getElementById('agendaViewCreated');
    const viewOwnerEl = document.getElementById('agendaViewOwner');
    const viewStatusEl = document.getElementById('agendaViewStatus');
    const viewPriorityEl = document.getElementById('agendaViewPriority');

    document.querySelectorAll('[data-modal-open="agendaViewModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            if (viewNameEl) viewNameEl.textContent = button.dataset.agendaTitle || '-';
            if (viewDescEl) viewDescEl.textContent = button.dataset.agendaDescription || 'Tidak ada deskripsi.';
            if (viewDueDateEl) viewDueDateEl.textContent = button.dataset.agendaDueDate || '-';
            if (viewCreatedEl) viewCreatedEl.textContent = button.dataset.agendaCreated || '-';
            if (viewOwnerEl) viewOwnerEl.textContent = button.dataset.agendaOwner || 'Sistem';
            
            if (viewStatusEl) {
                const status = button.dataset.agendaStatus || 'Terjadwal';
                viewStatusEl.innerHTML = `<i class="fas fa-circle" style="font-size: 8px; margin-right: 6px;"></i><span>${status}</span>`;
                viewStatusEl.className = 'detail-status-chip ' + (status === 'Selesai' ? 'status-active' : 'status-draft');
            }
            
            if (viewPriorityEl) {
                const rawPriority = (button.dataset.agendaPriority || 'medium').toLowerCase();
                const priorityLabels = { 'high': 'Tinggi', 'medium': 'Sedang', 'low': 'Rendah' };
                const label = priorityLabels[rawPriority] || 'Sedang';
                
                viewPriorityEl.textContent = label;
                viewPriorityEl.className = `priority-pill priority-pill--${rawPriority}`;
            }
            
            openModal(viewModal);
        });
    });

    document
        .querySelectorAll('[data-delete-trigger]')
        .forEach((button) =>
            button.addEventListener('click', () => {
                pendingDeleteForm = button.closest('form');
                const agendaTitle = button.dataset.agendaTitle || 'agenda ini';
                if (deleteNameEl) {
                    deleteNameEl.textContent = agendaTitle;
                }
                openModal(deleteModal);
            })
        );

    deleteConfirmBtn?.addEventListener('click', () => {
        if (pendingDeleteForm) {
            pendingDeleteForm.submit();
        }
        closeModal(deleteModal);
    });

    document
        .querySelectorAll('[data-modal-close]')
        .forEach((button) =>
            button.addEventListener('click', () => {
                closeModal(button.closest('.dialog-backdrop'));
            })
        );



    updatePriorityAppearance();

    if (form && form.dataset.hasErrors === 'true') {
        const currentPriority =
            form.querySelector('input[name="priority"]:checked')?.value || 'medium';

        setFormMode(
            'create',
            {
                priority: currentPriority,
            },
            { preserveValues: true }
        );
    }

    // Sync editor content to textarea before form submission
    form?.addEventListener('submit', (event) => {
        const editorArea = document.querySelector('[data-content-editor]');
        const textarea = document.querySelector('[data-content-textarea]');
        if (editorArea && textarea) {
            textarea.value = editorArea.innerHTML.trim();
        }
        
        // Debug: Log form data before submission
        const formData = new FormData(form);
        console.log('=== Form Submission Debug ===');
        console.log('Form Action:', form.action);
        console.log('Form Method:', form.method);
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }
        console.log('===========================');
        
        // Let the form submit normally (no preventDefault)
    });
});
