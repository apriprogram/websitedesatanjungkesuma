@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const closePanel = (picker) => {
                const panel = picker.querySelector('[data-kk-panel]');
                panel?.classList.remove('is-open');
                picker.classList.remove('is-open');
            };

            document.querySelectorAll('[data-kk-picker]').forEach((picker) => {
                const hiddenInput = picker.querySelector('[data-kk-value]');
                const displayInput = picker.querySelector('[data-kk-display]');
                const panel = picker.querySelector('[data-kk-panel]');
                const searchInput = picker.querySelector('[data-kk-search]');
                const options = Array.from(picker.querySelectorAll('[data-kk-option]'));
                const nativeValue = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');
                let isInternal = false;

                const setHiddenValue = (val) => {
                    if (nativeValue && nativeValue.set) {
                        nativeValue.set.call(hiddenInput, val);
                    } else {
                        hiddenInput.value = val;
                    }
                };

                const syncDisplay = (value) => {
                    displayInput.value = value || '';
                    options.forEach((btn) => {
                        btn.classList.toggle('is-active', btn.dataset.value === value);
                    });
                };

                const applySelection = (value) => {
                    isInternal = true;
                    setHiddenValue(value || '');
                    isInternal = false;
                    syncDisplay(value || '');
                };

                if (nativeValue && nativeValue.set && nativeValue.get) {
                    Object.defineProperty(hiddenInput, 'value', {
                        get() {
                            return nativeValue.get.call(hiddenInput);
                        },
                        set(val) {
                            setHiddenValue(val);
                            if (!isInternal) {
                                syncDisplay(val || '');
                            }
                        },
                    });
                }

                const filterOptions = (term) => {
                    const q = term.trim().toLowerCase();
                    options.forEach((btn) => {
                        const match = btn.dataset.value.toLowerCase().includes(q);
                        btn.classList.toggle('is-hidden', !match);
                    });
                };

                const openPanel = () => {
                    applySelection(hiddenInput.value);
                    panel.classList.add('is-open');
                    picker.classList.add('is-open');
                    searchInput.value = '';
                    filterOptions('');
                    setTimeout(() => searchInput.focus(), 10);
                };

                displayInput.addEventListener('click', () => {
                    if (panel.classList.contains('is-open')) {
                        closePanel(picker);
                    } else {
                        openPanel();
                    }
                });

                searchInput.addEventListener('input', (e) => {
                    filterOptions(e.target.value);
                });

                options.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        applySelection(btn.dataset.value);
                        closePanel(picker);
                    });
                });

                document.addEventListener('click', (e) => {
                    if (!picker.contains(e.target)) {
                        closePanel(picker);
                    }
                });

                // Initialize selected value
                applySelection(hiddenInput.value);
            });
        });
    </script>
@endpush
