// Initialize table managers
function initializeUserManager() {
    window.userManager = {
        applyFilters: function(filters) {
            // Implement filter logic for users table
            console.log('Applying user filters:', filters);
            // TODO: Add actual filter implementation
        },
        resetFilters: function() {
            // Reset user table filters
            console.log('Resetting user filters');
            // TODO: Add actual reset implementation
        },
        exportData: async function(format) {
            try {
                // Fetch user data
                const response = await fetch('/admin/users/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ format })
                });

                if (!response.ok) {
                    throw new Error('Export failed');
                }

                // Get the blob from response
                const blob = await response.blob();
                
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `users-export.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } catch (error) {
                console.error('Export failed:', error);
                alert('Gagal mengekspor data. Silakan coba lagi.');
            }
        }
    };
}

function initializePegawaiManager() {
    window.pegawaiManager = {
        applyFilters: function(filters) {
            // Implement filter logic for pegawai table
            console.log('Applying pegawai filters:', filters);
            // TODO: Add actual filter implementation
        },
        resetFilters: function() {
            // Reset pegawai table filters
            console.log('Resetting pegawai filters');
            // TODO: Add actual reset implementation
        },
        exportData: async function(format) {
            try {
                // Fetch pegawai data
                const response = await fetch('/admin/pegawai/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ format })
                });

                if (!response.ok) {
                    throw new Error('Export failed');
                }

                // Get the blob from response
                const blob = await response.blob();
                
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `pegawai-export.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } catch (error) {
                console.error('Export failed:', error);
                alert('Gagal mengekspor data. Silakan coba lagi.');
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize managers
    initializeUserManager();
    initializePegawaiManager();

    const body = document.body;
    // Modal helpers for dialog-backdrop elements
    const modalStack = [];
    const MODAL_VISIBLE_CLASS = 'is-visible';
    const MODAL_CLOSING_CLASS = 'is-closing';
    const BODY_LOCK_CLASS = 'modal-open';
    const FOCUSABLE_SELECTOR = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
        '[contenteditable="true"]',
    ].join(', ');

    const isVisibleElement = (element) => {
        if (!(element instanceof HTMLElement)) return false;
        if (element.hidden || element.getAttribute('aria-hidden') === 'true') return false;
        const style = window.getComputedStyle(element);
        if (style.display === 'none' || style.visibility === 'hidden') return false;
        return element.offsetWidth > 0 || element.offsetHeight > 0 || element.getClientRects().length > 0;
    };

    const getFocusableElements = (container) =>
        container
            ? Array.from(container.querySelectorAll(FOCUSABLE_SELECTOR)).filter(
                  (element) =>
                      element instanceof HTMLElement &&
                      !element.hasAttribute('disabled') &&
                      element.tabIndex !== -1 &&
                      isVisibleElement(element)
              )
            : [];

    const safeFocus = (element) => {
        if (!(element instanceof HTMLElement)) return;
        try {
            element.focus({ preventScroll: true });
        } catch {
            element.focus();
        }
    };

    const focusFirstElement = (modal) => {
        if (!(modal instanceof HTMLElement)) return;
        const explicit = modal.querySelector('[data-autofocus]');
        if (explicit instanceof HTMLElement) {
            safeFocus(explicit);
            return;
        }
        const focusable = getFocusableElements(modal);
        if (focusable.length > 0) {
            safeFocus(focusable[0]);
            return;
        }
        const dialogPanel = modal.querySelector('.dialog');
        if (dialogPanel instanceof HTMLElement) {
            if (!dialogPanel.hasAttribute('tabindex')) {
                dialogPanel.setAttribute('tabindex', '-1');
            }
            safeFocus(dialogPanel);
        }
    };

    const getTopModal = () => modalStack[modalStack.length - 1] ?? null;

    function openModal(modal, options = {}) {
        if (!(modal instanceof HTMLElement)) return;
        if (modalStack.some((entry) => entry.modal === modal)) return;

        const triggerElement =
            options.trigger instanceof HTMLElement
                ? options.trigger
                : document.activeElement instanceof HTMLElement
                ? document.activeElement
                : null;

        const entry = {
            modal,
            trigger: triggerElement,
            options: {
                restoreFocus: options.restoreFocus !== false,
            },
        };

        modalStack.push(entry);
        modal.classList.remove(MODAL_CLOSING_CLASS);
        modal.classList.add(MODAL_VISIBLE_CLASS);
        modal.setAttribute('aria-hidden', 'false');
        body.classList.add(BODY_LOCK_CLASS);

        window.requestAnimationFrame(() => focusFirstElement(modal));
    }

    function closeModal(modal, options = {}) {
        if (!(modal instanceof HTMLElement)) return;
        const entryIndex = modalStack.findIndex((item) => item.modal === modal);
        if (entryIndex === -1) return;

        const [entry] = modalStack.splice(entryIndex, 1);
        const shouldRestoreFocus =
            options.restoreFocus ?? entry.options?.restoreFocus ?? true;
        const focusTarget =
            options.focusElement instanceof HTMLElement
                ? options.focusElement
                : entry.trigger;

        let finished = false;
        const finish = () => {
            if (finished) return;
            finished = true;
            modal.classList.remove(MODAL_VISIBLE_CLASS);
            modal.classList.remove(MODAL_CLOSING_CLASS);
            modal.removeEventListener('transitionend', handleTransitionEnd);
            modal.setAttribute('aria-hidden', 'true');
            if (!modalStack.length) {
                body.classList.remove(BODY_LOCK_CLASS);
            }
        };

        const handleTransitionEnd = (event) => {
            if (event.target !== modal) return;
            finish();
        };

        if (modal.classList.contains(MODAL_VISIBLE_CLASS)) {
            modal.classList.add(MODAL_CLOSING_CLASS);
        }
        modal.setAttribute('aria-hidden', 'true');
        modal.addEventListener('transitionend', handleTransitionEnd);
        window.setTimeout(finish, 240);

        if (
            shouldRestoreFocus &&
            focusTarget &&
            document.contains(focusTarget) &&
            typeof focusTarget.focus === 'function'
        ) {
            window.requestAnimationFrame(() => safeFocus(focusTarget));
        }
    }

    const handleDocumentKeydown = (event) => {
        if (!modalStack.length) return;
        const topEntry = getTopModal();
        if (!topEntry) return;
        const { modal } = topEntry;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal(modal);
            return;
        }

        if (event.key === 'Tab') {
            const focusable = getFocusableElements(modal);
            if (!focusable.length) {
                event.preventDefault();
                focusFirstElement(modal);
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const activeElement = document.activeElement;

            if (event.shiftKey) {
                if (activeElement === first || !modal.contains(activeElement)) {
                    event.preventDefault();
                    safeFocus(last);
                }
            } else if (activeElement === last) {
                event.preventDefault();
                safeFocus(first);
            }
        }
    };

    document.addEventListener('keydown', handleDocumentKeydown);

    document.addEventListener('click', (event) => {
        const closeTrigger = event.target.closest('[data-modal-close]');
        if (closeTrigger) {
            event.preventDefault();
            const modal = closeTrigger.closest('.dialog-backdrop');
            closeModal(modal);
            return;
        }

        const dismissTrigger = event.target.closest('[data-modal-dismiss]');
        if (dismissTrigger) {
            event.preventDefault();
            const modal = dismissTrigger.closest('.dialog-backdrop');
            closeModal(modal);
        }
    });

    const exportModal = document.getElementById('exportConfigModal');
    const exportForm = document.getElementById('exportConfigForm');
    const exportSourceField = exportForm?.querySelector('[data-export-source-field]');
    const exportActionField = exportForm?.querySelector('[data-export-action-field]');
    const exportTitleInput = exportForm?.querySelector('[data-export-title-input]');
    const exportDescriptionInput = exportForm?.querySelector('[data-export-description-input]');
    const exportColumnsContainer = exportForm?.querySelector('[data-export-columns]');
    const exportSummaryCount = exportForm?.querySelector('[data-export-summary-count]');
    const exportSummaryTime = exportForm?.querySelector('[data-export-summary-time]');
    const exportTimestampInput = exportForm?.querySelector('[data-export-timestamp-input]');
    const exportFilterSection = exportForm?.querySelector('[data-export-filter-summary]');
    const exportFilterText = exportForm?.querySelector('[data-export-filter-text]');
    const exportSubmitButton = exportForm?.querySelector('[data-export-submit]');
    const exportSubmitText = exportForm?.querySelector('[data-export-submit-text]');
    const exportSpinner = exportForm?.querySelector('[data-export-spinner]');
    const exportScopeRadios = Array.from(exportForm?.querySelectorAll('input[name="export_scope"]') ?? []);
    const toastStack = document.getElementById('toastStack');
    const printPreview = document.getElementById('exportPrintPreview');

    const toTitleCase = (value = '') =>
        value
            .toString()
            .trim()
            .split(/[\s_]+/)
            .filter(Boolean)
            .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
            .join(' ');

    const formatDateTime = (date = new Date()) =>
        new Intl.DateTimeFormat('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        }).format(date);

    const sanitizeFileName = (name = 'laporan', extension = '') => {
        const safeName = name
            .toString()
            .trim()
            .replace(/[\\/:"*?<>|]+/g, ' ')
            .replace(/\s+/g, '-')
            .toLowerCase()
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        return extension ? `${safeName || 'laporan'}.${extension}` : safeName || 'laporan';
    };

    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    const setElementText = (element, text) => {
        if (element) {
            element.textContent = text;
        }
    };

    const highlightScopeRadios = () => {
        exportScopeRadios.forEach((radio) => {
            const wrapper = radio.closest('.export-toggle');
            if (!wrapper) return;
            wrapper.classList.toggle('is-active', radio.checked);
        });
    };

    const downloadBlob = (blob, filename) => {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    const librarySources = {
        jspdf: 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
        'jspdf-autotable': 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js',
        xlsx: 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
        docx: 'https://cdn.jsdelivr.net/npm/docx@7.7.0/build/index.js',
    };

    const scriptPromises = {};

    const loadScriptOnce = (key) => {
        if (scriptPromises[key]) {
            return scriptPromises[key];
        }
        scriptPromises[key] = new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[data-lib="${key}"]`);
            if (existing) {
                if (existing.dataset.loaded === 'true') {
                    resolve();
                    return;
                }
                existing.addEventListener('load', () => {
                    existing.dataset.loaded = 'true';
                    resolve();
                });
                existing.addEventListener('error', () => {
                    delete scriptPromises[key];
                    reject(new Error(`Gagal memuat pustaka ${key}`));
                });
                return;
            }
            const url = librarySources[key];
            if (!url) {
                delete scriptPromises[key];
                reject(new Error(`Sumber pustaka ${key} tidak ditemukan.`));
                return;
            }
            const script = document.createElement('script');
            script.src = url;
            script.async = true;
            script.dataset.lib = key;
            script.addEventListener('load', () => {
                script.dataset.loaded = 'true';
                resolve();
            });
            script.addEventListener('error', () => {
                delete scriptPromises[key];
                reject(new Error(`Gagal memuat pustaka ${key}`));
            });
            document.head.appendChild(script);
        });
        return scriptPromises[key];
    };

    const ensureCondition = async (key, validator) => {
        if (validator()) return;
        await loadScriptOnce(key);
        if (!validator()) {
            throw new Error(`Pustaka ${key} belum tersedia.`);
        }
    };

    const ensurePdfLib = async () => {
        await ensureCondition('jspdf', () => Boolean(window.jspdf?.jsPDF));
        await ensureCondition('jspdf-autotable', () => Boolean(window.jspdf?.jsPDF?.API?.autoTable));
        return window.jspdf.jsPDF;
    };

    const ensureXlsxLib = async () => {
        await ensureCondition('xlsx', () => Boolean(window.XLSX));
        return window.XLSX;
    };

    const ensureDocxLib = async () => {
        await ensureCondition('docx', () => Boolean(window.docx?.Document));
        return window.docx;
    };

    const activeToasts = new Set();

    const closeToast = (toast) => {
        if (!toast || toast.dataset.closing === 'true') return;
        toast.dataset.closing = 'true';
        toast.classList.add('is-leaving');
        window.setTimeout(() => {
            toast.remove();
            activeToasts.delete(toast);
        }, 220);
    };

    const createToast = ({ type = 'info', title = '', message = '', actions = [], duration = 3000 }) => {
        if (!toastStack) return null;
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;

        if (title) {
            const titleEl = document.createElement('p');
            titleEl.className = 'toast__title';
            titleEl.textContent = title;
            toast.appendChild(titleEl);
        }

        if (message) {
            const messageEl = document.createElement('p');
            messageEl.className = 'toast__message';
            messageEl.textContent = message;
            toast.appendChild(messageEl);
        }

        if (Array.isArray(actions) && actions.length > 0) {
            const actionsWrapper = document.createElement('div');
            actionsWrapper.className = 'toast__actions';
            actions.forEach(({ label, handler }) => {
                if (!label || typeof handler !== 'function') return;
                const actionButton = document.createElement('button');
                actionButton.type = 'button';
                actionButton.textContent = label;
                actionButton.addEventListener('click', () => handler());
                actionsWrapper.appendChild(actionButton);
            });
            toast.appendChild(actionsWrapper);
        }

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'toast__close';
        closeButton.setAttribute('aria-label', 'Tutup notifikasi');
        closeButton.textContent = 'Tutup';
        closeButton.addEventListener('click', () => closeToast(toast));
        toast.appendChild(closeButton);

        toastStack.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('is-visible'));

        if (duration > 0) {
            window.setTimeout(() => closeToast(toast), duration);
        }

        activeToasts.add(toast);
        return {
            close: () => closeToast(toast),
            element: toast,
        };
    };

    const exportState = {
        manager: null,
        action: null,
        source: null,
        counts: { filtered: 0, page: 0 },
        timestamp: null,
    };
    let lastExportContext = null;

    const actionLabelMap = {
        pdf: 'Ekspor PDF',
        excel: 'Ekspor Excel',
        word: 'Ekspor Word',
        print: 'Cetak',
    };

    const scopeLabelMap = {
        all: 'Semua hasil (terfilter)',
        page: 'Halaman ini',
    };

    const getScopeLabel = (scope) => scopeLabelMap[scope] ?? scope;

    const updateSummaryCountDisplay = (scope, counts) => {
        if (!counts) return;
        const value = scope === 'page' ? counts.page : counts.filtered;
        setElementText(exportSummaryCount, `${value}`);
    };

    const formatTimestampInput = (date) => {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

    const updateTimestampSummary = (date) => {
        if (!exportSummaryTime) return;
        setElementText(exportSummaryTime, formatDateTime(date));
    };

    const getTimestampFromInput = () => {
        if (!exportTimestampInput) return new Date();
        const value = exportTimestampInput.value?.trim();
        const applyAndReturn = (date) => {
            if (exportTimestampInput) {
                exportTimestampInput.value = formatTimestampInput(date);
            }
            return date;
        };
        if (!value) {
            return applyAndReturn(new Date());
        }
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) {
            return applyAndReturn(new Date());
        }
        return parsed;
    };

    const buildColumnOption = (column, checked = true) => {
        const wrapper = document.createElement('label');
        wrapper.className = 'export-column-option';
        if (column.locked) {
            wrapper.classList.add('is-locked');
        }

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.value = column.key;
        input.checked = checked;
        input.disabled = column.locked === true;
        input.dataset.exportColumn = column.key;

        const label = document.createElement('span');
        label.textContent = column.label;

        wrapper.appendChild(input);
        wrapper.appendChild(label);
        if (column.description) {
            const description = document.createElement('small');
            description.textContent = column.description;
            description.className = 'export-column-option__hint';
            wrapper.appendChild(description);
        }
        return wrapper;
    };

    const renderExportColumns = (columns = [], selected = []) => {
        if (!exportColumnsContainer) return;
        exportColumnsContainer.innerHTML = '';
        const selectedKeys = new Set(selected && selected.length ? selected : columns.map((column) => column.key));
        columns.forEach((column) => {
            const option = buildColumnOption(column, selectedKeys.has(column.key));
            exportColumnsContainer.appendChild(option);
        });
    };

    const getSelectedColumnKeys = () =>
        Array.from(exportColumnsContainer?.querySelectorAll('input[data-export-column]') ?? [])
            .filter((input) => input.checked)
            .map((input) => input.value);

    const setScopeSelection = (scope) => {
        exportScopeRadios.forEach((radio) => {
            radio.checked = radio.value === scope;
        });
        highlightScopeRadios();
        updateSummaryCountDisplay(scope, exportState.counts);
    };

    const openExportConfig = (source, action) => {
        const manager = tableManagers[source];
        if (!manager) return;

        const counts = manager.getCounts ? manager.getCounts() : null;
        if (!counts || counts.filtered === 0) {
            createToast({
                type: 'error',
                title: 'Tidak ada data',
                message: 'Tidak ada baris yang bisa diekspor pada tampilan ini.',
            });
            closeExportMenu(source);
            return;
        }

        exportState.manager = manager;
        exportState.action = action;
        exportState.source = source;
        exportState.counts = counts;
        exportState.timestamp = new Date();
        setExportLoading(false);

        const preferredScope = manager.getPreferredScope ? manager.getPreferredScope() : 'all';
        const columnOptions = manager.getColumnOptions ? manager.getColumnOptions() : [];
        const preferredColumns =
            manager.getPreferredColumns && manager.getPreferredColumns().length > 0
                ? manager.getPreferredColumns()
                : columnOptions
                      .filter((column) => column.defaultSelected !== false)
                      .map((column) => column.key);

        if (exportSourceField) exportSourceField.value = source;
        if (exportActionField) exportActionField.value = action;
        if (exportTitleInput) {
            exportTitleInput.value = manager.tableTitle ?? 'Data';
        }
        if (exportDescriptionInput) {
            const footer = document.querySelector(`[data-export-menu="${source}"] .export-dropdown__footer`);
            const summary = footer?.dataset.exportSummary ?? '';
            exportDescriptionInput.value = summary;
        }

        renderExportColumns(columnOptions, preferredColumns);
        setScopeSelection(preferredScope);
        if (exportTimestampInput) {
            exportTimestampInput.value = formatTimestampInput(exportState.timestamp);
        }
        updateTimestampSummary(exportState.timestamp);

        const filterSummary = manager.getFilterSummary ? manager.getFilterSummary() : '';
        if (exportFilterSection) {
            if (filterSummary) {
                exportFilterSection.hidden = false;
                setElementText(exportFilterText, filterSummary);
            } else {
                exportFilterSection.hidden = true;
                setElementText(exportFilterText, '—');
            }
        }

        if (exportSubmitText) {
            exportSubmitText.textContent = actionLabelMap[action] ?? 'Ekspor';
        }

        closeExportMenu(source);
        openModal(exportModal);
        window.requestAnimationFrame(() => exportTitleInput?.focus());
    };

    exportScopeRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            highlightScopeRadios();
            updateSummaryCountDisplay(radio.value, exportState.counts);
        });
    });
    highlightScopeRadios();

    const handleTimestampInputChange = () => {
        const timestamp = getTimestampFromInput();
        updateTimestampSummary(timestamp);
    };

    if (exportTimestampInput) {
        exportTimestampInput.addEventListener('change', handleTimestampInputChange);
        exportTimestampInput.addEventListener('input', handleTimestampInputChange);
    }

    const setExportLoading = (isLoading) => {
        if (!exportSubmitButton || !exportSpinner) return;
        exportSubmitButton.disabled = isLoading;
        exportSpinner.hidden = !isLoading;
    };

    const downloadErrorLog = (error, payload) => {
        const logPayload = {
            message: error?.message ?? 'Terjadi kesalahan tak terduga.',
            stack: error?.stack ?? null,
            action: exportState.action,
            source: exportState.source,
            scope: payload?.metadata?.scope ?? null,
            count: payload?.metadata?.count ?? null,
            timestamp: payload?.metadata?.timestampIso ?? new Date().toISOString(),
        };
        const blob = new Blob([JSON.stringify(logPayload, null, 2)], { type: 'application/json' });
        const filename = sanitizeFileName(`${payload?.metadata?.title || 'laporan'}-log`, 'json');
        downloadBlob(blob, filename);
    };

    const determinePdfOrientation = (columns = []) => {
        const estimatedWidth = columns.reduce((total, column) => total + (column.width || 0), 0);
        return estimatedWidth > 460 ? 'landscape' : 'portrait';
    };

    const generatePdf = async (payload) => {
        const jsPDFConstructor = await ensurePdfLib();
        const orientation = determinePdfOrientation(payload.columns);
        const doc = new jsPDFConstructor({ orientation, unit: 'pt', format: [595, 935] }); // F4: 210mm x 330mm
        const marginX = 15;
        let cursorY = 50;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text(payload.metadata.title, marginX, cursorY);
        cursorY += 20;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(60, 72, 88);

        const metaLines = [
            payload.metadata.description?.trim() || null,
            `Jumlah data: ${payload.metadata.count}`,
            `Tanggal: ${payload.metadata.timestamp}`,
            payload.metadata.filters ? `Filter: ${payload.metadata.filters}` : null,
        ].filter(Boolean);

        metaLines.forEach((line) => {
            const wrapped = doc.splitTextToSize(line, doc.internal.pageSize.getWidth() - marginX * 2);
            doc.text(wrapped, marginX, cursorY);
            cursorY += wrapped.length * 14;
        });

        cursorY += 8;
        doc.setTextColor(15, 23, 42);

        const columnStyles = {};
        payload.columns.forEach((column, index) => {
            columnStyles[index] = {
                halign: column.align || 'left',
                cellWidth: column.width || 'auto',
            };
        });

        doc.autoTable({
            startY: cursorY,
            head: [payload.headers],
            body: payload.rows,
            margin: { left: marginX, right: marginX },
            styles: {
                font: 'helvetica',
                fontSize: 5,
                cellPadding: { top: 2, right: 2, bottom: 2, left: 2 },
                overflow: 'linebreak',
                valign: 'top',
                minCellHeight: 12,
            },
            headStyles: {
                fillColor: [226, 232, 255],
                textColor: [15, 23, 42],
                fontStyle: 'bold',
            },
            alternateRowStyles: {
                fillColor: [250, 250, 255],
            },
            columnStyles,
            didDrawPage(data) {
                const pageWidth = doc.internal.pageSize.getWidth();
                const pageHeight = doc.internal.pageSize.getHeight();
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);
                doc.text(
                    `Halaman ${data.pageNumber} dari ${pageCount}`,
                    pageWidth / 2,
                    pageHeight - 24,
                    { align: 'center' }
                );
                doc.setTextColor(15, 23, 42);
            },
        });

        const filename = sanitizeFileName(`${payload.metadata.title}-${payload.metadata.scopeLabel}`, 'pdf');
        doc.save(filename);
    };

    const generateExcel = async (payload) => {
        const XLSXLib = await ensureXlsxLib();

        // Create header section with better formatting
        const infoRows = [
            [payload.metadata.title, '', '', ''], // Title spans multiple columns
            ['', '', '', ''],
            ['Jumlah data', payload.metadata.count, '', ''],
            ['Tanggal', payload.metadata.timestamp, '', ''],
        ];
        if (payload.metadata.filters) {
            infoRows.push(['Filter', payload.metadata.filters, '', '']);
        }

        // Add row numbers to headers and data
        const headersWithNo = ['No', ...payload.headers];
        const rowsWithNo = payload.rows.map((row, index) => [index + 1, ...row.map((cell) => cell ?? '')]);

        const worksheetData = [
            ...infoRows,
            [''], // Blank row
            headersWithNo,
            ...rowsWithNo,
        ];
        const dataSheet = XLSXLib.utils.aoa_to_sheet(worksheetData);
        const headerRowIndex = infoRows.length + 1; // plus blank row
        
        // Freeze header row
        dataSheet['!freeze'] = { xSplit: 0, ySplit: headerRowIndex + 1 };

        // Set column widths - No column is narrower
        const columnWidths = [
            { wch: 5 }, // No column
            ...payload.columns.map((column, columnIndex) => {
                const headerLength = payload.headers[columnIndex]?.length ?? 0;
                const maxDataLength = payload.rows.reduce((max, row) => {
                    const length = String(row[columnIndex] ?? '').length;
                    return Math.max(max, length);
                }, 0);
                const estimated = Math.max(headerLength, maxDataLength);
                return { wch: clamp(estimated + 2, 10, 40) };
            }),
        ];
        dataSheet['!cols'] = columnWidths;

        // Style title row (row 0)
        const titleCell = dataSheet['A1'];
        if (titleCell) {
            titleCell.s = {
                font: { bold: true, sz: 16, color: { rgb: '0F172A' } },
                alignment: { horizontal: 'left', vertical: 'center' },
            };
        }

        // Style info rows (Jumlah data, Tanggal)
        for (let i = 2; i < infoRows.length; i++) {
            const labelCell = dataSheet[XLSXLib.utils.encode_cell({ r: i, c: 0 })];
            if (labelCell) {
                labelCell.s = {
                    font: { bold: true, color: { rgb: '3C4858' } },
                    alignment: { horizontal: 'left', vertical: 'center' },
                };
            }
        }

        // Style table headers (including No column)
        headersWithNo.forEach((header, index) => {
            const headerAddress = XLSXLib.utils.encode_cell({ r: headerRowIndex, c: index });
            const headerCell = dataSheet[headerAddress];
            if (headerCell) {
                headerCell.s = {
                    font: { bold: true, color: { rgb: '0F172A' } },
                    fill: { fgColor: { rgb: 'E2E8FF' } },
                    alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
                    border: {
                        top: { style: 'thin', color: { rgb: '000000' } },
                        bottom: { style: 'thin', color: { rgb: '000000' } },
                        left: { style: 'thin', color: { rgb: '000000' } },
                        right: { style: 'thin', color: { rgb: '000000' } },
                    },
                };
            }
        });

        const metadataSheet = XLSXLib.utils.aoa_to_sheet([
            ['Judul', payload.metadata.title],
            ['Deskripsi', payload.metadata.description || '-'],
            ['Jumlah Data', payload.metadata.count],
            ['Tanggal', payload.metadata.timestamp],
            ['Filter', payload.metadata.filters || 'Tidak ada filter aktif'],
        ]);
        metadataSheet['!cols'] = [{ wch: 18 }, { wch: 60 }];

        const workbook = XLSXLib.utils.book_new();
        XLSXLib.utils.book_append_sheet(workbook, dataSheet, 'Data');
        XLSXLib.utils.book_append_sheet(workbook, metadataSheet, 'Metadata');

        const filename = sanitizeFileName(`${payload.metadata.title}-${payload.metadata.scopeLabel}`, 'xlsx');
        XLSXLib.writeFile(workbook, filename);
    };

    const generateWord = async (payload) => {
        const docxLib = await ensureDocxLib();
        const { Document, HeadingLevel, Packer, Paragraph, Table, TableRow, TableCell, TextRun, WidthType, AlignmentType, BorderStyle, PageOrientation } = docxLib;

        // Calculate column widths based on PDF settings (convert pt to twips: 1pt = 20 twips)
        const columnWidths = [
            { key: 'nama', width: 50 * 20 },
            { key: 'nik', width: 35 * 20 },
            { key: 'tempat_tanggal_lahir', width: 60 * 20 },
            { key: 'jenis_kelamin', width: 35 * 20 },
            { key: 'agama', width: 35 * 20 },
            { key: 'alamat', width: 60 * 20 },
            { key: 'status', width: 40 * 20 },
            { key: 'email', width: 55 * 20 },
            { key: 'kontak', width: 45 * 20 },
            { key: 'jabatan', width: 50 * 20 },
            { key: 'nip', width: 35 * 20 },
            { key: 'status_kepegawaian', width: 45 * 20 },
            { key: 'masa_jabatan', width: 55 * 20 },
            { key: 'sk_pengangkatan', width: 35 * 20 },
            { key: 'sk_pemberhentian', width: 35 * 20 },
            { key: 'universitas', width: 50 * 20 },
            { key: 'pendidikan_terakhir', width: 45 * 20 },
            { key: 'tahun_lulus', width: 30 * 20 },
            { key: 'sertifikat_pelatihan', width: 55 * 20 },
            { key: 'bahasa', width: 35 * 20 },
        ];

        const tableRows = [
            new TableRow({
                tableHeader: true,
                children: payload.headers.map((header, index) =>
                    new TableCell({
                        width: { size: columnWidths[index]?.width || 1000, type: WidthType.DXA },
                        shading: { fill: 'E2E8FF' },
                        margins: { top: 40, bottom: 40, left: 40, right: 40 },
                        children: [
                            new Paragraph({
                                children: [new TextRun({ text: header, bold: true, size: 12, font: 'Arial' })],
                                alignment: AlignmentType.CENTER,
                            }),
                        ],
                    })
                ),
            }),
            ...payload.rows.map((row) =>
                new TableRow({
                    children: row.map((value, index) => {
                        const lines = String(value ?? '').split(/\n+/);
                        const paragraph = new Paragraph({
                            children: lines.flatMap((line, lineIndex) =>
                                lineIndex === 0
                                    ? [new TextRun({ text: line, size: 12, font: 'Arial' })]
                                    : [new TextRun({ text: '', break: 1 }), new TextRun({ text: line, size: 12, font: 'Arial' })]
                            ),
                        });
                        return new TableCell({
                            width: { size: columnWidths[index]?.width || 1000, type: WidthType.DXA },
                            margins: { top: 40, bottom: 40, left: 40, right: 40 },
                            children: [paragraph],
                        });
                    }),
                })
            ),
        ];

        const sections = [
            new Paragraph({
                children: [new TextRun({ text: payload.metadata.title, bold: true, size: 28, font: 'Arial' })],
                heading: HeadingLevel.HEADING_1,
            }),
            new Paragraph({
                children: [
                    new TextRun({ text: `Jumlah data: ${payload.metadata.count}`, font: 'Arial', size: 20 }),
                ],
            }),
            new Paragraph({
                children: [
                    new TextRun({ text: `Tanggal: ${payload.metadata.timestamp}`, font: 'Arial', size: 20 }),
                ],
            }),
        ];

        if (payload.metadata.filters) {
            sections.push(
                new Paragraph({
                    text: `Filter: ${payload.metadata.filters}`,
                })
            );
        }

        sections.push(
            new Table({
                width: { size: 100, type: WidthType.PERCENTAGE },
                rows: tableRows,
            })
        );

        const doc = new Document({
            sections: [
                {
                    properties: {
                        page: {
                            width: 18720, // F4 landscape width: 330mm = 13 inches = 18720 twips
                            height: 11906, // F4 landscape height: 210mm = 8.27 inches = 11906 twips
                            orientation: PageOrientation.LANDSCAPE,
                            margin: {
                                top: 576, // 0.4 inch = 576 twips
                                right: 576,
                                bottom: 576,
                                left: 576,
                            },
                        },
                    },
                    children: sections,
                },
            ],
        });

        const blob = await Packer.toBlob(doc);
        const filename = sanitizeFileName(`${payload.metadata.title}-${payload.metadata.scopeLabel}`, 'docx');
        downloadBlob(blob, filename);
    };

    const generatePrint = (payload) =>
        new Promise((resolve) => {
            if (!printPreview) {
                resolve();
                return;
            }

            printPreview.innerHTML = '';

            const titleEl = document.createElement('h1');
            titleEl.className = 'print-title';
            titleEl.textContent = payload.metadata.title;

            const descriptionEl = document.createElement('p');
            descriptionEl.className = 'print-description';
            descriptionEl.textContent = payload.metadata.description || '';

            const metaEl = document.createElement('p');
            metaEl.className = 'print-meta';
            metaEl.textContent = `Jumlah data: ${payload.metadata.count} | Cakupan: ${payload.metadata.scopeLabel} | Timestamp: ${payload.metadata.timestamp}`;

            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const headRow = document.createElement('tr');
            payload.headers.forEach((header) => {
                const th = document.createElement('th');
                th.textContent = header;
                headRow.appendChild(th);
            });
            thead.appendChild(headRow);

            const tbody = document.createElement('tbody');
            payload.rows.forEach((row) => {
                const tr = document.createElement('tr');
                row.forEach((cell) => {
                    const td = document.createElement('td');
                    td.textContent = cell ?? '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

            table.appendChild(thead);
            table.appendChild(tbody);

            const footer = document.createElement('div');
            footer.className = 'print-footer';

            printPreview.appendChild(titleEl);
            if (payload.metadata.description) {
                printPreview.appendChild(descriptionEl);
            }
            printPreview.appendChild(metaEl);
            if (payload.metadata.filters) {
                const filterEl = document.createElement('p');
                filterEl.className = 'print-meta';
                filterEl.textContent = `Filter: ${payload.metadata.filters}`;
                printPreview.appendChild(filterEl);
            }
            printPreview.appendChild(table);
            printPreview.appendChild(footer);
            printPreview.hidden = false;

            closeModal(exportModal);

            window.requestAnimationFrame(() => {
                window.addEventListener(
                    'afterprint',
                    () => {
                        printPreview.innerHTML = '';
                        printPreview.hidden = true;
                        resolve();
                    },
                    { once: true }
                );
                window.print();
            });
        });

    const executeExportAction = async (action, payload) => {
        switch (action) {
            case 'pdf':
                return generatePdf(payload);
            case 'excel':
                return generateExcel(payload);
            case 'word':
                return generateWord(payload);
            case 'print':
                return generatePrint(payload);
            default:
                throw new Error('Format ekspor tidak dikenali.');
        }
    };

    const performExport = async (override = {}) => {
        if (!exportState.manager) {
            createToast({
                type: 'error',
                title: 'Ekspor tidak tersedia',
                message: 'Manajer tabel belum siap untuk diekspor.',
            });
            return;
        }

        const scopeRadio =
            override.scope ??
            exportForm?.querySelector('input[name="export_scope"]:checked')?.value ??
            'all';
        const selectedColumns = override.columnKeys ?? getSelectedColumnKeys();
        const currentAction = exportState.action;
        const actionLabel = actionLabelMap[currentAction] ?? 'Ekspor';

        if (selectedColumns.length === 0) {
            createToast({
                type: 'error',
                title: 'Pilih kolom',
                message: 'Pilih minimal satu kolom sebelum melakukan ekspor.',
            });
            return;
        }

        const title = exportTitleInput?.value?.trim() || exportState.manager.tableTitle || 'Data';
        const description = exportDescriptionInput?.value?.trim() || '';
        const timestampDate = getTimestampFromInput();
        exportState.timestamp = timestampDate;
        const timestamp = formatDateTime(timestampDate);

        setExportLoading(true);

        try {
            const dataset = exportState.manager.buildExportPayload(scopeRadio, selectedColumns);
            const filterSummary = exportState.manager.getFilterSummary
                ? exportState.manager.getFilterSummary()
                : '';
            const metadata = {
                title,
                description,
                count: dataset.rows.length,
                scope: scopeRadio,
                scopeLabel: getScopeLabel(scopeRadio),
                timestamp,
                timestampIso: timestampDate.toISOString(),
                filters: filterSummary,
            };

            if (metadata.count === 0) {
                throw new Error('Tidak ada baris yang tersedia untuk diekspor.');
            }

            const payload = { ...dataset, metadata };
            lastExportContext = { action: currentAction, payload, source: exportState.source };

            await executeExportAction(currentAction, payload);

            exportState.manager.setPreferredScope?.(scopeRadio);
            exportState.manager.setPreferredColumns?.(selectedColumns);
            exportState.manager.refreshExportMeta?.();
            exportState.counts = exportState.manager.getCounts
                ? exportState.manager.getCounts()
                : exportState.counts;
            updateSummaryCountDisplay(scopeRadio, exportState.counts);

            createToast({
                type: 'success',
                title: `${actionLabel} berhasil`,
                message: `Berhasil memproses ${metadata.count} baris.`,
            });
            exportState.manager = null;
            exportState.action = null;
            exportState.source = null;
            lastExportContext = null;

            if (currentAction !== 'print') {
                closeModal(exportModal);
            }
        } catch (error) {
            console.error(error);
            let errorMessage = error?.message ?? 'Terjadi kesalahan saat mengekspor data.';
            if (typeof errorMessage === 'string' && /pustaka/i.test(errorMessage)) {
                errorMessage += ' Pastikan koneksi internet tersedia sebelum mencoba lagi.';
            }
            createToast({
                type: 'error',
                title: `${actionLabel} gagal`,
                message: errorMessage,
                actions: [
                    {
                        label: 'Coba lagi',
                        handler: () => {
                            performExport({ scope: scopeRadio, columnKeys: selectedColumns });
                        },
                    },
                    {
                        label: 'Unduh log',
                        handler: () => {
                            if (lastExportContext?.payload) {
                                downloadErrorLog(error, lastExportContext.payload);
                            }
                        },
                    },
                ],
                duration: 3000,
            });
        } finally {
            setExportLoading(false);
        }
    };

    exportForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        performExport();
    });

    const exportDropdowns = document.querySelectorAll('[data-export]');
    let openExportKey = null;

    const closeExportMenu = (key) => {
        if (!key) return;
        const toggle = document.querySelector(`[data-export-toggle="${key}"]`);
        const menu = document.querySelector(`[data-export-menu="${key}"]`);
        if (!toggle || !menu) return;
        menu.classList.remove('is-open');
        window.setTimeout(() => {
            menu.hidden = true;
        }, 160);
        toggle.setAttribute('aria-expanded', 'false');
        if (openExportKey === key) {
            openExportKey = null;
        }
    };

    const openExportMenu = (key) => {
        const toggle = document.querySelector(`[data-export-toggle="${key}"]`);
        const menu = document.querySelector(`[data-export-menu="${key}"]`);
        if (!toggle || !menu) return;
        if (openExportKey && openExportKey !== key) {
            closeExportMenu(openExportKey);
        }
        menu.hidden = false;
        requestAnimationFrame(() => menu.classList.add('is-open'));
        toggle.setAttribute('aria-expanded', 'true');
        openExportKey = key;
    };

    exportDropdowns.forEach((wrapper) => {
        const key = wrapper.dataset.export;
        const toggle = wrapper.querySelector('[data-export-toggle]');
        const menu = wrapper.querySelector('[data-export-menu]');
        if (!key || !toggle || !menu) return;

        menu.addEventListener('click', (event) => event.stopPropagation());

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (openExportKey === key) {
                closeExportMenu(key);
            } else {
                openExportMenu(key);
            }
        });
    });

    document.querySelectorAll('[data-export-action]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            const action = button.dataset.exportAction;
            const source = button.dataset.exportSource;
            if (!action || !source) return;

            // If export modal exists and user expects a config, open it
            if (exportModal) {
                // open modal config as before
                openExportConfig(source, action);
                return;
            }

            // Fallback: perform immediate client-side export using table manager
            try {
                const manager = tableManagers[source];
                if (!manager) throw new Error('Manager tabel tidak ditemukan.');
                const counts = manager.getCounts ? manager.getCounts() : { filtered: 0, page: 0 };
                if (!counts || counts.filtered === 0) {
                    createToast({ type: 'error', title: 'Tidak ada data', message: 'Tidak ada baris yang bisa diekspor.' });
                    return;
                }

                const selectedColumns = manager.getPreferredColumns ? manager.getPreferredColumns() : [];
                if (selectedColumns.length === 0) {
                    createToast({ type: 'error', title: 'Pilih kolom', message: 'Tidak ada kolom yang dipilih untuk ekspor.' });
                    return;
                }

                const dataset = manager.buildExportPayload ? manager.buildExportPayload('all', selectedColumns) : null;
                if (!dataset) throw new Error('Gagal membangun data ekspor.');

                const metadata = {
                    title: manager.tableTitle || 'Data',
                    description: '',
                    count: dataset.rows.length,
                    scope: 'all',
                    scopeLabel: getScopeLabel('all'),
                    timestamp: formatDateTime(new Date()),
                    timestampIso: new Date().toISOString(),
                    filters: manager.getFilterSummary ? manager.getFilterSummary() : '',
                };

                const payload = { headers: dataset.headers, rows: dataset.rows, columns: dataset.columns, metadata };
                await executeExportAction(action, payload);
                createToast({ type: 'success', title: 'Ekspor berhasil', message: `Berhasil menghasilkan ${payload.metadata.count} baris.` });
            } catch (err) {
                console.error('Export error:', err);
                createToast({ type: 'error', title: 'Ekspor gagal', message: err.message || 'Terjadi kesalahan saat mengekspor.' });
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!openExportKey) return;
        const menu = document.querySelector(`[data-export-menu="${openExportKey}"]`);
        const toggle = document.querySelector(`[data-export-toggle="${openExportKey}"]`);
        if (!menu || !toggle) return;
        if (!menu.contains(event.target) && !toggle.contains(event.target)) {
            closeExportMenu(openExportKey);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && openExportKey) {
            closeExportMenu(openExportKey);
        }
    });

    const tabButtons = Array.from(document.querySelectorAll('[data-settings-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
    const actionButtons = Array.from(document.querySelectorAll('[data-tab-visible]'));

    const toggleActionButtons = (target) => {
        actionButtons.forEach((button) => {
            const isVisible = button.dataset.tabVisible === target;
            button.hidden = !isVisible;
            button.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
        });
    };

    const setActiveTab = (target) => {
        if (!target) return;
        tabButtons.forEach((button) => {
            const isActive = button.dataset.settingsTab === target;
            button.classList.toggle('is-active', isActive);
        });
        panels.forEach((panel) => {
            const isActive = panel.dataset.settingsPanel === target;
            panel.classList.toggle('is-active', isActive);
            panel.toggleAttribute('hidden', !isActive);
        });
        toggleActionButtons(target);
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => setActiveTab(button.dataset.settingsTab));
    });

    const defaultTab =
        tabButtons.find((button) => button.classList.contains('is-active'))?.dataset.settingsTab ??
        panels[0]?.dataset.settingsPanel;
    if (defaultTab) {
        setActiveTab(defaultTab);
    } else if (panels[0]) {
        toggleActionButtons(panels[0].dataset.settingsPanel);
    }

    const feedbackPanels = document.querySelectorAll('.feedback-panel');
    feedbackPanels.forEach((panel) => {
        panel.classList.add('is-loading');
        window.setTimeout(() => {
            panel.classList.add('is-hiding');
            window.setTimeout(() => {
                panel.remove();
            }, 360);
        }, 5000);
    });

    const createTableManager = (config = {}) => {
        const rows = Array.from(document.querySelectorAll(config.rowsSelector || ''));
        const searchInput = config.searchInput ? document.querySelector(config.searchInput) : null;
        const entriesSelect = config.entriesSelect ? document.querySelector(config.entriesSelect) : null;
        const infoTarget = config.infoTarget ? document.querySelector(config.infoTarget) : null;
        const paginationTarget = config.paginationTarget ? document.querySelector(config.paginationTarget) : null;
        const emptyElements = Array.from(document.querySelectorAll(config.emptySelector || ''));
        const searchEmpty = emptyElements.find((element) => element.classList.contains('table-empty--search'));
        const baseEmpty = emptyElements.find((element) => !element.classList.contains('table-empty--search'));

        const exportCountElement = config.exportCountSelector
            ? document.querySelector(config.exportCountSelector)
            : null;
        const exportFooterElement = exportCountElement
            ? exportCountElement.closest('.export-dropdown__footer')
            : null;
        const scopeLabelElement = exportFooterElement?.querySelector('[data-export-scope-label]');

        const defaultScope =
            exportFooterElement?.closest('.export-dropdown')?.dataset.exportDefaultScope ??
            config.defaultScope ??
            'all';

        const normalize = (value) => {
            if (value === undefined || value === null) return '';
            return String(value).trim();
        };

        const columnDefs = Array.isArray(config.columns)
            ? config.columns
                  .filter((column) => column?.key)
                  .map((column) => {
                      let extractor;
                      if (typeof column.extractor === 'function') {
                          extractor = column.extractor;
                      } else if (typeof column.domAccessor === 'function') {
                          extractor = (row) => normalize(column.domAccessor(row, column));
                      } else if (column.selector) {
                          extractor = (row) => {
                              const element = row.querySelector(column.selector);
                              return normalize(element?.textContent ?? '');
                          };
                      } else {
                          extractor = (row) => normalize(row.textContent);
                      }
                      return {
                          key: column.key,
                          label: column.label ?? column.key,
                          extractor,
                          width: column.width,
                          align: column.align,
                          type: column.type ?? 'text',
                          defaultSelected: column.defaultSelected !== false,
                          locked: column.locked === true,
                          description: column.description ?? '',
                      };
                  })
            : [];

        const columnMap = new Map(columnDefs.map((column) => [column.key, column]));
        const defaultColumnKeys = columnDefs
            .filter((column) => column.defaultSelected)
            .map((column) => column.key);

        const state = {
            rows,
            filteredRows: rows.slice(),
            perPage: parseInt(entriesSelect?.value ?? config.defaultPerPage ?? 10, 10),
            page: 1,
            totalPages: 1,
            activeFilters: {},
            preferredScope: defaultScope,
            preferredColumns:
                defaultColumnKeys.length > 0 ? defaultColumnKeys : columnDefs.map((column) => column.key),
        };

        const computeCounts = () => {
            const filtered = state.filteredRows.length;
            const startIndex = (state.page - 1) * state.perPage;
            const pageCount = Math.max(Math.min(filtered - startIndex, state.perPage), 0);
            return {
                total: state.rows.length,
                filtered,
                page: pageCount,
            };
        };

        const updateScopeLabel = () => {
            if (scopeLabelElement) {
                scopeLabelElement.textContent = getScopeLabel(state.preferredScope);
            }
        };

        const updateExportMeta = () => {
            const counts = computeCounts();
            if (exportCountElement) {
                exportCountElement.textContent = counts.filtered;
            }
            if (exportFooterElement) {
                exportFooterElement.dataset.filteredCount = counts.filtered;
                exportFooterElement.dataset.pageCount = counts.page;
                exportFooterElement.dataset.visibleCount = counts.page;
                exportFooterElement.dataset.totalCount = counts.total;
            }
            updateScopeLabel();
        };

        const hideAllRows = () => {
            state.rows.forEach((row) => {
                row.hidden = true;
                row.classList.add('is-hidden');
            });
        };

        const updateRows = () => {
            hideAllRows();
            if (state.filteredRows.length === 0) {
                return;
            }
            const startIndex = (state.page - 1) * state.perPage;
            const endIndex = startIndex + state.perPage;
            state.filteredRows.slice(startIndex, endIndex).forEach((row) => {
                row.hidden = false;
                row.classList.remove('is-hidden');
            });
        };

        const updateInfo = () => {
            if (!infoTarget) return;
            const total = state.filteredRows.length;
            if (total === 0) {
                infoTarget.textContent = 'Menampilkan 0 data';
                return;
            }
            const startIndex = (state.page - 1) * state.perPage + 1;
            const endIndex = Math.min(startIndex + state.perPage - 1, total);
            infoTarget.textContent = `Menampilkan ${startIndex}-${endIndex} dari ${total} data`;
        };

        const updateEmptyStates = () => {
            const hasRows = state.rows.length > 0;
            const hasFiltered = state.filteredRows.length > 0;
            if (!hasRows) {
                if (searchEmpty) {
                    searchEmpty.hidden = true;
                    searchEmpty.setAttribute('aria-hidden', 'true');
                }
                if (baseEmpty) {
                    baseEmpty.hidden = false;
                    baseEmpty.setAttribute('aria-hidden', 'false');
                }
                return;
            }
            if (!hasFiltered) {
                if (searchEmpty) {
                    searchEmpty.hidden = false;
                    searchEmpty.setAttribute('aria-hidden', 'false');
                }
                if (baseEmpty) {
                    baseEmpty.hidden = true;
                    baseEmpty.setAttribute('aria-hidden', 'true');
                }
            } else {
                if (searchEmpty) {
                    searchEmpty.hidden = true;
                    searchEmpty.setAttribute('aria-hidden', 'true');
                }
                if (baseEmpty) {
                    baseEmpty.hidden = true;
                    baseEmpty.setAttribute('aria-hidden', 'true');
                }
            }
        };

        const goToPage = (pageNumber) => {
            const totalPages = Math.max(1, state.totalPages);
            const clamped = Math.min(Math.max(pageNumber, 1), totalPages);
            if (clamped === state.page) return;
            state.page = clamped;
            updateRowsAndUI();
        };

        const renderPagination = () => {
            if (!paginationTarget) return;
            const total = state.filteredRows.length;
            state.totalPages = total === 0 ? 1 : Math.ceil(total / Math.max(state.perPage, 1));
            paginationTarget.innerHTML = '';
            if (total <= state.perPage) {
                paginationTarget.hidden = true;
                return;
            }
            paginationTarget.hidden = false;

            const createButton = (label, pageNumber, options = {}) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'pagination-btn';
                if (options.disabled) {
                    button.disabled = true;
                    button.classList.add('is-disabled');
                }
                if (options.active) {
                    button.classList.add('is-active');
                    button.setAttribute('aria-current', 'page');
                }
                if (options.ariaLabel) {
                    button.setAttribute('aria-label', options.ariaLabel);
                }
                button.textContent = label;
                if (!options.disabled && !options.active) {
                    button.addEventListener('click', () => goToPage(pageNumber));
                }
                return button;
            };

            const createEllipsis = () => {
                const span = document.createElement('span');
                span.className = 'pagination-ellipsis';
                span.textContent = '...';
                return span;
            };

            paginationTarget.appendChild(
                createButton('Sebelumnya', state.page - 1, {
                    disabled: state.page <= 1,
                    ariaLabel: 'Halaman sebelumnya',
                })
            );

            const maxButtons = 5;
            let startPage = Math.max(1, state.page - 2);
            let endPage = Math.min(state.totalPages, startPage + maxButtons - 1);
            startPage = Math.max(1, endPage - maxButtons + 1);

            if (startPage > 1) {
                paginationTarget.appendChild(createButton('1', 1, { active: state.page === 1 }));
                if (startPage > 2) {
                    paginationTarget.appendChild(createEllipsis());
                }
            }

            for (let pageNumber = startPage; pageNumber <= endPage; pageNumber += 1) {
                paginationTarget.appendChild(
                    createButton(String(pageNumber), pageNumber, { active: pageNumber === state.page })
                );
            }

            if (endPage < state.totalPages) {
                if (endPage < state.totalPages - 1) {
                    paginationTarget.appendChild(createEllipsis());
                }
                paginationTarget.appendChild(
                    createButton(String(state.totalPages), state.totalPages, {
                        active: state.page === state.totalPages,
                    })
                );
            }

            paginationTarget.appendChild(
                createButton('Berikutnya', state.page + 1, {
                    disabled: state.page >= state.totalPages,
                    ariaLabel: 'Halaman berikutnya',
                })
            );
        };

        const updateRowsAndUI = () => {
            updateRows();
            updateInfo();
            updateEmptyStates();
            renderPagination();
            updateExportMeta();
        };

        const matchesActiveFilters = (row) => {
            if (typeof config.filterPredicate !== 'function') return true;
            return config.filterPredicate(row, state.activeFilters || {});
        };

        const applyFiltersAndUpdate = () => {
            const term = (searchInput?.value || '').trim().toLowerCase();
            state.filteredRows = state.rows.filter((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                const matchesSearch = term === '' || haystack.includes(term);
                if (!matchesSearch) return false;
                return matchesActiveFilters(row);
            });
            state.page = 1;
            updateRowsAndUI();
        };

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                applyFiltersAndUpdate();
            });
        }

        if (entriesSelect) {
            entriesSelect.addEventListener('change', () => {
                const parsed = parseInt(entriesSelect.value, 10);
                state.perPage = Number.isFinite(parsed) && parsed > 0 ? parsed : config.defaultPerPage ?? 10;
                state.page = 1;
                updateRowsAndUI();
            });
        }

        const getRowsByScope = (scope = 'all') => {
            if (scope === 'page') {
                const startIndex = (state.page - 1) * state.perPage;
                return state.filteredRows.slice(startIndex, startIndex + state.perPage);
            }
            return state.filteredRows.slice();
        };

        const resolveColumns = (requestedKeys = []) => {
            const candidates = (requestedKeys.length > 0 ? requestedKeys : state.preferredColumns)
                .map((key) => columnMap.get(key))
                .filter(Boolean);
            return candidates.length > 0 ? candidates : columnDefs;
        };

        applyFiltersAndUpdate();

        return {
            setFilters(newFilters = {}) {
                state.activeFilters = newFilters;
                applyFiltersAndUpdate();
            },
            resetFilters() {
                state.activeFilters = {};
                applyFiltersAndUpdate();
            },
            getFilters() {
                return { ...state.activeFilters };
            },
            getCounts: () => computeCounts(),
            getColumnOptions: () =>
                columnDefs.map((column) => ({
                    key: column.key,
                    label: column.label,
                    description: column.description,
                    defaultSelected: state.preferredColumns.includes(column.key),
                    locked: column.locked,
                })),
            getPreferredColumns: () => state.preferredColumns.slice(),
            setPreferredColumns(keys = []) {
                const valid = keys.filter((key) => columnMap.has(key));
                if (valid.length > 0) {
                    state.preferredColumns = valid;
                }
            },
            getPreferredScope: () => state.preferredScope,
            setPreferredScope(scope) {
                if (['all', 'page'].includes(scope)) {
                    state.preferredScope = scope;
                    updateScopeLabel();
                }
            },
            buildExportPayload(scope = 'all', requestedKeys = []) {
                const selectedColumns = resolveColumns(requestedKeys);
                const rowsForExport = getRowsByScope(scope);
                const mappedRows = rowsForExport.map((row) =>
                    selectedColumns.map((column) => normalize(column.extractor(row, column)))
                );
                return {
                    headers: selectedColumns.map((column) => column.label),
                    rows: mappedRows,
                    columns: selectedColumns,
                };
            },
            getFilterSummary: () =>
                typeof config.describeFilters === 'function'
                    ? config.describeFilters(state.activeFilters || {})
                    : '',
            refreshExportMeta: () => updateExportMeta(),
            tableTitle: config.tableTitle ?? 'Data',
        };
    };

    const tableManagers = {};

    tableManagers.users = createTableManager({
        rowsSelector: '[data-table="users"] .table-row',
        searchInput: '#adminSearch',
        entriesSelect: '[data-entries-select="users"]',
        infoTarget: '[data-entries-info="users"]',
        paginationTarget: '[data-pagination="users"]',
        emptySelector: '[data-empty="users"]',
        defaultPerPage: 10,
        exportCountSelector: '[data-export="users"] [data-export-count]',
        tableTitle: 'Daftar Admin Portal',
        columns: [
            {
                key: 'number',
                label: 'No',
                width: 36,
                align: 'center',
                type: 'number',
                extractor: (row) => row.querySelector('.table-cell--seq')?.textContent?.trim() ?? '',
            },
            {
                key: 'admin',
                label: 'Admin',
                width: 200,
                extractor: (row) => {
                    const name = row.querySelector('.table-cell__meta strong')?.textContent?.trim() ?? '';
                    const email = row.querySelector('.table-cell__meta small')?.textContent?.trim() ?? '';
                    return email ? `${name}\n${email}` : name;
                },
            },
            {
                key: 'role',
                label: 'Role',
                width: 120,
                extractor: (row) => row.querySelector('.table-chip')?.textContent?.replace(/\s+/g, ' ').trim() ?? '',
            },
            {
                key: 'status',
                label: 'Status',
                width: 130,
                extractor: (row) => row.querySelector('.status-pill')?.textContent?.replace(/\s+/g, ' ').trim() ?? '',
            },
            {
                key: 'login',
                label: 'Login Terakhir',
                width: 160,
                extractor: (row) => row.querySelector('.table-cell--muted')?.textContent?.trim() ?? '',
            },
        ],
        filterPredicate: (row, filters = {}) => {
            const statusFilters = filters.status ?? [];
            const roleFilters = filters.role ?? [];
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const rowRole = (row.dataset.role || '').toLowerCase();
            if (statusFilters.length > 0 && !statusFilters.includes(rowStatus)) {
                return false;
            }
            if (roleFilters.length > 0 && !roleFilters.includes(rowRole)) {
                return false;
            }
            return true;
        },
        describeFilters: (filters = {}) => {
            const parts = [];
            if ((filters.status ?? []).length > 0) {
                parts.push(`Status: ${(filters.status || []).map((value) => toTitleCase(value)).join(', ')}`);
            }
            if ((filters.role ?? []).length > 0) {
                parts.push(`Role: ${(filters.role ?? []).map((value) => toTitleCase(value)).join(', ')}`);
            }
            return parts.join(' | ');
        },
    });

    tableManagers.pegawai = createTableManager({
        rowsSelector: '[data-table="pegawai"] .table-row',
        searchInput: '#pegawaiSearch',
        entriesSelect: '[data-entries-select="pegawai"]',
        infoTarget: '[data-entries-info="pegawai"]',
        paginationTarget: '[data-pagination="pegawai"]',
        emptySelector: '[data-empty="pegawai"]',
        defaultPerPage: 10,
        exportCountSelector: '[data-export="pegawai"] [data-export-count]',
        tableTitle: 'Data Aparatur Desa',
        columns: [
            { key: 'nama', label: 'Nama', width: 50, domAccessor: (row) => row.dataset.pegawaiNama || '' },
            { key: 'nik', label: 'NIK', width: 50, domAccessor: (row) => row.dataset.pegawaiNik || '' },
            { key: 'tempat_tanggal_lahir', label: 'Tempat Tanggal Lahir', width: 60, domAccessor: (row) => row.dataset.pegawaiTtl || '' },
            { key: 'jenis_kelamin', label: 'Jenis Kelamin', width: 35, domAccessor: (row) => row.dataset.pegawaiGender || '' },
            { key: 'agama', label: 'Agama', width: 35, domAccessor: (row) => row.dataset.pegawaiAgama || '' },
            { key: 'alamat', label: 'Alamat', width: 60, domAccessor: (row) => row.dataset.pegawaiAlamat || '' },
            { key: 'status', label: 'Status', width: 40, domAccessor: (row) => row.dataset.pegawaiStatus || '' },
            { key: 'email', label: 'Email', width: 55, domAccessor: (row) => row.dataset.pegawaiEmail || '' },
            { key: 'kontak', label: 'Kontak', width: 45, domAccessor: (row) => row.dataset.pegawaiHp || '' },
            { key: 'jabatan', label: 'Jabatan', width: 50, domAccessor: (row) => row.dataset.pegawaiJabatan || '' },
            { key: 'nip', label: 'NIP', width: 35, domAccessor: (row) => row.dataset.pegawaiNip || '' },
            { key: 'status_kepegawaian', label: 'Status Kepegawaian', width: 45, domAccessor: (row) => row.dataset.pegawaiStatus || '' },
            { key: 'masa_jabatan', label: 'Masa Jabatan', width: 55, domAccessor: (row) => row.dataset.pegawaiMasaJabatan || '' },
            { key: 'sk_pengangkatan', label: 'SK Pengangkatan', width: 35, domAccessor: (row) => row.dataset.pegawaiSkAngkat || '' },
            { key: 'sk_pemberhentian', label: 'SK Pemberhentian', width: 35, domAccessor: (row) => row.dataset.pegawaiSkBerhenti || '' },
            { key: 'universitas', label: 'Universitas', width: 50, domAccessor: (row) => row.dataset.pegawaiUniv || '' },
            { key: 'pendidikan_terakhir', label: 'Pendidikan Terakhir', width: 45, domAccessor: (row) => row.dataset.pegawaiPendidikan || '' },
            { key: 'tahun_lulus', label: 'Tahun Lulus', width: 30, domAccessor: (row) => row.dataset.pegawaiTahunLulus || '' },
            { key: 'sertifikat_pelatihan', label: 'Sertifikat Pelatihan/Kursus', width: 55, domAccessor: (row) => row.dataset.pegawaiSertifikat || '' },
            { key: 'bahasa', label: 'Bahasa', width: 35, domAccessor: (row) => row.dataset.pegawaiBahasa || '' },
        ],
        fallbackBuilder: (row) => {
            const cells = row.querySelectorAll('.table-cell');
            const name = row.querySelector('.table-cell__meta strong')?.textContent?.trim() ?? '';
            const email = row.querySelector('.table-cell__meta small')?.textContent?.trim() ?? '';
            const jabatanText = row.querySelector('.table-chip')?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
            const statusText = row.querySelector('.status-pill')?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
            const updatedText = row.querySelectorAll('.table-cell')[4]?.textContent?.trim() ?? '';
            const sequenceValue = Number(cells[0]?.textContent?.trim());
            return {
                sequence: Number.isFinite(sequenceValue) ? sequenceValue : cells[0]?.textContent?.trim() ?? '',
                name,
                email,
                jabatan: jabatanText,
                status: statusText,
                status_value: statusText.toLowerCase(),
                updated: updatedText,
                search: (row.dataset.search || '').toLowerCase(),
            };
        },
        filterPredicate: (row, filters = {}, data = {}) => {
            const statusFilters = filters.status ?? [];
            const rowStatus = (data.status_value || row.dataset.status || '').toLowerCase();
            if (statusFilters.length > 0 && !statusFilters.includes(rowStatus)) {
                return false;
            }
            return true;
        },
        describeFilters: (filters = {}) => {
            const statusFilters = filters.status ?? [];
            if (statusFilters.length === 0) return '';
            const formattedStatuses = statusFilters.map((value) => toTitleCase(value)).join(', ');
            return `Status: ${formattedStatuses}`;
        },
    });

    const fancySelectControllers = new WeakMap();
    const pegawaiModal = document.getElementById('pegawaiFormModal');
    const pegawaiForm = document.getElementById('pegawaiForm');
    const pegawaiMethodField = pegawaiForm?.querySelector('[data-method-field]');
    const pegawaiContextField = pegawaiForm?.querySelector('input[name="context_tab"]');
    const pegawaiRemoveField = pegawaiForm?.querySelector('[data-remove-field]');
    const pegawaiNameField = pegawaiForm?.querySelector('input[name="nama"]');
    const pegawaiJabatanField = pegawaiForm?.querySelector('input[name="jabatan"]');
    const pegawaiEmailField = pegawaiForm?.querySelector('input[name="email"]');
    const pegawaiPhoneField = pegawaiForm?.querySelector('input[name="nomor_hp"]');
    const pegawaiStatusField = pegawaiForm?.querySelector('select[name="status"]');
    const pegawaiNikField = pegawaiForm?.querySelector('input[name="nik"]');
    const pegawaiNipField = pegawaiForm?.querySelector('input[name="nip"]');
    const pegawaiGenderField = pegawaiForm?.querySelector('select[name="jenis_kelamin"]');
    const pegawaiTempatLahirField = pegawaiForm?.querySelector('input[name="tempat_lahir"]');
    const pegawaiTanggalLahirField = pegawaiForm?.querySelector('input[name="tanggal_lahir"]');
    const pegawaiAgamaField = pegawaiForm?.querySelector('input[name="agama"]');
    const pegawaiAlamatField = pegawaiForm?.querySelector('textarea[name="alamat"]');
    const pegawaiMasaMulaiField = pegawaiForm?.querySelector('input[name="masa_jabatan_mulai"]');
    const pegawaiMasaSelesaiField = pegawaiForm?.querySelector('input[name="masa_jabatan_selesai"]');
    const pegawaiUniversitasField = pegawaiForm?.querySelector('input[name="universitas"]');
    const pegawaiPendidikanField = pegawaiForm?.querySelector('input[name="pendidikan_terakhir"]');
    const pegawaiTahunLulusField = pegawaiForm?.querySelector('input[name="tahun_lulus"]');
    const pegawaiBahasaField = pegawaiForm?.querySelector('input[name="bahasa"]');
    const pegawaiSertifikatField = pegawaiForm?.querySelector('textarea[name="sertifikat_pelatihan"]');
    const pegawaiLastLoginField = pegawaiForm?.querySelector('input[name="last_login"]');
    const pegawaiMetaCreatedBy = pegawaiForm?.querySelector('[data-meta="created-by"]');
    const pegawaiMetaUpdatedBy = pegawaiForm?.querySelector('[data-meta="updated-by"]');
    const pegawaiMetaCreatedAt = pegawaiForm?.querySelector('[data-meta="created-at"]');
    const pegawaiMetaUpdatedAt = pegawaiForm?.querySelector('[data-meta="updated-at"]');
    const pegawaiMetaDeletedAt = pegawaiForm?.querySelector('[data-meta="deleted-at"]');
    const pegawaiCurrentKtp = pegawaiForm?.querySelector('[data-current-ktp]');
    const pegawaiCurrentKtpLink = pegawaiCurrentKtp?.querySelector('[data-current-ktp-link]');
    const pegawaiRemoveKtpWrapper = pegawaiForm?.querySelector('[data-remove-ktp]');
    const pegawaiRemoveKtpCheckbox = pegawaiRemoveKtpWrapper?.querySelector('input[name="remove_foto_ktp"]');
    const pegawaiCurrentSkPengangkatan = pegawaiForm?.querySelector('[data-current-sk-pengangkatan]');
    const pegawaiCurrentSkPengangkatanLink =
        pegawaiCurrentSkPengangkatan?.querySelector('[data-current-sk-pengangkatan-link]');
    const pegawaiRemoveSkPengangkatanWrapper = pegawaiForm?.querySelector('[data-remove-sk-pengangkatan]');
    const pegawaiRemoveSkPengangkatanCheckbox =
        pegawaiRemoveSkPengangkatanWrapper?.querySelector('input[name="remove_sk_pengangkatan"]');
    const pegawaiCurrentSkPemberhentian = pegawaiForm?.querySelector('[data-current-sk-pemberhentian]');
    const pegawaiCurrentSkPemberhentianLink =
        pegawaiCurrentSkPemberhentian?.querySelector('[data-current-sk-pemberhentian-link]');
    const pegawaiRemoveSkPemberhentianWrapper = pegawaiForm?.querySelector('[data-remove-sk-pemberhentian]');
    const pegawaiRemoveSkPemberhentianCheckbox =
        pegawaiRemoveSkPemberhentianWrapper?.querySelector('input[name="remove_sk_pemberhentian"]');
    const pegawaiTitle = document.getElementById('pegawaiFormTitle');
    const pegawaiSubtitle = document.getElementById('pegawaiFormSubtitle');
    let pegawaiAvatarHelpers = null;
    const pegawaiCreateAction = pegawaiForm?.dataset.createAction;

    const adminModal = document.getElementById('adminFormModal');
    const adminForm = document.getElementById('userForm');
    const adminMethodField = adminForm?.querySelector('[data-method-field]');
    const adminContextField = adminForm?.querySelector('input[name="context_tab"]');
    const adminRemoveField = adminForm?.querySelector('[data-remove-field]');
    const adminNameField = adminForm?.querySelector('input[name="nama"]');
    const adminEmailField = adminForm?.querySelector('input[name="email"]');
    const adminPhoneField = adminForm?.querySelector('input[name="nomor_hp"]');
    const adminPasswordField = adminForm?.querySelector('input[name="password"]');
    const adminPasswordConfirmField = adminForm?.querySelector('input[name="password_confirmation"]');
    const adminIsAdminField = adminForm?.querySelector('input[name="is_admin"]');
    const adminIsActiveField = adminForm?.querySelector('input[name="is_active"]');
    const adminTitle = document.getElementById('userFormTitle');
    const adminSubtitle = document.getElementById('userFormSubtitle');
    let adminAvatarHelpers = null;
    const adminCreateAction = adminForm?.dataset.createAction;

    const setFancySelectValue = (select, value, emitChange = false) => {
        if (!select) return;
        const controller = fancySelectControllers.get(select);
        if (controller && typeof controller.setValue === 'function') {
            controller.setValue(value, { emitChange });
            return;
        }
        select.value = value;
        if (emitChange) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const setupAvatarInput = (form, previewId, removeSelector) => {
        if (!form) return null;
        const fileInput = form.querySelector('input[type="file"]');
        const preview = previewId ? document.getElementById(previewId) : null;
        const removeButton = removeSelector ? form.querySelector(removeSelector) : null;

        const set = (url) => {
            if (preview && url) {
                preview.src = url;
            }
            if (fileInput) {
                fileInput.value = '';
            }
            if (removeButton) {
                removeButton.hidden = !url;
            }
        };

        const reset = () => {
            if (preview && preview.dataset.default) {
                preview.src = preview.dataset.default;
            }
            if (fileInput) {
                fileInput.value = '';
            }
            if (removeButton) {
                removeButton.hidden = true;
            }
        };

        if (removeButton) {
            removeButton.addEventListener('click', (event) => {
                event.preventDefault();
                reset();
            });
        }

        return { set, reset };
    };

    adminAvatarHelpers = setupAvatarInput(adminForm, 'userAvatarPreview', '[data-action="remove-user-photo"]');
    pegawaiAvatarHelpers = setupAvatarInput(pegawaiForm, 'pegawaiAvatarPreview', '[data-action="remove-pegawai-photo"]');

    const applyFileInfo = (wrapper, link, removeWrapper, checkbox, url, name, fallbackLabel) => {
        if (!wrapper) return;
        const hasFile = Boolean(url);
        const empty = wrapper.querySelector('[data-empty]');
        if (empty) empty.hidden = hasFile;
        if (link) {
            if (hasFile) {
                link.hidden = false;
                link.href = url;
                link.textContent = name || fallbackLabel || link.textContent || 'Lihat dokumen';
            } else {
                link.hidden = true;
                link.removeAttribute('href');
            }
        }
        if (removeWrapper) {
            removeWrapper.hidden = !hasFile;
        }
        if (checkbox) {
            checkbox.checked = false;
        }
    };

    const setPegawaiMetadata = ({ createdBy, updatedBy, createdAt, updatedAt, deletedAt } = {}) => {
        const autoNote = 'Diisi otomatis setelah tersimpan';
        if (pegawaiMetaCreatedBy) pegawaiMetaCreatedBy.textContent = createdBy || autoNote;
        if (pegawaiMetaUpdatedBy) pegawaiMetaUpdatedBy.textContent = updatedBy || autoNote;
        if (pegawaiMetaCreatedAt) pegawaiMetaCreatedAt.textContent = createdAt || autoNote;
        if (pegawaiMetaUpdatedAt) pegawaiMetaUpdatedAt.textContent = updatedAt || autoNote;
        if (pegawaiMetaDeletedAt) pegawaiMetaDeletedAt.textContent = deletedAt || '-';
    };

    const setDetailLink = (wrapper, link, url, name) => {
        if (!wrapper) return;
        const empty = wrapper.querySelector('[data-empty]');
        const hasFile = Boolean(url);
        if (empty) empty.hidden = hasFile;
        if (link) {
            if (hasFile) {
                link.hidden = false;
                link.href = url;
                link.textContent = name || link.textContent || 'Lihat dokumen';
            } else {
                link.hidden = true;
                link.removeAttribute('href');
            }
        }
    };

    function resetAdminForm() {
        if (!adminForm) return;
        adminForm.reset();
        adminForm.action = adminCreateAction || adminForm.action;
        if (adminMethodField) adminMethodField.value = 'POST';
        if (adminContextField) adminContextField.value = 'users';
        if (adminRemoveField) adminRemoveField.value = '0';
        if (adminIsAdminField) adminIsAdminField.checked = true;
        if (adminIsActiveField) adminIsActiveField.checked = true;
        if (adminPasswordField) {
            adminPasswordField.required = true;
            adminPasswordField.value = '';
        }
        if (adminPasswordConfirmField) {
            adminPasswordConfirmField.required = true;
            adminPasswordConfirmField.value = '';
        }
        if (adminTitle) adminTitle.textContent = 'Tambah Admin';
        if (adminSubtitle) adminSubtitle.textContent = 'Isi detail admin untuk memberikan akses dashboard.';
        if (adminAvatarHelpers?.reset) adminAvatarHelpers.reset();
        adminForm.dataset.mode = 'create';
    }

    function openAdminForm(mode, payload = {}) {
        if (!adminForm || !adminModal) return;
        setActiveTab('users');
        if (adminContextField) adminContextField.value = 'users';

        if (mode === 'edit') {
            adminForm.dataset.mode = 'edit';
            adminForm.action = payload.update || adminCreateAction || adminForm.action;
            if (adminMethodField) adminMethodField.value = 'PUT';
            if (adminTitle) adminTitle.textContent = 'Perbarui Admin';
            if (adminSubtitle) adminSubtitle.textContent = 'Sesuaikan data admin dan status akses.';
            if (adminNameField) adminNameField.value = payload.nama ?? '';
            if (adminEmailField) adminEmailField.value = payload.email ?? '';
            if (adminPhoneField) adminPhoneField.value = payload.nomorHp ?? '';
            if (adminPasswordField) {
                adminPasswordField.required = false;
                adminPasswordField.value = '';
            }
            if (adminPasswordConfirmField) {
                adminPasswordConfirmField.required = false;
                adminPasswordConfirmField.value = '';
            }
            if (adminIsAdminField) adminIsAdminField.checked = payload.isAdmin === '1';
            if (adminIsActiveField) adminIsActiveField.checked = payload.isActive === '1';
            if (adminRemoveField) adminRemoveField.value = '0';
            if (adminAvatarHelpers?.set) adminAvatarHelpers.set(payload.avatar);
        } else {
            resetAdminForm();
        }

        openModal(adminModal);
        window.requestAnimationFrame(() => {
            if (adminNameField) {
                adminNameField.focus();
            }
        });
    }

    function resetPegawaiForm() {
        if (!pegawaiForm) return;

        pegawaiForm.reset();
        pegawaiForm.action = pegawaiCreateAction || pegawaiForm.action;

        if (pegawaiMethodField) pegawaiMethodField.value = 'POST';
        if (pegawaiContextField) pegawaiContextField.value = 'pegawai';
        if (pegawaiRemoveField) pegawaiRemoveField.value = '0';
        if (pegawaiStatusField) setFancySelectValue(pegawaiStatusField, 'Aktif');
        if (pegawaiGenderField) setFancySelectValue(pegawaiGenderField, 'Laki-laki');
        if (pegawaiNikField) pegawaiNikField.value = '';
        if (pegawaiNipField) pegawaiNipField.value = '';
        if (pegawaiTempatLahirField) pegawaiTempatLahirField.value = '';
        if (pegawaiTanggalLahirField) pegawaiTanggalLahirField.value = '';
        if (pegawaiAgamaField) pegawaiAgamaField.value = '';
        if (pegawaiAlamatField) pegawaiAlamatField.value = '';
        if (pegawaiMasaMulaiField) pegawaiMasaMulaiField.value = '';
        if (pegawaiMasaSelesaiField) pegawaiMasaSelesaiField.value = '';
        if (pegawaiUniversitasField) pegawaiUniversitasField.value = '';
        if (pegawaiPendidikanField) pegawaiPendidikanField.value = '';
        if (pegawaiTahunLulusField) pegawaiTahunLulusField.value = '';
        if (pegawaiBahasaField) pegawaiBahasaField.value = '';
        if (pegawaiSertifikatField) pegawaiSertifikatField.value = '';
        if (pegawaiLastLoginField) pegawaiLastLoginField.value = '';
        setPegawaiMetadata();

        applyFileInfo(
            pegawaiCurrentKtp,
            pegawaiCurrentKtpLink,
            pegawaiRemoveKtpWrapper,
            pegawaiRemoveKtpCheckbox,
            '',
            '',
            'Lihat foto KTP'
        );
        applyFileInfo(
            pegawaiCurrentSkPengangkatan,
            pegawaiCurrentSkPengangkatanLink,
            pegawaiRemoveSkPengangkatanWrapper,
            pegawaiRemoveSkPengangkatanCheckbox,
            '',
            '',
            'Lihat dokumen'
        );
        applyFileInfo(
            pegawaiCurrentSkPemberhentian,
            pegawaiCurrentSkPemberhentianLink,
            pegawaiRemoveSkPemberhentianWrapper,
            pegawaiRemoveSkPemberhentianCheckbox,
            '',
            '',
            'Lihat dokumen'
        );

        if (pegawaiTitle) pegawaiTitle.textContent = 'Tambah Pegawai';
        if (pegawaiSubtitle) pegawaiSubtitle.textContent = 'Lengkapi informasi aparatur desa untuk publikasi.';
        if (pegawaiAvatarHelpers?.reset) pegawaiAvatarHelpers.reset();

        pegawaiForm.dataset.mode = 'create';
    }

    function openPegawaiForm(mode, payload = {}) {
        if (!pegawaiForm || !pegawaiModal) return;
        setActiveTab('pegawai');
        if (pegawaiContextField) pegawaiContextField.value = 'pegawai';

        if (mode === 'edit') {
            pegawaiForm.dataset.mode = 'edit';
            pegawaiForm.action = payload.update || pegawaiCreateAction || pegawaiForm.action;
            if (pegawaiMethodField) pegawaiMethodField.value = 'PUT';
            if (pegawaiTitle) pegawaiTitle.textContent = 'Perbarui Pegawai';
            if (pegawaiSubtitle) pegawaiSubtitle.textContent = 'Sesuaikan data aparatur desa.';
            if (pegawaiNikField) pegawaiNikField.value = payload.nik ?? '';
            if (pegawaiNameField) pegawaiNameField.value = payload.nama ?? '';
            if (pegawaiNipField) pegawaiNipField.value = payload.nip ?? '';
            if (pegawaiJabatanField) pegawaiJabatanField.value = payload.jabatan ?? '';
            if (pegawaiEmailField) pegawaiEmailField.value = payload.email ?? '';
            if (pegawaiPhoneField) pegawaiPhoneField.value = payload.nomorHp ?? '';
            if (pegawaiGenderField) setFancySelectValue(pegawaiGenderField, payload.jenisKelamin ?? 'Laki-laki');
            if (pegawaiTempatLahirField) pegawaiTempatLahirField.value = payload.tempatLahir ?? '';
            if (pegawaiTanggalLahirField) pegawaiTanggalLahirField.value = payload.tanggalLahir ?? '';
            if (pegawaiAgamaField) pegawaiAgamaField.value = payload.agama ?? '';
            if (pegawaiAlamatField) pegawaiAlamatField.value = payload.alamat ?? '';
            if (pegawaiStatusField) setFancySelectValue(pegawaiStatusField, payload.statusRaw ?? payload.status ?? 'Aktif');
            if (pegawaiMasaMulaiField) pegawaiMasaMulaiField.value = payload.masaJabatanMulai ?? '';
            if (pegawaiMasaSelesaiField) pegawaiMasaSelesaiField.value = payload.masaJabatanSelesai ?? '';
            if (pegawaiUniversitasField) pegawaiUniversitasField.value = payload.universitas ?? '';
            if (pegawaiPendidikanField) pegawaiPendidikanField.value = payload.pendidikanTerakhir ?? '';
            if (pegawaiTahunLulusField) pegawaiTahunLulusField.value = payload.tahunLulus ?? '';
            if (pegawaiBahasaField) pegawaiBahasaField.value = payload.bahasa ?? '';
            if (pegawaiSertifikatField) pegawaiSertifikatField.value = payload.sertifikatPelatihan ?? '';
            if (pegawaiLastLoginField) pegawaiLastLoginField.value = payload.lastLogin ?? '';
            if (pegawaiRemoveField) pegawaiRemoveField.value = '0';
            if (pegawaiAvatarHelpers) pegawaiAvatarHelpers.set(payload.avatar);
            setPegawaiMetadata({
                createdBy: payload.createdBy,
                updatedBy: payload.updatedBy,
                createdAt: payload.createdAtDisplay,
                updatedAt: payload.updatedAtDisplay,
                deletedAt: payload.deletedAtDisplay,
            });
            applyFileInfo(
                pegawaiCurrentKtp,
                pegawaiCurrentKtpLink,
                pegawaiRemoveKtpWrapper,
                pegawaiRemoveKtpCheckbox,
                payload.fotoKtpUrl,
                payload.fotoKtpName,
                'Lihat foto KTP'
            );
            applyFileInfo(
                pegawaiCurrentSkPengangkatan,
                pegawaiCurrentSkPengangkatanLink,
                pegawaiRemoveSkPengangkatanWrapper,
                pegawaiRemoveSkPengangkatanCheckbox,
                payload.skPengangkatanUrl,
                payload.skPengangkatanName,
                'Lihat dokumen'
            );
            applyFileInfo(
                pegawaiCurrentSkPemberhentian,
                pegawaiCurrentSkPemberhentianLink,
                pegawaiRemoveSkPemberhentianWrapper,
                pegawaiRemoveSkPemberhentianCheckbox,
                payload.skPemberhentianUrl,
                payload.skPemberhentianName,
                'Lihat dokumen'
            );
        } else {
            resetPegawaiForm();
        }

        openModal(pegawaiModal);
        window.requestAnimationFrame(() => {
            if (pegawaiNameField) {
                pegawaiNameField.focus();
            }
        });
    }

    document.querySelector('.js-create-admin')?.addEventListener('click', () => openAdminForm('create'));
    document.querySelectorAll('.js-edit-admin').forEach((button) => {
        button.addEventListener('click', () => openAdminForm('edit', button.dataset));
    });

    document.querySelector('.js-create-pegawai')?.addEventListener('click', () => openPegawaiForm('create'));
    document.querySelectorAll('.js-edit-pegawai').forEach((button) => {
        button.addEventListener('click', () => openPegawaiForm('edit', button.dataset));
    });

    // Filter functionality
    const filterDropdowns = document.querySelectorAll('[data-filter]');
    let openFilterKey = null;

    const closeFilterMenu = (key) => {
        if (!key) return;
        const toggle = document.querySelector(`[data-filter-toggle="${key}"]`);
        const menu = document.querySelector(`[data-filter-menu="${key}"]`);
        if (!toggle || !menu) return;
        menu.classList.remove('is-open');
        window.setTimeout(() => {
            menu.hidden = true;
        }, 160);
        toggle.setAttribute('aria-expanded', 'false');
        if (openFilterKey === key) {
            openFilterKey = null;
        }
    };

    const openFilterMenu = (key) => {
        const toggle = document.querySelector(`[data-filter-toggle="${key}"]`);
        const menu = document.querySelector(`[data-filter-menu="${key}"]`);
        if (!toggle || !menu) return;
        if (openFilterKey && openFilterKey !== key) {
            closeFilterMenu(openFilterKey);
        }
        menu.hidden = false;
        requestAnimationFrame(() => menu.classList.add('is-open'));
        toggle.setAttribute('aria-expanded', 'true');
        openFilterKey = key;
    };

    filterDropdowns.forEach((wrapper) => {
        const key = wrapper.dataset.filter;
        if (!key) return;

        const toggle = wrapper.querySelector('[data-filter-toggle]');
        const menu = wrapper.querySelector('[data-filter-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (openFilterKey === key) {
                closeFilterMenu(key);
            } else {
                openFilterMenu(key);
            }
        });

        menu.addEventListener('click', (event) => event.stopPropagation());

        const apply = menu.querySelector(`[data-filter-apply="${key}"]`);
        const reset = menu.querySelector(`[data-filter-reset="${key}"]`);
        const checkboxes = menu.querySelectorAll('[data-filter-field]');

        if (apply) {
            apply.addEventListener('click', () => {
                const filters = {};
                checkboxes.forEach((checkbox) => {
                    if (!checkbox.checked) return;
                    const field = checkbox.dataset.filterField;
                    if (!field) return;
                    if (!filters[field]) filters[field] = [];
                    filters[field].push(checkbox.value);
                });

                if (tableManagers[key]) {
                    tableManagers[key].setFilters(filters);
                }

                closeFilterMenu(key);
            });
        }

        if (reset) {
            reset.addEventListener('click', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
                if (tableManagers[key]) {
                    tableManagers[key].resetFilters();
                }
                closeFilterMenu(key);
            });
        }
    });

    document.addEventListener('click', (event) => {
        if (!openFilterKey) return;
        const menu = document.querySelector(`[data-filter-menu="${openFilterKey}"]`);
        const toggle = document.querySelector(`[data-filter-toggle="${openFilterKey}"]`);
        if (!menu || !toggle) return;
        if (!menu.contains(event.target) && !toggle.contains(event.target)) {
            closeFilterMenu(openFilterKey);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (openFilterKey) {
                closeFilterMenu(openFilterKey);
            }
            if (openExportKey) {
                closeExportMenu(openExportKey);
            }
        }
    });

    const detailModal = document.getElementById('detailModal');
    const detailTitle = document.getElementById('detailModalTitle');
    const detailSubtitle = document.getElementById('detailModalSubtitle');
    const detailAvatar = document.getElementById('detailModalAvatar');
    const detailName = document.getElementById('detailModalName');
    const detailStatus = document.getElementById('detailModalStatus');
    const detailEmail = document.getElementById('detailModalEmail');
    const detailPhone = document.getElementById('detailModalPhone');
            const detailInfoLabel = document.getElementById('detailModalInfoLabel');
            const detailInfoValue = document.getElementById('detailModalInfoValue');
            const detailMetaLabel = document.getElementById('detailModalMetaLabel');
            const detailMetaValue = document.getElementById('detailModalMetaValue');
            const detailLoginLocation = document.getElementById('detailModalLoginLocation');
            const detailLoginIp = document.getElementById('detailModalLoginIp');
            const detailSections = detailModal?.querySelector('[data-pegawai-detail]');
            const detailNik = document.getElementById('detailNik');
            const detailBirth = document.getElementById('detailBirth');
            const detailGender = document.getElementById('detailGender');
            const detailReligion = document.getElementById('detailReligion');
    const detailAddress = document.getElementById('detailAddress');
    const detailFotoKtp = document.getElementById('detailFotoKtp');
    const detailFotoKtpLink = detailFotoKtp?.querySelector('[data-file-link="ktp"]');
    const detailNip = document.getElementById('detailNip');
    const detailJob = document.getElementById('detailJob');
    const detailEmploymentStatus = document.getElementById('detailEmploymentStatus');
    const detailTenure = document.getElementById('detailTenure');
    const detailSkPengangkatan = document.getElementById('detailSkPengangkatan');
    const detailSkPengangkatanLink = detailSkPengangkatan?.querySelector('[data-file-link="sk-pengangkatan"]');
    const detailSkPemberhentian = document.getElementById('detailSkPemberhentian');
    const detailSkPemberhentianLink = detailSkPemberhentian?.querySelector('[data-file-link="sk-pemberhentian"]');
    const detailUniversity = document.getElementById('detailUniversity');
    const detailEducation = document.getElementById('detailEducation');
    const detailGraduationYear = document.getElementById('detailGraduationYear');
    const detailCertifications = document.getElementById('detailCertifications');
    const detailLanguages = document.getElementById('detailLanguages');
    const detailLastLogin = document.getElementById('detailLastLogin');
    const detailCreatedBy = document.getElementById('detailCreatedBy');
    const detailUpdatedBy = document.getElementById('detailUpdatedBy');
    const detailCreatedAt = document.getElementById('detailCreatedAt');
    const detailUpdatedAt = document.getElementById('detailUpdatedAt');
    const detailDeletedAt = document.getElementById('detailDeletedAt');

    const applyStatusClass = (element, statusClass) => {
        if (!element) return;
        element.classList.remove('status-active', 'status-draft', 'status-scheduled', 'status-pending');
        if (statusClass) {
            element.classList.add(statusClass);
        }
    };

    const openDetailModal = (type, payload = {}) => {
        if (!detailModal) return;
        const isPegawai = type === 'pegawai';
        if (detailSections) {
            detailSections.hidden = !isPegawai;
        }
        if (detailTitle) {
            detailTitle.textContent = type === 'admin' ? 'Detail Admin' : 'Detail Pegawai';
        }
        if (detailSubtitle) {
            detailSubtitle.textContent =
                type === 'admin'
                    ? 'Tinjau status akses admin terpilih.'
                    : 'Tinjau profil aparatur desa terpilih.';
        }
        if (detailAvatar) {
            detailAvatar.src = payload.avatar || detailAvatar.dataset.default || detailAvatar.src;
            detailAvatar.alt = payload.nama ? `Foto ${payload.nama}` : 'Avatar akun';
        }
        if (detailName) {
            detailName.textContent = payload.nama ?? '-';
        }
        if (detailStatus) {
            const rawStatus = payload.statusRaw ?? payload.status ?? '-';
            detailStatus.textContent = payload.status ?? '-';
            applyStatusClass(detailStatus, payload.statusClass);
            if (rawStatus && rawStatus !== '-') {
                detailStatus.setAttribute('title', rawStatus);
            } else {
                detailStatus.removeAttribute('title');
            }
        }
        if (detailEmail) {
            detailEmail.textContent = payload.email ?? '-';
        }
        if (detailPhone) {
            detailPhone.textContent = payload.nomorHp ?? '-';
        }
        if (detailInfoLabel) {
            detailInfoLabel.textContent = type === 'admin' ? 'Role' : 'Jabatan';
        }
        if (detailInfoValue) {
            if (type === 'admin') {
                const roleLabel = payload.role ?? '-';
                const superFlag = payload.isAdmin === '1' ? ' (Super Admin)' : '';
                detailInfoValue.textContent = `${roleLabel}${superFlag}`;
            } else {
                detailInfoValue.textContent = payload.jabatan ?? '-';
            }
        }
        if (detailMetaLabel) {
            detailMetaLabel.textContent = type === 'admin' ? 'Login Terakhir' : 'Pembaruan Data';
        }
        if (detailMetaValue) {
            if (type === 'admin') {
                const created = payload.created && payload.created !== '-' ? ` - Dibuat ${payload.created}` : '';
                detailMetaValue.textContent = (payload.lastLogin ?? '-') + created;
            } else {
                detailMetaValue.textContent = payload.updated ?? '-';
            }
        }
        if (detailLoginLocation) {
            detailLoginLocation.textContent = type === 'admin' ? payload.loginLocation ?? '-' : '-';
        }
        if (detailLoginIp) {
            detailLoginIp.textContent = type === 'admin' ? payload.loginIp ?? '-' : '-';
        }
                if (detailLoginLocation) {
                    detailLoginLocation.textContent = type === 'admin' ? payload.loginLocation ?? '-' : '-';
                }
                if (detailLoginIp) {
                    detailLoginIp.textContent = type === 'admin' ? payload.loginIp ?? '-' : '-';
                }
                if (isPegawai) {
                    const valueOrDash = (value, fallback = '-') => {
                        if (value === undefined || value === null) return fallback;
                        const text = String(value).trim();
                        return text !== '' ? text : fallback;
            };
            if (detailNik) detailNik.textContent = valueOrDash(payload.nik);
            if (detailBirth) {
                const parts = [];
                if (valueOrDash(payload.tempatLahir, '')) parts.push(valueOrDash(payload.tempatLahir, ''));
                if (valueOrDash(payload.tanggalLahirDisplay, '')) parts.push(valueOrDash(payload.tanggalLahirDisplay, ''));
                detailBirth.textContent = parts.length > 0 ? parts.join(', ') : '-';
            }
            if (detailGender) detailGender.textContent = valueOrDash(payload.jenisKelamin);
            if (detailReligion) detailReligion.textContent = valueOrDash(payload.agama);
            if (detailAddress) detailAddress.textContent = valueOrDash(payload.alamat);
            setDetailLink(detailFotoKtp, detailFotoKtpLink, payload.fotoKtpUrl, payload.fotoKtpName || 'Lihat foto KTP');
            if (detailNip) detailNip.textContent = valueOrDash(payload.nip);
            if (detailJob) detailJob.textContent = valueOrDash(payload.jabatan);
            if (detailEmploymentStatus) {
                detailEmploymentStatus.textContent = valueOrDash(payload.statusRaw ?? payload.status);
            }
            if (detailTenure) detailTenure.textContent = valueOrDash(payload.tenureDisplay);
            setDetailLink(
                detailSkPengangkatan,
                detailSkPengangkatanLink,
                payload.skPengangkatanUrl,
                payload.skPengangkatanName || 'Lihat dokumen'
            );
            setDetailLink(
                detailSkPemberhentian,
                detailSkPemberhentianLink,
                payload.skPemberhentianUrl,
                payload.skPemberhentianName || 'Lihat dokumen'
            );
            if (detailUniversity) detailUniversity.textContent = valueOrDash(payload.universitas);
            if (detailEducation) detailEducation.textContent = valueOrDash(payload.pendidikanTerakhir);
            if (detailGraduationYear) detailGraduationYear.textContent = valueOrDash(payload.tahunLulus);
            if (detailCertifications) detailCertifications.textContent = valueOrDash(payload.sertifikatPelatihan);
            if (detailLanguages) detailLanguages.textContent = valueOrDash(payload.bahasa);
            if (detailLastLogin) detailLastLogin.textContent = valueOrDash(payload.lastLoginDisplay);
            if (detailCreatedBy) detailCreatedBy.textContent = valueOrDash(payload.createdBy);
            if (detailUpdatedBy) detailUpdatedBy.textContent = valueOrDash(payload.updatedBy);
            if (detailCreatedAt) detailCreatedAt.textContent = valueOrDash(payload.createdAtDisplay);
            if (detailUpdatedAt) detailUpdatedAt.textContent = valueOrDash(payload.updatedAtDisplay);
            if (detailDeletedAt) detailDeletedAt.textContent = valueOrDash(payload.deletedAtDisplay, '-');
        }
        openModal(detailModal);
    };


    document.querySelectorAll('.js-view-admin').forEach((button) => {
        button.addEventListener('click', () => {
            openDetailModal('admin', {
                avatar: button.dataset.avatar,
                nama: button.dataset.nama,
                email: button.dataset.email,
                nomorHp: button.dataset.nomorHp,
                role: button.dataset.role,
                isAdmin: button.dataset.isAdmin,
                status: button.dataset.status,
                statusRaw: button.dataset.statusRaw || button.dataset.status,
                statusClass: button.dataset.statusClass,
                lastLogin: button.dataset.lastLogin,
                loginLocation: button.dataset.loginLocation,
                loginIp: button.dataset.loginIp,
                created: button.dataset.created,
            });
        });
    });

    document.querySelectorAll('.js-view-pegawai').forEach((button) => {
        button.addEventListener('click', () => {
            openDetailModal('pegawai', {
                avatar: button.dataset.avatar,
                nama: button.dataset.nama,
                nik: button.dataset.nik,
                nip: button.dataset.nip,
                jabatan: button.dataset.jabatan,
                email: button.dataset.email,
                nomorHp: button.dataset.nomorHp,
                jenisKelamin: button.dataset.jenisKelamin,
                tempatLahir: button.dataset.tempatLahir,
                tanggalLahir: button.dataset.tanggalLahir,
                tanggalLahirDisplay: button.dataset.tanggalLahirDisplay,
                agama: button.dataset.agama,
                alamat: button.dataset.alamat,
                status: button.dataset.status,
                statusRaw: button.dataset.statusRaw || button.dataset.status,
                statusClass: button.dataset.statusClass,
                masaJabatanMulai: button.dataset.masaJabatanMulai,
                masaJabatanMulaiDisplay: button.dataset.masaJabatanMulaiDisplay,
                masaJabatanSelesai: button.dataset.masaJabatanSelesai,
                masaJabatanSelesaiDisplay: button.dataset.masaJabatanSelesaiDisplay,
                tenureDisplay: button.dataset.tenureDisplay,
                universitas: button.dataset.universitas,
                pendidikanTerakhir: button.dataset.pendidikanTerakhir,
                tahunLulus: button.dataset.tahunLulus,
                sertifikatPelatihan: button.dataset.sertifikatPelatihan,
                bahasa: button.dataset.bahasa,
                fotoKtpUrl: button.dataset.fotoKtpUrl,
                fotoKtpName: button.dataset.fotoKtpName,
                skPengangkatanUrl: button.dataset.skPengangkatanUrl,
                skPengangkatanName: button.dataset.skPengangkatanName,
                skPemberhentianUrl: button.dataset.skPemberhentianUrl,
                skPemberhentianName: button.dataset.skPemberhentianName,
                lastLogin: button.dataset.lastLogin,
                lastLoginDisplay: button.dataset.lastLoginDisplay,
                createdBy: button.dataset.createdBy,
                updatedBy: button.dataset.updatedBy,
                createdAtDisplay: button.dataset.createdAtDisplay,
                updatedAtDisplay: button.dataset.updatedAtDisplay,
                deletedAtDisplay: button.dataset.deletedAtDisplay,
                updated: button.dataset.updated,
            });
        });
    });

    resetAdminForm();
    resetPegawaiForm();
});
