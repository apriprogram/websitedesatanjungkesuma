document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    const focusFirstElement = (root) => {
        const element = root?.querySelector(
            'button:not([disabled]), a[href], input, select, textarea'
        );
        element?.focus({ preventScroll: true });
    };

    const syncBodyState = () => {
        const visibleModal = document.querySelector('.dialog-backdrop.is-visible');
        if (visibleModal) {
            body.classList.add('modal-open');
        } else {
            body.classList.remove('modal-open');
        }
    };

    const openModal = (modal) => {
        if (!modal) {
            return;
        }

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        syncBodyState();
        requestAnimationFrame(() => focusFirstElement(modal));
    };

    const closeModal = (modal) => {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        syncBodyState();
    };

    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modal = document.getElementById(trigger.dataset.modalOpen);
            openModal(modal);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modal = trigger.closest('.dialog-backdrop');
            closeModal(modal);
        });
    });



    const modalToOpen = window.__entityModalToOpen ?? null;
    if (modalToOpen) {
        const modal = document.getElementById(modalToOpen);
        if (modal) {
            openModal(modal);
        }
    }

    const enhancedSelects = document.querySelectorAll(
        '.entries-control select, .resident-filter select'
    );

    enhancedSelects.forEach((select) => {
        const openClass = 'is-open';
        select.addEventListener('focus', () => select.classList.add(openClass));
        select.addEventListener('blur', () => select.classList.remove(openClass));
        select.addEventListener('change', () => select.classList.add(openClass));
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.entries-control') && !event.target.closest('.resident-filter')) {
            enhancedSelects.forEach((select) => select.classList.remove('is-open'));
        }
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
});
