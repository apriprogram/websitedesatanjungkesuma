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
                    title: 'Masukkan Gambar',
                    description: 'Unggah file gambar dari komputer Anda.',
                    render: () => `
                        <div class="file-drop-zone" id="contentDropZone" style="padding: 2.5rem 1rem; margin-bottom: 2rem; border: 2px dashed #cbd5e1; border-radius: 16px; text-align: center; background: #f8fafc; transition: all 0.2s ease;">
                            <div class="drop-icon" style="font-size: 3rem; color: #94a3b8; margin-bottom: 0.75rem;">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4 style="font-size: 1.1rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 600;">Pilih file atau tarik & lepas ke sini.</h4>
                            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem;">jpeg, png, gif - Maks. 10MB</p>
                            <label class="news-btn news-btn--secondary" style="cursor: pointer; background: #fff; border: 1px solid #cbd5e1; padding: 0.6rem 1.2rem; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 0.9rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                Telusuri file
                                <input id="contentImageFile" type="file" name="imageFile" accept="image/*" hidden>
                            </label>
                            <div id="contentImagePreview" style="margin-top: 1.25rem; display: none; font-size: 0.95rem; font-weight: 600; color: #3b82f6; background: #eff6ff; padding: 8px 12px; border-radius: 8px; border: 1px solid #dbeafe;"></div>
                        </div>

                        <div class="news-editor-modal-section">
                            <label class="news-editor-input-modal__label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span class="modal-section-title">Lebar Tampilan Gambar</span>
                                <span id="widthValueDisplay" style="font-weight: 700; color: #3b82f6; background: #fff; border: 1px solid #dbeafe; padding: 2px 10px; border-radius: 6px; font-size: 0.9rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">100%</span>
                            </label>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <i class="fas fa-image" style="font-size: 0.9rem; color: #94a3b8;"></i>
                                <input type="range" name="imageWidth" min="10" max="100" value="100" step="5" id="imageWidthSlider"
                                       style="flex: 1; height: 6px; border-radius: 3px; background: #cbd5e1; appearance: none; cursor: pointer; outline: none;">
                                <i class="fas fa-image" style="font-size: 1.5rem; color: #475569;"></i>
                            </div>
                            <p class="modal-section-desc">Geser untuk mengatur persentase lebar gambar di dalam konten.</p>
                        </div>
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
                    title: 'Masukkan Tabel',
                    description: 'Atur jumlah baris dan kolom tabel yang akan dimasukkan.',
                    render: () => `
                        <div class="news-editor-modal-section">
                            <label class="news-editor-input-modal__label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span class="modal-section-title">Pilih Ukuran Grid</span>
                                <span id="gridValueDisplay" style="font-weight: 700; color: #3b82f6; background: #fff; border: 1px solid #dbeafe; padding: 2px 10px; border-radius: 6px; font-size: 0.9rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">1 x 1</span>
                            </label>
                            <div id="tableGridPicker" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 4px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 8px; cursor: pointer; justify-content: center;">
                                ${Array.from({length: 100}).map((_, i) => `
                                    <div class="grid-square" data-row="${Math.floor(i/10)+1}" data-col="${(i%10)+1}" 
                                         style="aspect-ratio: 1; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; transition: all 0.1s ease;">
                                    </div>
                                `).join('')}
                            </div>
                            <p class="modal-section-desc">Geser kursor untuk memilih ukuran tabel, lalu klik untuk mengunci.</p>
                            <input type="hidden" name="rows" value="1">
                            <input type="hidden" name="cols" value="1">
                        </div>
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

                if (command === 'insertImage') {
                    const contentImageFile = modalFields.querySelector('#contentImageFile');
                    const contentImagePreview = modalFields.querySelector('#contentImagePreview');
                    const dropZone = modalFields.querySelector('#contentDropZone');
                    
                    if (contentImageFile && contentImagePreview) {
                        contentImageFile.addEventListener('change', (e) => {
                            if (e.target.files && e.target.files[0]) {
                                contentImagePreview.textContent = 'File terpilih: ' + e.target.files[0].name;
                                contentImagePreview.style.display = 'block';
                            }
                        });
                    }

                    if (dropZone && contentImageFile) {
                        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                            dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
                        });
                        ['dragenter', 'dragover'].forEach(eventName => {
                            dropZone.addEventListener(eventName, () => {
                                dropZone.style.borderColor = '#3b82f6';
                                dropZone.style.background = '#eff6ff';
                            }, false);
                        });
                        ['dragleave', 'drop'].forEach(eventName => {
                            dropZone.addEventListener(eventName, () => {
                                dropZone.style.borderColor = '#cbd5e1';
                                dropZone.style.background = '#f8fafc';
                            }, false);
                        });
                        dropZone.addEventListener('drop', (e) => {
                            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                                contentImageFile.files = e.dataTransfer.files;
                                contentImagePreview.textContent = 'File terpilih: ' + e.dataTransfer.files[0].name;
                                contentImagePreview.style.display = 'block';
                            }
                        });
                    }

                    const widthSlider = modalFields.querySelector('#imageWidthSlider');
                    const widthDisplay = modalFields.querySelector('#widthValueDisplay');
                    if (widthSlider && widthDisplay) {
                        widthSlider.addEventListener('input', () => {
                            widthDisplay.textContent = widthSlider.value + '%';
                        });
                    }
                }
                
                if (command === 'insertTable') {
                    const gridPicker = modalFields.querySelector('#tableGridPicker');
                    const gridSquares = modalFields.querySelectorAll('.grid-square');
                    const gridDisplay = modalFields.querySelector('#gridValueDisplay');
                    const rowsInput = modalFields.querySelector('input[name="rows"]');
                    const colsInput = modalFields.querySelector('input[name="cols"]');

                    if (gridPicker) {
                        gridSquares.forEach(sq => {
                            sq.addEventListener('mouseenter', () => {
                                const r = parseInt(sq.dataset.row);
                                const c = parseInt(sq.dataset.col);
                                gridDisplay.textContent = `${r} x ${c}`;
                                gridSquares.forEach(s => {
                                    const sr = parseInt(s.dataset.row);
                                    const sc = parseInt(s.dataset.col);
                                    if (sr <= r && sc <= c) {
                                        s.style.background = '#3b82f6';
                                        s.style.borderColor = '#2563eb';
                                    } else {
                                        s.style.background = '#fff';
                                        s.style.borderColor = '#cbd5e1';
                                    }
                                });
                            });
                            sq.addEventListener('click', () => {
                                rowsInput.value = sq.dataset.row;
                                colsInput.value = sq.dataset.col;
                                gridPicker.style.borderColor = '#10b981';
                                setTimeout(() => {
                                    modalForm.dispatchEvent(new Event('submit'));
                                }, 100);
                            });
                        });
                    }
                }
            };

            let savedRange = null;
            const saveCursorPosition = () => {
                const sel = window.getSelection();
                if (sel && sel.rangeCount > 0) {
                    savedRange = sel.getRangeAt(0).cloneRange();
                }
            };

            const insertHtmlAtCursor = (html) => {
                editorArea.focus();
                const sel = window.getSelection();
                
                let range = null;
                if (savedRange && editorArea.contains(savedRange.commonAncestorContainer)) {
                    range = savedRange;
                } else if (sel && sel.rangeCount > 0 && editorArea.contains(sel.getRangeAt(0).commonAncestorContainer)) {
                    range = sel.getRangeAt(0);
                }

                const el = document.createElement('div');
                el.innerHTML = html;
                const fragment = document.createDocumentFragment();
                let lastNode = null;
                while (el.firstChild) {
                    lastNode = el.firstChild;
                    fragment.appendChild(lastNode);
                }

                if (range) {
                    sel.removeAllRanges();
                    sel.addRange(range);
                    range.deleteContents();
                    range.insertNode(fragment);
                    
                    if (lastNode) {
                        const newRange = document.createRange();
                        newRange.setStartAfter(lastNode);
                        newRange.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(newRange);
                    }
                } else {
                    editorArea.appendChild(fragment);
                }
                syncContent();
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
                        saveCursorPosition();
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

                if (pendingCommand === 'createLink') {
                    const url = (formData.get('value') || '').trim();
                    if (url) {
                        document.execCommand(pendingCommand, false, url);
                        applied = true;
                    }
                } else if (pendingCommand === 'insertImage') {
                    const fileInput = modalForm.querySelector('input[name="imageFile"]');
                    const widthInput = modalForm.querySelector('input[name="imageWidth"]');
                    
                    const file = fileInput && fileInput.files[0];
                    const width = widthInput && widthInput.value ? widthInput.value + '%' : '100%';

                    const buildImgHtml = (src) => {
                        return `<p><img src="${src}" style="width:${width}; max-width:100%; height:auto; display:block; margin:10px auto; border-radius:4px;" draggable="true"></p>`;
                    };

                    if (file) {
                        const formDataUpload = new FormData();
                        formDataUpload.append('image', file);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                        closeModal();

                        fetch('/admin/media/editor-upload', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formDataUpload
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.url) {
                                insertHtmlAtCursor(buildImgHtml(data.url));
                            } else {
                                alert('Gagal mengunggah gambar.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Terjadi kesalahan saat mengunggah.');
                        });
                        return;
                    } else {
                        alert('Silakan pilih file gambar terlebih dahulu.');
                        return;
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
                        let tableHtml = `<div class="news-editor-table-wrapper" contenteditable="false">
                            <table class="news-editor-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                                <tbody>`;
                        for (let r = 0; r < rows; r++) {
                            tableHtml += '<tr>';
                            for (let c = 0; c < cols; c++) {
                                tableHtml += `<td contenteditable="true" style="border: 1px solid #cbd5e1; padding: 0; position: relative; min-width: 50px;">
                                    <div class="table-cell-resizable" style="resize: both; overflow: auto; padding: 12px; min-width: 50px; min-height: 20px;">&nbsp;</div>
                                </td>`;
                            }
                            tableHtml += '</tr>';
                        }
                        tableHtml += `</tbody>
                            </table>
                        </div><p></p>`;
                        insertHtmlAtCursor(tableHtml);
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

            (function initImageResizer() {
                const overlay = document.createElement('div');
                overlay.id = 'imgResizeOverlay';
                overlay.style.cssText = `
                    position: fixed; display: none; z-index: 99999; pointer-events: none;
                    box-sizing: border-box;
                `;
                overlay.innerHTML = `
                    <div id="imgAlignBar" style="
                        position: absolute; top: -50px; left: 50%; transform: translateX(-50%);
                        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 5px 8px;
                        display: flex; gap: 2px; align-items: center; pointer-events: all;
                        box-shadow: 0 4px 16px rgba(0,0,0,0.12); white-space: nowrap;
                    ">
                        <button type="button" data-align="left"   title="Rata Kiri"   style="background:none;border:none;color:#64748b;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:14px;display:grid;place-items:center;"><i class='fas fa-align-left'></i></button>
                        <button type="button" data-align="center" title="Rata Tengah" style="background:none;border:none;color:#64748b;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:14px;display:grid;place-items:center;"><i class='fas fa-align-center'></i></button>
                        <button type="button" data-align="right"  title="Rata Kanan"  style="background:none;border:none;color:#64748b;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:14px;display:grid;place-items:center;"><i class='fas fa-align-right'></i></button>
                        <span style="width:1px;height:20px;background:#e2e8f0;margin:0 4px;display:inline-block;"></span>
                        <button type="button" id="imgMoveHint" title="Tarik untuk memindahkan" style="background:none;border:none;color:#94a3b8;width:30px;height:30px;border-radius:6px;cursor:grab;font-size:14px;display:grid;place-items:center;"><i class='fas fa-arrows-alt'></i></button>
                        <span style="width:1px;height:20px;background:#e2e8f0;margin:0 4px;display:inline-block;"></span>
                        <button type="button" id="imgDeleteBtn" title="Hapus gambar" style="background:none;border:none;color:#ef4444;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:14px;display:grid;place-items:center;"><i class='fas fa-trash'></i></button>
                    </div>
                    <div style="position:absolute;inset:-2px;border:2px solid #3b82f6;border-radius:2px;pointer-events:none;"></div>
                    <div class="rsz-handle" data-dir="nw" style="top:-5px;left:-5px;cursor:nw-resize;"></div>
                    <div class="rsz-handle" data-dir="n"  style="top:-5px;left:50%;transform:translateX(-50%);cursor:n-resize;"></div>
                    <div class="rsz-handle" data-dir="ne" style="top:-5px;right:-5px;cursor:ne-resize;"></div>
                    <div class="rsz-handle" data-dir="e"  style="top:50%;right:-5px;transform:translateY(-50%);cursor:e-resize;"></div>
                    <div class="rsz-handle" data-dir="se" style="bottom:-5px;right:-5px;cursor:se-resize;"></div>
                    <div class="rsz-handle" data-dir="s"  style="bottom:-5px;left:50%;transform:translateX(-50%);cursor:s-resize;"></div>
                    <div class="rsz-handle" data-dir="sw" style="bottom:-5px;left:-5px;cursor:sw-resize;"></div>
                    <div class="rsz-handle" data-dir="w"  style="top:50%;left:-5px;transform:translateY(-50%);cursor:w-resize;"></div>
                `;
                document.body.appendChild(overlay);

                overlay.querySelectorAll('.rsz-handle').forEach(h => {
                    h.style.cssText += `
                        position:absolute; width:10px; height:10px; background:#3b82f6;
                        border:2px solid #fff; border-radius:2px; pointer-events:all;
                        box-shadow:0 1px 4px rgba(0,0,0,0.4);
                    `;
                });

                let selectedImg = null;
                let isDragging = false;
                let dragDir = '';
                let startX, startY, startW, startH, aspectRatio;

                const positionOverlay = () => {
                    if (!selectedImg) return;
                    const r = selectedImg.getBoundingClientRect();
                    overlay.style.cssText += `
                        display: block;
                        top: ${r.top}px; left: ${r.left}px;
                        width: ${r.width}px; height: ${r.height}px;
                    `;
                };

                const hideOverlay = () => {
                    overlay.style.display = 'none';
                    selectedImg = null;
                };

                editorArea.addEventListener('click', (e) => {
                    if (e.target.tagName === 'IMG') {
                        e.preventDefault();
                        selectedImg = e.target;
                        selectedImg.setAttribute('draggable', 'true');
                        aspectRatio = selectedImg.naturalWidth / selectedImg.naturalHeight;
                        positionOverlay();
                    } else {
                        hideOverlay();
                    }
                });

                overlay.querySelectorAll('[data-align]').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!selectedImg) return;
                        const align = btn.dataset.align;
                        const parent = selectedImg.closest('p') || selectedImg.parentElement;
                        if (align === 'left') {
                            selectedImg.style.margin = '10px auto 10px 0';
                            selectedImg.style.display = 'block';
                            if (parent) parent.style.textAlign = 'left';
                        } else if (align === 'center') {
                            selectedImg.style.margin = '10px auto';
                            selectedImg.style.display = 'block';
                            if (parent) parent.style.textAlign = 'center';
                        } else if (align === 'right') {
                            selectedImg.style.margin = '10px 0 10px auto';
                            selectedImg.style.display = 'block';
                            if (parent) parent.style.textAlign = 'right';
                        }
                        syncContent();
                        setTimeout(positionOverlay, 50);
                    });
                });

                const deleteBtn = overlay.querySelector('#imgDeleteBtn');
                deleteBtn?.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!selectedImg) return;
                    const parent = selectedImg.closest('p');
                    if (parent && parent.childElementCount === 1) {
                        parent.remove();
                    } else {
                        selectedImg.remove();
                    }
                    hideOverlay();
                    syncContent();
                });

                overlay.querySelectorAll('.rsz-handle').forEach(handle => {
                    handle.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!selectedImg) return;
                        isDragging = true;
                        dragDir = handle.dataset.dir;
                        startX = e.clientX;
                        startY = e.clientY;
                        startW = selectedImg.getBoundingClientRect().width;
                        startH = selectedImg.getBoundingClientRect().height;
                        aspectRatio = startW / startH;
                        document.body.style.userSelect = 'none';
                        overlay.style.pointerEvents = 'all';
                    });
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isDragging || !selectedImg) return;
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    let newW = startW;

                    if (dragDir.includes('e')) newW = Math.max(60, startW + dx);
                    else if (dragDir.includes('w')) newW = Math.max(60, startW - dx);
                    else if (dragDir === 'n' || dragDir === 's') {
                        const newH = Math.max(40, startH + (dragDir === 's' ? dy : -dy));
                        newW = newH * aspectRatio;
                    }

                    if (dragDir === 'nw' || dragDir === 'ne' || dragDir === 'sw' || dragDir === 'se') {
                        newW = Math.max(60, newW);
                    }

                    const editorWidth = editorArea.getBoundingClientRect().width;
                    const pct = Math.round((newW / editorWidth) * 100);
                    selectedImg.style.width = Math.max(5, Math.min(100, pct)) + '%';
                    selectedImg.style.height = 'auto';

                    positionOverlay();
                });

                document.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        document.body.style.userSelect = '';
                        overlay.style.pointerEvents = 'none';
                        syncContent();
                        positionOverlay();
                    }
                });

                editorArea.addEventListener('scroll', positionOverlay, true);
                window.addEventListener('scroll', positionOverlay, true);
                window.addEventListener('resize', positionOverlay);

                const dropIndicator = document.createElement('div');
                dropIndicator.id = 'imgDropIndicator';
                dropIndicator.style.cssText = `
                    display: none; position: fixed; pointer-events: none; z-index: 99998;
                    width: 3px; border-radius: 3px; background: #3b82f6;
                    box-shadow: 0 0 0 2px rgba(59,130,246,0.25);
                    transition: top 0.05s, left 0.05s, height 0.05s;
                `;
                dropIndicator.innerHTML = `<div style="
                    width:10px; height:10px; border-radius:50%; background:#3b82f6;
                    position:absolute; top:-4px; left:-3.5px;
                    box-shadow:0 0 0 3px rgba(59,130,246,0.3);
                    animation: dropPulse 0.8s ease-in-out infinite alternate;
                "></div>`;
                document.body.appendChild(dropIndicator);

                if (!document.getElementById('dropIndicatorStyle')) {
                    const styleEl = document.createElement('style');
                    styleEl.id = 'dropIndicatorStyle';
                    styleEl.textContent = `
                        @keyframes dropPulse {
                            from { box-shadow: 0 0 0 2px rgba(59,130,246,0.4); }
                            to   { box-shadow: 0 0 0 6px rgba(59,130,246,0.0); }
                        }
                    `;
                    document.head.appendChild(styleEl);
                }

                let dropRange = null;
                const hideDropIndicator = () => {
                    dropIndicator.style.display = 'none';
                    dropRange = null;
                };

                const showDropIndicator = (clientX, clientY) => {
                    let range = null;
                    if (document.caretRangeFromPoint) {
                        range = document.caretRangeFromPoint(clientX, clientY);
                    } else if (document.caretPositionFromPoint) {
                        const pos = document.caretPositionFromPoint(clientX, clientY);
                        if (pos) {
                            range = document.createRange();
                            range.setStart(pos.offsetNode, pos.offset);
                            range.collapse(true);
                        }
                    }
                    if (!range || !editorArea.contains(range.commonAncestorContainer)) {
                        hideDropIndicator();
                        return;
                    }
                    dropRange = range;
                    const rects = range.getClientRects();
                    if (!rects.length) { hideDropIndicator(); return; }
                    const r = rects[0];
                    const lineH = Math.max(20, r.height || 20);

                    dropIndicator.style.display = 'block';
                    dropIndicator.style.top    = `${r.top}px`;
                    dropIndicator.style.left   = `${r.left}px`;
                    dropIndicator.style.height = `${lineH}px`;
                };

                let draggedImg = null;
                editorArea.addEventListener('dragstart', (e) => {
                    if (e.target.tagName === 'IMG') {
                        draggedImg = e.target;
                        hideOverlay();
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/html', e.target.outerHTML);
                        draggedImg.style.outline = '2px dashed #3b82f6';
                    }
                });
                editorArea.addEventListener('dragend', (e) => {
                    if (draggedImg) {
                        draggedImg.style.outline = '';
                        draggedImg = null;
                    }
                    hideDropIndicator();
                    syncContent();
                });
                editorArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    if (e.dataTransfer.types.includes('Files') || draggedImg) {
                        e.dataTransfer.dropEffect = draggedImg ? 'move' : 'copy';
                        showDropIndicator(e.clientX, e.clientY);
                    }
                });
                editorArea.addEventListener('dragleave', (e) => {
                    if (!editorArea.contains(e.relatedTarget)) {
                        hideDropIndicator();
                    }
                });
                editorArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    hideDropIndicator();

                    const range = dropRange || (() => {
                        if (document.caretRangeFromPoint) return document.caretRangeFromPoint(e.clientX, e.clientY);
                        return null;
                    })();

                    if (draggedImg) {
                        if (range && editorArea.contains(range.commonAncestorContainer)) {
                            const imgClone = draggedImg.cloneNode(true);
                            draggedImg.remove();
                            range.insertNode(imgClone);
                        } else {
                            draggedImg.style.outline = '';
                        }
                        draggedImg = null;
                        syncContent();
                        return;
                    }

                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        const file = e.dataTransfer.files[0];
                        if (!file.type.startsWith('image/')) return;

                        const formDataUpload = new FormData();
                        formDataUpload.append('image', file);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                        let placeholderNode = null;
                        if (range && editorArea.contains(range.commonAncestorContainer)) {
                            placeholderNode = document.createTextNode(' [Mengunggah gambar...] ');
                            range.insertNode(placeholderNode);
                        }

                        fetch('/admin/media/editor-upload', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formDataUpload
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.url) {
                                const imgHtml = `<p><img src="${data.url}" style="width:100%; max-width:100%; height:auto; display:block; margin:10px auto; border-radius:4px;" draggable="true"></p>`;
                                
                                if (placeholderNode) {
                                    const el = document.createElement('div');
                                    el.innerHTML = imgHtml;
                                    const frag = document.createDocumentFragment();
                                    while (el.firstChild) frag.appendChild(el.firstChild);
                                    
                                    const newRange = document.createRange();
                                    newRange.setStartBefore(placeholderNode);
                                    newRange.collapse(true);
                                    newRange.insertNode(frag);
                                    placeholderNode.remove();
                                } else {
                                    insertHtmlAtCursor(imgHtml);
                                }
                                syncContent();
                            } else {
                                if (placeholderNode) placeholderNode.remove();
                                alert('Gagal mengunggah gambar.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            if (placeholderNode) placeholderNode.remove();
                            alert('Terjadi kesalahan saat mengunggah.');
                        });
                    }
                });

                document.addEventListener('mousedown', (e) => {
                    if (!editorArea.contains(e.target) && !overlay.contains(e.target)) {
                        hideOverlay();
                    }
                });
            })();


            let tableToolbar = document.getElementById('newsEditorTableToolbar');
            if (!tableToolbar) {
                tableToolbar = document.createElement('div');
                tableToolbar.id = 'newsEditorTableToolbar';
                tableToolbar.className = 'news-editor-table-toolbar';
                tableToolbar.innerHTML = `
                    <div class="news-editor-table-toolbar-inner">
                        <button type="button" data-action="addRowAbove" title="Tambah Baris Atas"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" data-action="addRowBelow" title="Tambah Baris Bawah"><i class="fas fa-arrow-down"></i></button>
                        <hr style="height: 20px; border: none; border-left: 1px solid rgba(255,255,255,0.2); margin: 0 4px;">
                        <button type="button" data-action="addColLeft" title="Tambah Kolom Kiri"><i class="fas fa-arrow-left"></i></button>
                        <button type="button" data-action="addColRight" title="Tambah Kolom Kanan"><i class="fas fa-arrow-right"></i></button>
                        <hr style="height: 20px; border: none; border-left: 1px solid rgba(255,255,255,0.2); margin: 0 4px;">
                        <button type="button" data-action="deleteRow" title="Hapus Baris" class="danger"><i class="fas fa-minus-square"></i></button>
                        <button type="button" data-action="deleteCol" title="Hapus Kolom" class="danger"><i class="fas fa-columns"></i></button>
                        <button type="button" data-action="deleteTable" title="Hapus Tabel" class="danger"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                editorArea.parentElement.insertBefore(tableToolbar, editorArea);
            }

            const style = document.createElement('style');
            style.innerHTML = `
                .news-editor-table-toolbar {
                    position: sticky;
                    top: 20px;
                    z-index: 10001;
                    height: 0;
                    width: 100%;
                    display: none;
                    justify-content: center;
                    pointer-events: none;
                    overflow: visible;
                }
                .news-editor-table-toolbar-inner {
                    position: absolute;
                    top: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #1e293b;
                    border-radius: 14px;
                    padding: 8px 16px;
                    display: flex;
                    gap: 8px;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    align-items: center;
                    pointer-events: auto;
                    width: fit-content;
                }
                .news-editor-table-toolbar-inner button {
                    background: transparent;
                    border: none;
                    color: #f1f5f9;
                    width: 32px;
                    height: 32px;
                    display: grid;
                    place-items: center;
                    cursor: pointer;
                    border-radius: 8px;
                    transition: all 0.2s;
                    font-size: 0.9rem;
                }
                .news-editor-table-toolbar-inner button:hover {
                    background: rgba(255, 255, 255, 0.1);
                    color: #60a5fa;
                }
                .news-editor-table-toolbar-inner button.danger {
                    color: #f87171;
                }
                .news-editor-table-toolbar-inner button.danger:hover {
                    background: rgba(239, 68, 68, 0.15);
                    color: #ffffff;
                }
                .news-editor-table-toolbar-inner hr {
                    height: 20px;
                    width: 1px;
                    background: rgba(255, 255, 255, 0.15);
                    border: none;
                    margin: 0 4px;
                }
                .news-editor-table-wrapper {
                    margin: 1.5rem 0;
                    position: relative;
                }
            `;
            document.head.appendChild(style);

            let activeCell = null;
            editorArea.addEventListener('contextmenu', (e) => {
                const cell = e.target.closest('td, th');
                if (cell) {
                    e.preventDefault();
                    activeCell = cell;
                    tableToolbar.style.display = 'flex';
                } else {
                    tableToolbar.style.display = 'none';
                }
            });
            
            document.addEventListener('click', (e) => {
                if (!tableToolbar.contains(e.target)) {
                    tableToolbar.style.display = 'none';
                }
            });

            tableToolbar.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const action = e.target.closest('button')?.dataset.action;
                if (!action || !activeCell) return;

                const row = activeCell.parentElement;
                const table = row.parentElement.closest('table');
                const colIndex = activeCell.cellIndex;
                const rowIndex = row.rowIndex;

                switch (action) {
                    case 'addRowAbove':
                    case 'addRowBelow':
                        const newRow = table.insertRow(action === 'addRowAbove' ? rowIndex : rowIndex + 1);
                        for (let i = 0; i < row.cells.length; i++) {
                            const newCell = newRow.insertCell(i);
                            newCell.innerHTML = `<div class="table-cell-resizable" style="resize: both; overflow: auto; padding: 12px; min-width: 50px; min-height: 20px;">&nbsp;</div>`;
                            newCell.style.padding = '0';
                            newCell.contentEditable = true;
                            newCell.style.cssText = activeCell.style.cssText;
                        }
                        break;
                    case 'addColLeft':
                    case 'addColRight':
                        const index = action === 'addColLeft' ? colIndex : colIndex + 1;
                        Array.from(table.rows).forEach(r => {
                            const newCell = r.insertCell(index);
                            newCell.innerHTML = `<div class="table-cell-resizable" style="resize: both; overflow: auto; padding: 12px; min-width: 50px; min-height: 20px;">&nbsp;</div>`;
                            newCell.style.padding = '0';
                            newCell.contentEditable = true;
                            newCell.style.cssText = activeCell.style.cssText;
                        });
                        break;
                    case 'deleteRow':
                        if (table.rows.length > 1) table.deleteRow(rowIndex);
                        break;
                    case 'deleteCol':
                        if (row.cells.length > 1) {
                            Array.from(table.rows).forEach(r => r.deleteCell(colIndex));
                        }
                        break;
                    case 'deleteTable':
                        if (confirm('Hapus tabel ini?')) table.closest('.news-editor-table-wrapper').remove();
                        break;
                }
                tableToolbar.style.display = 'none';
                syncContent();
            });

            editorArea.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key.toLowerCase() === 'z') {
                        e.preventDefault();
                        if (e.shiftKey) document.execCommand('redo', false, null);
                        else document.execCommand('undo', false, null);
                        syncContent();
                    } else if (e.key.toLowerCase() === 'y') {
                        e.preventDefault();
                        document.execCommand('redo', false, null);
                        syncContent();
                    }
                }
            });

            syncContent();
        });
    </script>
@endpush
