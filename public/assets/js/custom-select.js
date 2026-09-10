/**
 * Custom Select Component
 * Mengubah native <select class="cs-native"> menjadi custom dropdown panel.
 * Dapat dipanggil kembali untuk halaman lain: CustomSelect.init()
 *
 * Cara pakai:
 *   <div class="cs-wrapper" data-cs-label="Pilih...">
 *     <select class="cs-native" name="field">
 *       <option value="">Semua</option>
 *       <option value="a">Pilihan A</option>
 *     </select>
 *   </div>
 *
 * Kemudian panggil: CustomSelect.init()
 */
const CustomSelect = (() => {
    const CLOSE_MS = 180;

    function buildUI(wrapper) {
        const native = wrapper.querySelector('select.cs-native');
        if (!native || wrapper.dataset.csInit) return;
        wrapper.dataset.csInit = '1';

        const label = wrapper.dataset.csLabel || 'Pilih...';
        const options = Array.from(native.options);

        /* ── Trigger Button ── */
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'cs-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const textSpan = document.createElement('span');
        textSpan.className = 'cs-trigger__text';

        const iconSpan = document.createElement('span');
        iconSpan.className = 'cs-trigger__icon';
        iconSpan.innerHTML = '<i class="fas fa-chevron-down"></i>';

        trigger.appendChild(textSpan);
        trigger.appendChild(iconSpan);

        /* ── Panel ── */
        const panel = document.createElement('div');
        panel.className = 'cs-panel';
        panel.setAttribute('role', 'listbox');

        /* Options list */
        const ul = document.createElement('ul');
        ul.className = 'cs-options';

        function renderOptions(selectedVal) {
            ul.innerHTML = '';
            options.forEach(opt => {
                const li = document.createElement('li');
                li.className = 'cs-option' + (opt.value === selectedVal ? ' cs-selected' : '');
                li.setAttribute('role', 'option');
                li.setAttribute('aria-selected', opt.value === selectedVal ? 'true' : 'false');
                li.dataset.value = opt.value;

                const labelSpan = document.createElement('span');
                labelSpan.className = 'cs-option__label';
                labelSpan.textContent = opt.text.trim();

                const checkSpan = document.createElement('span');
                checkSpan.className = 'cs-option__check';
                checkSpan.innerHTML = '<i class="fas fa-check"></i>';

                li.appendChild(labelSpan);
                li.appendChild(checkSpan);
                ul.appendChild(li);

                li.addEventListener('click', () => {
                    native.value = opt.value;
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                    textSpan.textContent = opt.text.trim() || label;
                    renderOptions(opt.value);
                    close();
                });
            });
        }

        /* Sync trigger text ke nilai awal */
        function syncTrigger() {
            const sel = native.options[native.selectedIndex];
            textSpan.textContent = sel ? sel.text.trim() : label;
        }

        panel.appendChild(ul);

        wrapper.insertBefore(trigger, native);
        wrapper.appendChild(panel);

        /* ── Open / Close ── */
        function open() {
            // Tutup semua cs-wrapper lain
            document.querySelectorAll('.cs-wrapper.cs-open').forEach(w => {
                if (w !== wrapper) closeWrapper(w);
            });
            const sel = native.value;
            renderOptions(sel);
            wrapper.classList.remove('cs-closing');
            wrapper.classList.add('cs-open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function close() {
            wrapper.classList.add('cs-closing');
            wrapper.classList.remove('cs-open');
            trigger.setAttribute('aria-expanded', 'false');
            setTimeout(() => wrapper.classList.remove('cs-closing'), CLOSE_MS);
        }

        function closeWrapper(w) {
            w.classList.add('cs-closing');
            w.classList.remove('cs-open');
            w.querySelector('.cs-trigger')?.setAttribute('aria-expanded', 'false');
            setTimeout(() => w.classList.remove('cs-closing'), CLOSE_MS);
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            wrapper.classList.contains('cs-open') ? close() : open();
        });

        /* Keyboard nav */
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); wrapper.classList.contains('cs-open') ? close() : open(); }
            if (e.key === 'Escape') close();
        });

        /* Sync jika native berubah secara programatis */
        native.addEventListener('change', syncTrigger);

        syncTrigger();
    }

    /* Tutup semua ketika klik di luar */
    document.addEventListener('click', () => {
        document.querySelectorAll('.cs-wrapper.cs-open').forEach(w => {
            w.classList.add('cs-closing');
            w.classList.remove('cs-open');
            w.querySelector('.cs-trigger')?.setAttribute('aria-expanded', 'false');
            setTimeout(() => w.classList.remove('cs-closing'), 180);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.cs-wrapper.cs-open').forEach(w => {
                w.classList.add('cs-closing');
                w.classList.remove('cs-open');
                setTimeout(() => w.classList.remove('cs-closing'), 180);
            });
        }
    });

    function init(root = document) {
        root.querySelectorAll('.cs-wrapper').forEach(buildUI);
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => CustomSelect.init());
