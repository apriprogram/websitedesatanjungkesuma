@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editorArea = document.querySelector('[data-content-editor]');
            const textarea = document.querySelector('[data-content-textarea]');
            if (!editorArea || !textarea) {
                return;
            }

            const toolbarButtons = document.querySelectorAll('[data-editor-command]');
            const dropdowns = document.querySelectorAll('[data-editor-dropdown]');
            const caseButtons = document.querySelectorAll('[data-change-case]');
            const lineSpacingButtons = document.querySelectorAll('[data-line-spacing]');
            const paragraphSpacingButtons = document.querySelectorAll('[data-paragraph-spacing]');
            const modal = document.querySelector('[data-editor-input-modal]');
            const modalTitle = modal?.querySelector('[data-editor-input-modal-title]');
            const modalDescription = modal?.querySelector('[data-editor-input-modal-description]');
            const modalFields = modal?.querySelector('[data-editor-input-modal-fields]');
            const modalForm = modal?.querySelector('[data-editor-input-modal-form]');
            const modalCloseTargets = modal?.querySelectorAll('[data-editor-input-modal-close]');
            const modalCancelButton = modal?.querySelector('[data-editor-input-modal-cancel]');

            editorArea.dataset.lineSpacing = editorArea.dataset.lineSpacing || 'normal';
            editorArea.dataset.paragraphSpacing = editorArea.dataset.paragraphSpacing || 'standard';

            const syncContent = () => {
                textarea.value = editorArea.innerHTML.trim();
            };

            const modalTemplates = {
                createLink: {
                    title: 'Masukkan tautan',
                    description: 'Tambahkan URL yang disisipkan ke teks yang dipilih.',
                    render: () => `
                        <label class="news-editor-input-modal__label">URL</label>
                        <input class="news-editor-input-modal__input" type="url" name="value" placeholder="https://contoh.com" required>
                    `,
                },
                insertImage: {
                    title: 'Masukkan gambar',
                    description: 'Tempelkan tautan gambar yang akan ditampilkan pada isi berita.',
                    render: () => `
                        <label class="news-editor-input-modal__label">URL gambar</label>
                        <input class="news-editor-input-modal__input" type="url" name="value" placeholder="https://contoh.com/foto.jpg" required>
                    `,
                },
                hiliteColor: {
                    title: 'Warna shading',
                    description: 'Pilih warna latar untuk teks yang disorot.',
                    render: options => {
                        const colorValue = options?.color || '#dcdcfb';
                        const textValue = options?.text || colorValue;
                        return `
                            <label class="news-editor-input-modal__label">Warna</label>
                            <div class="news-editor-input-modal__color-row">
                                <input class="news-editor-input-modal__input" type="text" name="value" value="${textValue}" placeholder="rgba(99, 102, 241, 0.15)" required>
                                <input type="color" name="valueColor" value="${colorValue}">
                            </div>
                        `;
                    },
                },
                insertTable: {
                    title: 'Masukkan tabel',
                    description: 'Atur jumlah baris dan kolom tabel yang akan dimasukkan.',
                    render: () => `
                        <label class="news-editor-input-modal__label">Baris</label>
                        <input class="news-editor-input-modal__input" type="number" name="rows" min="1" value="3" required>
                        <label class="news-editor-input-modal__label">Kolom</label>
                        <input class="news-editor-input-modal__input" type="number" name="cols" min="1" value="2" required>
                    `,
                },
            };

            let pendingCommand = null;

            const openModal = (command, options = {}) => {
                if (!modal || !modalTitle || !modalDescription || !modalFields) {
                    return;
                }
                const template = modalTemplates[command];
                if (!template) {
                    return;
                }
                pendingCommand = command;
                modalTitle.textContent = template.title;
                modalDescription.textContent = template.description;
                modalFields.innerHTML = template.render(options);
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
                setTimeout(() => {
                    const firstInput = modalFields.querySelector('input');
                    firstInput?.focus();
                }, 10);

                const colorInput = modalFields.querySelector('input[name="valueColor"]');
                const textInput = modalFields.querySelector('input[name="value"]');
                colorInput?.addEventListener('input', () => {
                    if (textInput) {
                        textInput.value = colorInput.value;
                    }
                });
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }
                modal.classList.remove('is-visible');
                modal.setAttribute('aria-hidden', 'true');
                pendingCommand = null;
                if (modalFields) {
                    modalFields.innerHTML = '';
                }
            };

            modalCloseTargets?.forEach(target => {
                target.addEventListener('click', () => closeModal());
            });
            modalCancelButton?.addEventListener('click', () => closeModal());
            modal?.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            const applyTextTransform = transform => {
                if (!transform) {
                    return;
                }
                const selection = window.getSelection();
                if (!selection || !selection.rangeCount || selection.isCollapsed) {
                    return;
                }
                const range = selection.getRangeAt(0);
                const fragment = range.extractContents();
                const wrapper = document.createElement('span');
                wrapper.style.textTransform = transform;
                wrapper.style.display = 'inline-block';
                wrapper.appendChild(fragment);
                range.insertNode(wrapper);
                selection.removeAllRanges();
                const newRange = document.createRange();
                newRange.selectNodeContents(wrapper);
                selection.addRange(newRange);
                editorArea.focus();
                syncContent();
            };

            const closeDropdown = dropdown => dropdown && dropdown.classList.remove('is-open');

            toolbarButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const command = button.dataset.editorCommand;
                    if (Object.prototype.hasOwnProperty.call(modalTemplates, command)) {
                        const options = {
                            color: button.dataset.editorValue || '#dcdcfb',
                            text: button.dataset.editorValue || '',
                        };
                        openModal(command, options);
                        return;
                    }
                    const value = button.dataset.commandValue || null;
                    document.execCommand(command, false, value);
                    editorArea.focus();
                    syncContent();
                });
            });

            modalForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!pendingCommand || !modalForm) {
                    return;
                }
                const formData = new FormData(modalForm);
                let applied = false;

                if (pendingCommand === 'createLink' || pendingCommand === 'insertImage') {
                    const url = (formData.get('value') || '').trim();
                    if (url) {
                        document.execCommand(pendingCommand, false, url);
                        applied = true;
                    }
                } else if (pendingCommand === 'hiliteColor') {
                    const manual = (formData.get('value') || '').trim();
                    const picker = (formData.get('valueColor') || '').trim();
                    const color = manual || picker;
                    if (color) {
                        document.execCommand('hiliteColor', false, color);
                        applied = true;
                    }
                } else if (pendingCommand === 'insertTable') {
                    const rows = parseInt(formData.get('rows'), 10);
                    const cols = parseInt(formData.get('cols'), 10);
                    if (rows > 0 && cols > 0) {
                        let tableHtml = '<table class="news-editor-table">';
                        for (let r = 0; r < rows; r++) {
                            tableHtml += '<tr>';
                            for (let c = 0; c < cols; c++) {
                                tableHtml += r === 0 ? '<th>&nbsp;</th>' : '<td>&nbsp;</td>';
                            }
                            tableHtml += '</tr>';
                        }
                        tableHtml += '</table><p></p>';
                        document.execCommand('insertHTML', false, tableHtml);
                        applied = true;
                    }
                }

                if (applied) {
                    editorArea.focus();
                    syncContent();
                }
                closeModal();
            });

            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('[data-dropdown-toggle]');
                const menu = dropdown.querySelector('[data-dropdown-open]');
                const label = dropdown.querySelector('[data-dropdown-label]');

                toggle.addEventListener('click', event => {
                    event.stopPropagation();
                    dropdown.classList.toggle('is-open');
                });

                menu.addEventListener('click', event => {
                    const option = event.target.closest('button[data-editor-command]');
                    if (!option) {
                        return;
                    }
                    event.preventDefault();
                    const command = option.dataset.editorCommand;
                    const value = option.dataset.editorValue || null;
                    document.execCommand(command, false, value);
                    if (label) {
                        label.textContent = option.textContent.trim();
                    }
                    editorArea.focus();
                    syncContent();
                });
            });

            caseButtons.forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    const dropdown = button.closest('[data-editor-dropdown]');
                    const label = dropdown?.querySelector('[data-dropdown-label]');
                    applyTextTransform(button.dataset.changeCase);
                    if (label) {
                        label.textContent = button.textContent.trim();
                    }
                    closeDropdown(dropdown);
                });
            });

            lineSpacingButtons.forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    const dropdown = button.closest('[data-editor-dropdown]');
                    const label = dropdown?.querySelector('[data-dropdown-label]');
                    editorArea.dataset.lineSpacing = button.dataset.lineSpacing || 'normal';
                    if (label) {
                        label.textContent = button.textContent.trim();
                    }
                    closeDropdown(dropdown);
                    editorArea.focus();
                    syncContent();
                });
            });

            paragraphSpacingButtons.forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    const dropdown = button.closest('[data-editor-dropdown]');
                    const label = dropdown?.querySelector('[data-dropdown-label]');
                    editorArea.dataset.paragraphSpacing = button.dataset.paragraphSpacing || 'standard';
                    if (label) {
                        label.textContent = button.textContent.trim();
                    }
                    closeDropdown(dropdown);
                    editorArea.focus();
                    syncContent();
                });
            });

            document.addEventListener('click', () => {
                dropdowns.forEach(dropdown => dropdown.classList.remove('is-open'));
            });

            document.querySelectorAll('select[data-editor-command]').forEach(select => {
                select.addEventListener('change', () => {
                    const command = select.dataset.editorCommand;
                    const value = select.value || select.dataset.commandDefault || '';
                    if (!value) {
                        return;
                    }
                    document.execCommand(command, false, value);
                    select.value = select.dataset.commandDefault || '';
                    editorArea.focus();
                    syncContent();
                });
            });

            editorArea.addEventListener('input', syncContent);

            const form = editorArea.closest('form');
            if (form) {
                form.addEventListener('submit', syncContent);
            }

            syncContent();
        });
    </script>
@endpush
