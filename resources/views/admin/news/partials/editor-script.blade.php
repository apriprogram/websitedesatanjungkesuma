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
            const modalSubmitButton = modal?.querySelector('[data-editor-input-modal-submit]');

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
                insertTable: {
                    title: 'Masukkan Tabel',
                    description: 'Atur jumlah baris dan kolom tabel yang akan dimasukkan.',
                    render: () => `
                        <div class="news-editor-modal-section">
                            <label class="news-editor-input-modal__label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span class="modal-section-title">Pilih Ukuran Grid</span>
                                <span id="gridValueDisplay" style="font-weight: 700; color: #3b82f6; background: #fff; border: 1px solid #dbeafe; padding: 2px 10px; border-radius: 6px; font-size: 0.9rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">1 x 1</span>
                            </label>
                            <div id="tableGridPicker" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 4px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 8px; cursor: pointer; justify-content: center; user-select: none;">
                                ${Array.from({length: 100}).map((_, i) => `
                                    <div class="grid-square" data-row="${Math.floor(i/10)+1}" data-col="${(i%10)+1}" 
                                         style="aspect-ratio: 1; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; transition: background 0.08s ease, border-color 0.08s ease;">
                                    </div>
                                `).join('')}
                            </div>
                            <p class="modal-section-desc" style="margin-top: 8px;">Geser kursor untuk memilih ukuran tabel, lalu <strong>klik</strong> untuk mengunci pilihan.</p>
                            <div id="gridLockedInfo" style="display:none; margin-top:10px; padding: 8px 14px; background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 8px; font-size: 0.88rem; color: #065f46; font-weight: 600;">
                                <i class="fas fa-check-circle" style="margin-right:6px;"></i>
                                <span id="gridLockedText">Tabel terkunci</span>
                            </div>
                            <input type="hidden" name="rows" id="tableRowsInput" value="0">
                            <input type="hidden" name="cols" id="tableColsInput" value="0">
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
                    const rowsInput = modalFields.querySelector('#tableRowsInput');
                    const colsInput = modalFields.querySelector('#tableColsInput');
                    const lockedInfo = modalFields.querySelector('#gridLockedInfo');
                    const lockedText = modalFields.querySelector('#gridLockedText');

                    let lockedRows = 0;
                    let lockedCols = 0;

                    const paintGrid = (r, c, isLocked) => {
                        gridSquares.forEach(s => {
                            const sr = parseInt(s.dataset.row);
                            const sc = parseInt(s.dataset.col);
                            const inSelection = sr <= r && sc <= c;
                            if (isLocked) {
                                s.style.background = inSelection ? '#10b981' : '#fff';
                                s.style.borderColor = inSelection ? '#059669' : '#cbd5e1';
                            } else {
                                s.style.background = inSelection ? '#3b82f6' : '#fff';
                                s.style.borderColor = inSelection ? '#2563eb' : '#cbd5e1';
                            }
                        });
                    };

                    if (gridPicker) {
                        gridSquares.forEach(sq => {
                            sq.addEventListener('mouseenter', () => {
                                const r = parseInt(sq.dataset.row);
                                const c = parseInt(sq.dataset.col);
                                gridDisplay.textContent = `${r} x ${c}`;
                                if (lockedRows > 0) {
                                    // Jika sudah ada lock, tampilkan preview tapi pertahankan warna lock
                                    paintGrid(r, c, false);
                                } else {
                                    paintGrid(r, c, false);
                                }
                            });

                            sq.addEventListener('mouseleave', () => {
                                // Kembalikan tampilan ke locked state jika ada
                                if (lockedRows > 0) {
                                    paintGrid(lockedRows, lockedCols, true);
                                    gridDisplay.textContent = `${lockedRows} x ${lockedCols}`;
                                }
                            });

                            sq.addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                lockedRows = parseInt(sq.dataset.row);
                                lockedCols = parseInt(sq.dataset.col);
                                rowsInput.value = lockedRows;
                                colsInput.value = lockedCols;
                                gridDisplay.textContent = `${lockedRows} x ${lockedCols}`;
                                paintGrid(lockedRows, lockedCols, true);
                                gridPicker.style.borderColor = '#10b981';
                                if (lockedInfo) lockedInfo.style.display = 'block';
                                if (lockedText) lockedText.textContent = `Tabel ${lockedRows} baris × ${lockedCols} kolom terkunci — klik Simpan untuk memasukkan`;
                            });
                        });

                        // Reset tampilan saat mouse meninggalkan area grid
                        gridPicker.addEventListener('mouseleave', () => {
                            if (lockedRows > 0) {
                                paintGrid(lockedRows, lockedCols, true);
                                gridDisplay.textContent = `${lockedRows} x ${lockedCols}`;
                            } else {
                                gridSquares.forEach(s => {
                                    s.style.background = '#fff';
                                    s.style.borderColor = '#cbd5e1';
                                });
                            }
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

            // ─────────────────────────────────────────────────────────────
            // Inline Color Picker (Highlight + Text Color)
            // ─────────────────────────────────────────────────────────────
            const HIGHLIGHT_PRESETS = [
                { label: 'Ungu Soft',   value: 'rgba(99, 102, 241, 0.18)' },
                { label: 'Biru Soft',   value: 'rgba(59, 130, 246, 0.18)' },
                { label: 'Hijau Soft',  value: 'rgba(16, 185, 129, 0.18)' },
                { label: 'Kuning Soft', value: 'rgba(251, 191, 36, 0.30)' },
                { label: 'Oranye Soft', value: 'rgba(249, 115, 22, 0.18)' },
                { label: 'Merah Soft',  value: 'rgba(239, 68, 68, 0.18)'  },
                { label: 'Pink Soft',   value: 'rgba(236, 72, 153, 0.18)' },
                { label: 'Teal Soft',   value: 'rgba(20, 184, 166, 0.18)' },
                { label: 'Ungu',        value: '#e0e7ff' },
                { label: 'Biru',        value: '#dbeafe' },
                { label: 'Hijau',       value: '#d1fae5' },
                { label: 'Kuning',      value: '#fef3c7' },
                { label: 'Oranye',      value: '#ffedd5' },
                { label: 'Merah',       value: '#fee2e2' },
                { label: 'Pink',        value: '#fce7f3' },
                { label: 'Teal',        value: '#ccfbf1' },
                { label: 'Abu-abu',     value: '#f1f5f9' },
                { label: 'Dark Blue',   value: '#c7d2fe' },
                { label: 'Lime',        value: '#ecfccb' },
                { label: 'Amber',       value: '#fef08a' },
                { label: 'Rose',        value: '#ffe4e6' },
                { label: 'Violet',      value: '#ede9fe' },
                { label: 'Cyan',        value: '#cffafe' },
                { label: 'Slate',       value: '#e2e8f0' },
            ];

            const TEXT_COLOR_PRESETS = [
                { label: 'Hitam',      value: '#0f172a' },
                { label: 'Abu Gelap',  value: '#334155' },
                { label: 'Abu',        value: '#64748b' },
                { label: 'Abu Terang', value: '#94a3b8' },
                { label: 'Ungu',       value: '#6366f1' },
                { label: 'Biru',       value: '#3b82f6' },
                { label: 'Biru Tua',   value: '#1d4ed8' },
                { label: 'Hijau',      value: '#10b981' },
                { label: 'Hijau Tua',  value: '#047857' },
                { label: 'Kuning',     value: '#f59e0b' },
                { label: 'Oranye',     value: '#f97316' },
                { label: 'Merah',      value: '#ef4444' },
                { label: 'Merah Tua',  value: '#b91c1c' },
                { label: 'Pink',       value: '#ec4899' },
                { label: 'Teal',       value: '#14b8a6' },
                { label: 'Cyan',       value: '#06b6d4' },
                { label: 'Violet',     value: '#8b5cf6' },
                { label: 'Rose',       value: '#f43f5e' },
                { label: 'Amber',      value: '#d97706' },
                { label: 'Lime',       value: '#65a30d' },
                { label: 'Indigo',     value: '#4f46e5' },
                { label: 'Sky',        value: '#0ea5e9' },
                { label: 'Emerald',    value: '#059669' },
                { label: 'Putih',      value: '#ffffff' },
            ];

            /**
             * Build and inject the color panel HTML into a container element.
             */
            const buildColorPanel = (container, presets, command, barEl) => {
                const isHighlight = command === 'hiliteColor';
                const panelId = container.id;

                // Build swatch grid
                const swatchesHtml = presets.map(p => {
                    const isSolid = !p.value.startsWith('rgba');
                    const border  = isSolid ? `border: 1px solid rgba(0,0,0,0.08);` : `border: 1px solid rgba(0,0,0,0.05);`;
                    return `<button type="button" class="cp-swatch" title="${p.label}"
                        data-cp-value="${p.value}" data-cp-command="${command}"
                        style="background:${p.value};${border}"></button>`;
                }).join('');

                container.innerHTML = `
                    <div class="cp-panel-inner">
                        <div class="cp-panel-header">
                            <span>${isHighlight ? 'Warna Latar' : 'Warna Teks'}</span>
                            <button type="button" class="cp-panel-close" data-cp-close="${panelId}" title="Tutup">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                        <div class="cp-swatches">${swatchesHtml}</div>
                        <div class="cp-divider"></div>
                        <div class="cp-custom-row">
                            <label class="cp-custom-label">${isHighlight ? 'Warna Custom' : 'Warna Custom'}:</label>
                            <div class="cp-custom-input-wrap">
                                <input type="color" class="cp-custom-color" id="cp-custom-${panelId}"
                                    value="${isHighlight ? '#e0e7ff' : '#0f172a'}"
                                    title="Pilih warna custom">
                                <input type="text" class="cp-custom-text" id="cp-text-${panelId}"
                                    placeholder="${isHighlight ? 'rgba(99,102,241,0.2)' : '#1d4ed8'}"
                                    value="${isHighlight ? '' : ''}">
                                <button type="button" class="cp-apply-custom" data-cp-command="${command}" title="Terapkan warna custom">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                        ${isHighlight ? `
                        <div class="cp-remove-row">
                            <button type="button" class="cp-remove-btn" data-cp-command="${command}" title="Hapus warna latar">
                                <i class="fas fa-times-circle"></i> Hapus Warna Latar
                            </button>
                        </div>` : `
                        <div class="cp-remove-row">
                            <button type="button" class="cp-remove-btn" data-cp-command="${command}" title="Kembalikan ke warna default">
                                <i class="fas fa-times-circle"></i> Hapus Warna Teks
                            </button>
                        </div>`}
                    </div>
                `;
            };

            /**
             * Apply a color command to selected text, handling hiliteColor special cases.
             */
            const applyColorCommand = (command, color, barEl) => {
                saveCursorPosition();
                if (savedRange && editorArea.contains(savedRange.commonAncestorContainer)) {
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(savedRange);
                }
                editorArea.focus();
                document.execCommand(command, false, color);
                syncContent();
                if (barEl) barEl.style.background = color;
            };

            /**
             * Toggle a color panel open/close.
             */
            const openColorPanels = new Set();
            const toggleColorPanel = (pickerEl, panelEl) => {
                const isOpen = panelEl.style.display === 'block';
                // Close all open panels
                document.querySelectorAll('.news-editor-color-panel').forEach(p => {
                    p.style.display = 'none';
                });
                openColorPanels.clear();
                if (!isOpen) {
                    panelEl.style.display = 'block';
                    openColorPanels.add(panelEl);
                    // Adjust position if near right edge
                    requestAnimationFrame(() => {
                        const rect = panelEl.getBoundingClientRect();
                        const vw = window.innerWidth;
                        if (rect.right > vw - 10) {
                            panelEl.style.right = '0';
                            panelEl.style.left = 'auto';
                        } else {
                            panelEl.style.left = '0';
                            panelEl.style.right = 'auto';
                        }
                    });
                }
            };

            // Initialize both color pickers
            [
                { pickerId: 'highlightPicker', panelId: 'highlightPanel', barId: 'highlightBar', triggerId: 'highlightTrigger', command: 'hiliteColor', presets: HIGHLIGHT_PRESETS },
                { pickerId: 'textColorPicker',  panelId: 'textColorPanel',  barId: 'textColorBar',  triggerId: 'textColorTrigger',  command: 'foreColor',   presets: TEXT_COLOR_PRESETS },
            ].forEach(cfg => {
                const pickerEl  = document.getElementById(cfg.pickerId);
                const panelEl   = document.getElementById(cfg.panelId);
                const barEl     = document.getElementById(cfg.barId);
                const triggerEl = document.getElementById(cfg.triggerId);
                if (!pickerEl || !panelEl || !triggerEl) return;

                // Build panel content
                buildColorPanel(panelEl, cfg.presets, cfg.command, barEl);

                // Trigger button — save cursor BEFORE opening
                triggerEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    saveCursorPosition();
                    toggleColorPanel(pickerEl, panelEl);
                });

                // Swatch clicks — apply color immediately
                panelEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const swatch = e.target.closest('.cp-swatch');
                    if (swatch) {
                        const color = swatch.dataset.cpValue;
                        applyColorCommand(cfg.command, color, barEl);
                        panelEl.style.display = 'none';
                        return;
                    }
                    // Close button
                    const closeBtn = e.target.closest('[data-cp-close]');
                    if (closeBtn) {
                        panelEl.style.display = 'none';
                        return;
                    }
                    // Remove color button
                    const removeBtn = e.target.closest('.cp-remove-btn');
                    if (removeBtn) {
                        if (cfg.command === 'hiliteColor') {
                            applyColorCommand('hiliteColor', 'transparent', barEl);
                        } else {
                            applyColorCommand('removeFormat', null, barEl);
                            if (barEl) barEl.style.background = '#0f172a';
                        }
                        panelEl.style.display = 'none';
                        return;
                    }
                    // Apply custom color button
                    const applyBtn = e.target.closest('.cp-apply-custom');
                    if (applyBtn) {
                        const textInput = panelEl.querySelector('.cp-custom-text');
                        const colorInput = panelEl.querySelector('.cp-custom-color');
                        const color = (textInput?.value || '').trim() || colorInput?.value || '#000000';
                        applyColorCommand(cfg.command, color, barEl);
                        panelEl.style.display = 'none';
                        return;
                    }
                    // Color input change — sync text input
                    const colorPicker = e.target.closest('.cp-custom-color');
                    if (colorPicker) {
                        const textInput = panelEl.querySelector('.cp-custom-text');
                        if (textInput) textInput.value = colorPicker.value;
                    }
                });

                // Sync color input → text input on change
                panelEl.querySelector('.cp-custom-color')?.addEventListener('input', (e) => {
                    const textInput = panelEl.querySelector('.cp-custom-text');
                    if (textInput) textInput.value = e.target.value;
                });
            });

            // Close color panels when clicking outside
            document.addEventListener('mousedown', (e) => {
                const clickedInsidePicker = e.target.closest('.news-editor-color-picker');
                if (!clickedInsidePicker) {
                    document.querySelectorAll('.news-editor-color-panel').forEach(p => {
                        p.style.display = 'none';
                    });
                }
            });

            // Inject color picker styles
            if (!document.getElementById('colorPickerStyles')) {
                const cpStyle = document.createElement('style');
                cpStyle.id = 'colorPickerStyles';
                cpStyle.textContent = `
                    .news-editor-color-picker {
                        position: relative;
                        display: inline-flex;
                        align-items: center;
                    }
                    .news-editor-color-trigger {
                        background: transparent;
                        border: none;
                        cursor: pointer;
                        padding: 6px 8px;
                        border-radius: 8px;
                        display: inline-flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 2px;
                        color: inherit;
                        font-size: 1rem;
                        transition: background 0.15s ease;
                        line-height: 1;
                    }
                    .news-editor-color-trigger:hover {
                        background: rgba(99, 102, 241, 0.10);
                        color: #6366f1;
                    }
                    .news-editor-color-trigger .color-bar {
                        transition: background 0.2s ease;
                    }
                    .news-editor-color-panel {
                        position: absolute;
                        top: calc(100% + 6px);
                        left: 0;
                        z-index: 99999;
                        min-width: 270px;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 14px;
                        box-shadow: 0 20px 40px rgba(0,0,0,0.15), 0 4px 12px rgba(0,0,0,0.08);
                        overflow: hidden;
                        animation: cpFadeIn 0.15s ease;
                    }
                    @keyframes cpFadeIn {
                        from { opacity: 0; transform: translateY(-6px) scale(0.97); }
                        to   { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .cp-panel-inner {
                        padding: 12px;
                    }
                    .cp-panel-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 10px;
                        font-size: 0.82rem;
                        font-weight: 700;
                        color: #475569;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                    }
                    .cp-panel-close {
                        background: none;
                        border: none;
                        cursor: pointer;
                        color: #94a3b8;
                        padding: 2px 6px;
                        border-radius: 6px;
                        font-size: 0.85rem;
                        transition: color 0.15s, background 0.15s;
                    }
                    .cp-panel-close:hover { color: #ef4444; background: #fef2f2; }
                    .cp-swatches {
                        display: grid;
                        grid-template-columns: repeat(8, 1fr);
                        gap: 5px;
                        margin-bottom: 2px;
                    }
                    .cp-swatch {
                        width: 26px;
                        height: 26px;
                        border-radius: 6px;
                        cursor: pointer;
                        transition: transform 0.12s ease, box-shadow 0.12s ease;
                        flex-shrink: 0;
                    }
                    .cp-swatch:hover {
                        transform: scale(1.25);
                        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                        z-index: 2;
                        position: relative;
                    }
                    .cp-divider {
                        height: 1px;
                        background: #f1f5f9;
                        margin: 10px 0;
                    }
                    .cp-custom-label {
                        font-size: 0.78rem;
                        font-weight: 600;
                        color: #64748b;
                        display: block;
                        margin-bottom: 6px;
                    }
                    .cp-custom-input-wrap {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .cp-custom-color {
                        width: 34px;
                        height: 34px;
                        border-radius: 8px;
                        border: 1px solid #e2e8f0;
                        cursor: pointer;
                        padding: 2px;
                        flex-shrink: 0;
                        background: none;
                    }
                    .cp-custom-text {
                        flex: 1;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        padding: 6px 10px;
                        font-size: 0.82rem;
                        color: #334155;
                        outline: none;
                        transition: border-color 0.15s;
                        font-family: monospace;
                    }
                    .cp-custom-text:focus {
                        border-color: #6366f1;
                        box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
                    }
                    .cp-apply-custom {
                        background: #6366f1;
                        border: none;
                        border-radius: 8px;
                        color: white;
                        width: 32px;
                        height: 32px;
                        display: grid;
                        place-items: center;
                        cursor: pointer;
                        font-size: 0.85rem;
                        flex-shrink: 0;
                        transition: background 0.15s;
                    }
                    .cp-apply-custom:hover { background: #4f46e5; }
                    .cp-remove-row {
                        margin-top: 8px;
                    }
                    .cp-remove-btn {
                        background: none;
                        border: 1px solid #fca5a5;
                        border-radius: 8px;
                        color: #ef4444;
                        font-size: 0.8rem;
                        padding: 5px 12px;
                        cursor: pointer;
                        width: 100%;
                        text-align: left;
                        transition: background 0.15s;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .cp-remove-btn:hover {
                        background: #fef2f2;
                    }
                `;
                document.head.appendChild(cpStyle);
            }
            // ─────────────────────────────────────────────────────────────
            // End Inline Color Picker
            // ─────────────────────────────────────────────────────────────

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

            // Fungsi untuk mengumpulkan data dari field modal
            const collectModalFormData = () => {
                const data = {};
                if (!modalFields) return data;
                modalFields.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name) {
                        if (el.type === 'checkbox') {
                            data[el.name] = el.checked ? el.value : '';
                        } else if (el.type === 'file') {
                            data[el.name] = el.files ? el.files[0] : null;
                        } else {
                            data[el.name] = el.value;
                        }
                    }
                });
                return data;
            };

            const handleModalSubmit = () => {
                if (!pendingCommand) return;

                const fields = collectModalFormData();
                let applied = false;

                if (pendingCommand === 'createLink') {
                    const url = (fields['value'] || '').trim();
                    if (url) {
                        if (savedRange && editorArea.contains(savedRange.commonAncestorContainer)) {
                            const sel = window.getSelection();
                            sel.removeAllRanges();
                            sel.addRange(savedRange);
                        }
                        editorArea.focus();
                        document.execCommand(pendingCommand, false, url);
                        applied = true;
                    }
                } else if (pendingCommand === 'insertImage') {
                    const fileInput = modalFields.querySelector('input[name="imageFile"]');
                    const widthInput = modalFields.querySelector('input[name="imageWidth"]');

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
                } else if (pendingCommand === 'insertTable') {
                    const rows = parseInt(fields['rows'] || '0', 10);
                    const cols = parseInt(fields['cols'] || '0', 10);
                    if (rows > 0 && cols > 0) {
                        // Buat header row + body rows
                        let theadHtml = '<thead><tr>';
                        for (let c = 0; c < cols; c++) {
                            theadHtml += `<th contenteditable="true" style="border: 1px solid #cbd5e1; background: #f1f5f9; padding: 10px 12px; font-weight: 600; font-size: 0.9rem; color: #0f172a; text-align: left; min-width: 80px;">Kolom ${c + 1}</th>`;
                        }
                        theadHtml += '</tr></thead>';

                        let tbodyHtml = '<tbody>';
                        for (let r = 0; r < rows; r++) {
                            tbodyHtml += '<tr>';
                            for (let c = 0; c < cols; c++) {
                                tbodyHtml += `<td contenteditable="true" style="border: 1px solid #cbd5e1; padding: 10px 12px; min-width: 80px; min-height: 24px; vertical-align: top;">&nbsp;</td>`;
                            }
                            tbodyHtml += '</tr>';
                        }
                        tbodyHtml += '</tbody>';

                        const tableHtml = `<div class="news-editor-table-wrapper" style="overflow-x: auto; margin: 1.5rem 0;">
                            <table class="news-editor-table" style="width: 100%; border-collapse: collapse; table-layout: auto; font-size: 0.95rem;">
                                ${theadHtml}
                                ${tbodyHtml}
                            </table>
                        </div><p><br></p>`;
                        insertHtmlAtCursor(tableHtml);
                        applied = true;
                    } else {
                        // Belum memilih ukuran grid
                        const lockedInfo = document.querySelector('#gridLockedInfo');
                        if (lockedInfo) {
                            lockedInfo.style.display = 'block';
                            lockedInfo.style.background = '#fef2f2';
                            lockedInfo.style.borderColor = '#fca5a5';
                            lockedInfo.style.color = '#991b1b';
                            const icon = lockedInfo.querySelector('i');
                            if (icon) icon.className = 'fas fa-exclamation-circle';
                            const lt = lockedInfo.querySelector('#gridLockedText');
                            if (lt) lt.textContent = 'Pilih ukuran tabel terlebih dahulu dengan mengklik grid di atas!';
                        }
                        return; // Jangan tutup modal
                    }
                }

                if (applied) {
                    editorArea.focus();
                    syncContent();
                }
                closeModal();
            };

            modalSubmitButton?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                handleModalSubmit();
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
