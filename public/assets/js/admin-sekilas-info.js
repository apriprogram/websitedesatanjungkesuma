document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('infoModal');
    const form = document.getElementById('infoForm');
    const methodInput = document.getElementById('formMethod');
    const titleEl = document.getElementById('modalTitle');
    const fieldTitle = document.getElementById('fieldTitle');
    const fieldContent = document.getElementById('fieldContent');
    const fieldSort = document.getElementById('fieldSort');
    const fieldPublished = document.getElementById('fieldPublished');
    const fieldActive = document.getElementById('fieldActive');

    const openBtn = document.getElementById('openCreateModal');
    const closeTriggers = modal ? modal.querySelectorAll('[data-close]') : [];

    const formatDateTimeLocal = (value) => {
        if (!value) return '';
        const date = new Date(value);
        if (isNaN(date.getTime())) return '';
        const pad = (n) => n.toString().padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    const openModal = () => {
        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        form.reset();
        form.action = form.dataset.createUrl || form.action;
        methodInput.value = 'POST';
        titleEl.textContent = 'Tambah Sekilas Info';
    };

    if (form) {
        form.dataset.createUrl = form.action;
    }

    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
            openModal();
        });
    }

    closeTriggers.forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    document.querySelectorAll('.js-edit').forEach((button) => {
        button.addEventListener('click', () => {
            const { title, content, sort, published, active, updateUrl } = button.dataset;
            form.action = updateUrl;
            methodInput.value = 'PUT';
            titleEl.textContent = 'Edit Sekilas Info';
            fieldTitle.value = title || '';
            fieldContent.value = content || '';
            fieldSort.value = sort || 0;
            fieldPublished.value = formatDateTimeLocal(published);
            fieldActive.checked = active === '1';
            openModal();
        });
    });
});
