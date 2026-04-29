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
                    description: 'Unggah file gambar dari komputer atau masukkan URL.',
                    render: () => `
                        <div class="file-drop-zone" id="contentDropZone" style="padding: 2rem 1rem; margin-bottom: 1.5rem; border: 2px dashed #cbd5e1; border-radius: 12px; text-align: center; background: #f8fafc; transition: all 0.2s ease;">
                            <div class="drop-icon" style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem;">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4 style="font-size: 1rem; color: #0f172a; margin-bottom: 0.25rem;">Pilih file atau tarik & lepas ke sini.</h4>
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">jpeg, png, gif - Maks. 10MB</p>
                            <label class="news-btn news-btn--secondary" style="cursor: pointer; background: #fff; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; display: inline-block;">
                                Telusuri file
                                <input id="contentImageFile" type="file" name="imageFile" accept="image/*" hidden>
                            </label>
                            <div id="contentImagePreview" style="margin-top: 1rem; display: none; font-size: 0.9rem; font-weight: 500; color: #3b82f6;"></div>
                        </div>
                        <div style="text-align:center; color:#64748b; font-size:0.8rem; margin-bottom: 1.5rem; font-weight:500; position:relative;">
                            <hr style="position:absolute; width:100%; top:50%; left:0; border:none; border-top:1px solid #e2e8f0; z-index:1;">
                            <span style="background:#fff; padding:0 10px; position:relative; z-index:2;">ATAU</span>
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label class="news-editor-input-modal__label">URL gambar</label>
                            <input class="news-editor-input-modal__input" type="url" name="imageUrl" placeholder="https://contoh.com/foto.jpg">
                        </div>
                        <div>
                            <label class="news-editor-input-modal__label">Lebar Gambar (Persen)</label>
                            <input class="news-editor-input-modal__input" type="number" name="imageWidth" min="10" max="100" value="100" placeholder="100">
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
                }
            };

            // Save cursor position BEFORE modal opens (called from toolbar click)
            let savedRange = null;
            const saveCursorPosition = () => {
                const sel = window.getSelection();
                if (sel && sel.rangeCount > 0) {
                    savedRange = sel.getRangeAt(0).cloneRange();
                }
            };

            // Insert HTML at saved cursor position in editorArea
            const insertHtmlAtCursor = (html) => {
                editorArea.focus();
                const sel = window.getSelection();
                
                // CRITICAL FIX: Ensure range is actually inside editorArea
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
                    // Fallback: Append to end if no valid range found inside editor
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
                        saveCursorPosition(); // Save position BEFORE modal steals focus
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
                    const urlInput = modalForm.querySelector('input[name="imageUrl"]');
                    const widthInput = modalForm.querySelector('input[name="imageWidth"]');
                    
                    const file = fileInput && fileInput.files[0];
                    const url = urlInput ? urlInput.value.trim() : '';
                    const width = widthInput && widthInput.value ? widthInput.value + '%' : '100%';

                    const buildImgHtml = (src) => {
                        return `<p><img src="${src}" style="width:${width}; max-width:100%; height:auto; display:block; margin:10px auto; border-radius:4px;" draggable="true"></p>`;
                    };

                    if (file) {
                        const formDataUpload = new FormData();
                        formDataUpload.append('image', file);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                        closeModal(); // close first, keep savedRange intact

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
                    } else if (url) {
                        insertHtmlAtCursor(buildImgHtml(url));
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

            // ============================================================
            // IMAGE RESIZE + ALIGNMENT SYSTEM
            // ============================================================
            (function initImageResizer() {
                // Create the resize overlay (injected once)
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

                // Style handles
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

                // Click on image in editor – select it & make it draggable
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

                // Alignment toolbar buttons
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

                // Delete button
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

                // Resize drag – mousedown on handle
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

                    // Determine new width based on drag direction
                    if (dragDir.includes('e')) newW = Math.max(60, startW + dx);
                    else if (dragDir.includes('w')) newW = Math.max(60, startW - dx);
                    else if (dragDir === 'n' || dragDir === 's') {
                        const newH = Math.max(40, startH + (dragDir === 's' ? dy : -dy));
                        newW = newH * aspectRatio;
                    }

                    // For corner handles, keep aspect ratio
                    if (dragDir === 'nw' || dragDir === 'ne' || dragDir === 'sw' || dragDir === 'se') {
                        newW = Math.max(60, newW);
                    }

                    // Apply as percentage of editor width
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

                // Reposition on scroll / resize
                editorArea.addEventListener('scroll', positionOverlay, true);
                window.addEventListener('scroll', positionOverlay, true);
                window.addEventListener('resize', positionOverlay);

                // === Visual drop indicator ===
                const dropIndicator = document.createElement('div');
                dropIndicator.id = 'imgDropIndicator';
                dropIndicator.style.cssText = `
                    display: none; position: fixed; pointer-events: none; z-index: 99998;
                    width: 3px; border-radius: 3px; background: #3b82f6;
                    box-shadow: 0 0 0 2px rgba(59,130,246,0.25);
                    transition: top 0.05s, left 0.05s, height 0.05s;
                `;
                // Add pulsing dot at top of indicator
                dropIndicator.innerHTML = `<div style="
                    width:10px; height:10px; border-radius:50%; background:#3b82f6;
                    position:absolute; top:-4px; left:-3.5px;
                    box-shadow:0 0 0 3px rgba(59,130,246,0.3);
                    animation: dropPulse 0.8s ease-in-out infinite alternate;
                "></div>`;
                document.body.appendChild(dropIndicator);

                // Add pulse animation style
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

                // Native drag-and-drop image repositioning inside editor
                let draggedImg = null;
                editorArea.addEventListener('dragstart', (e) => {
                    if (e.target.tagName === 'IMG') {
                        draggedImg = e.target;
                        hideOverlay();
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/html', e.target.outerHTML);
                        // Add a blue outline instead of opacity to mark the dragged image
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
                    // Prevent default to allow drop
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

                    // Handle internal dragged image repositioning
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

                    // Handle external file drop
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        const file = e.dataTransfer.files[0];
                        if (!file.type.startsWith('image/')) return;

                        const formDataUpload = new FormData();
                        formDataUpload.append('image', file);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                        // Create placeholder text while uploading
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

                // Hide when clicking outside editor
                document.addEventListener('mousedown', (e) => {
                    if (!editorArea.contains(e.target) && !overlay.contains(e.target)) {
                        hideOverlay();
                    }
                });
            })();
            // ============================================================

            syncContent();
        });
    </script>
@endpush
