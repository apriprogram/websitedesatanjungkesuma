document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('[data-toggle-password]');
    const form = document.getElementById('adminLoginForm');
    const passwordInput = document.getElementById('password');
    const stylableInputs = document.querySelectorAll('.modern-input');
    const typingTimers = new WeakMap();
    const passwordHints = passwordInput
        ? passwordInput.closest('.form-field').querySelectorAll('[data-rule]')
        : [];
    const rememberCheckbox = document.getElementById('remember');
    const rememberEmailKey = 'adminLoginRememberEmail';
    const rememberFlagKey = 'adminLoginRememberChecked';
    const rememberTargetInput = document.getElementById('email');
    const rememberStorage =
        typeof window !== 'undefined' && 'localStorage' in window ? window.localStorage : null;

    const rules = {
        length: (value) => value.length >= 8,
        'upper-lower': (value) => /[A-Z]/.test(value) && /[a-z]/.test(value),
        number: (value) => /\d/.test(value),
        symbol: (value) => /[^A-Za-z0-9]/.test(value),
    };

    const syncInputVisualState = (input) => {
        if (!input) return;
        const value = (input.value || '').trim();
        const hasValue = value.length > 0;
        input.classList.toggle('is-entered', hasValue);
        const confirmOnValid = input.dataset.confirmOnValid === 'true';
        const canConfirm = confirmOnValid && typeof input.checkValidity === 'function';
        const isConfirmed = canConfirm && hasValue && input.checkValidity();
        input.classList.toggle('is-confirmed', !!isConfirmed);
        input.classList.toggle('is-disabled', input.disabled);
        const isErrored = input.getAttribute('aria-invalid') === 'true';
        input.classList.toggle('is-error', isErrored);
    };

    stylableInputs.forEach((input) => {
        if (input.type === 'email') {
            input.dataset.confirmOnValid = 'true';
        }

        syncInputVisualState(input);

        input.addEventListener('focus', () => {
            input.classList.add('is-focused');
        });

        input.addEventListener('blur', () => {
            input.classList.remove('is-focused');
            input.classList.remove('is-typing');
            syncInputVisualState(input);
        });

        input.addEventListener('input', () => {
            const parentField = input.closest('.form-field');
            parentField?.classList.remove('has-error');

            const feedback = parentField?.querySelector('.field-feedback');
            feedback?.removeAttribute('data-dismissed');

            if (input.dataset.serverError === 'true') {
                input.dataset.serverError = 'false';
                input.removeAttribute('aria-invalid');
                feedback?.setAttribute('data-dismissed', 'true');
            }

            clearTimeout(typingTimers.get(input));
            input.classList.add('is-typing');
            typingTimers.set(
                input,
                setTimeout(() => input.classList.remove('is-typing'), 500),
            );

            syncInputVisualState(input);
        });

        input.addEventListener('change', () => syncInputVisualState(input));
    });

    const syncPasswordState = () => {
        if (!passwordInput) return true;
        const value = passwordInput.value || '';
        let passedAll = true;
        passwordHints.forEach((item) => {
            const key = item.dataset.rule;
            const validator = rules[key];
            const passed = validator ? validator(value) : true;
            passedAll = passedAll && passed;
            item.classList.toggle('is-valid', passed);
        });
        if (!passedAll) {
            passwordInput.setCustomValidity('Password belum memenuhi persyaratan keamanan.');
            passwordInput.setAttribute('aria-invalid', 'true');
        } else {
            passwordInput.setCustomValidity('');
            passwordInput.removeAttribute('aria-invalid');
        }
        syncInputVisualState(passwordInput);
        return passedAll;
    };

    if (passwordInput) {
        passwordInput.addEventListener('input', syncPasswordState);
        passwordInput.addEventListener('blur', syncPasswordState);
        syncPasswordState();
    }

    if (form) {
        form.addEventListener('submit', (event) => {
            if (!syncPasswordState()) {
                event.preventDefault();
                passwordInput?.reportValidity?.();
            }
        });
    }

    const persistRememberedState = () => {
        if (!rememberCheckbox || !rememberTargetInput) return;
        if (!rememberStorage) return;
        if (rememberCheckbox.checked) {
            rememberStorage.setItem(rememberFlagKey, 'true');
            rememberStorage.setItem(rememberEmailKey, rememberTargetInput.value || '');
        } else {
            rememberStorage.removeItem(rememberEmailKey);
            rememberStorage.removeItem(rememberFlagKey);
        }
    };

    if (rememberCheckbox && rememberTargetInput && rememberStorage) {
        const storedFlag = rememberStorage.getItem(rememberFlagKey) === 'true';
        const storedEmail = rememberStorage.getItem(rememberEmailKey);

        if (storedFlag) {
            rememberCheckbox.checked = true;
            if (!rememberTargetInput.value && storedEmail) {
                rememberTargetInput.value = storedEmail;
                rememberTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        rememberCheckbox.addEventListener('change', () => {
            persistRememberedState();
        });

        rememberTargetInput.addEventListener('input', () => {
            if (rememberCheckbox.checked) {
                rememberStorage.setItem(rememberEmailKey, rememberTargetInput.value || '');
            }
        });

        form?.addEventListener('submit', persistRememberedState);
    }

    toggles.forEach((toggle) => {
        const targetId = toggle.getAttribute('data-toggle-password');
        const input = document.getElementById(targetId);
        if (!input) return;

        toggle.addEventListener('click', () => {
            const isHidden = input.getAttribute('type') === 'password';
            input.setAttribute('type', isHidden ? 'text' : 'password');
            toggle.classList.toggle('is-visible', isHidden);
        });
    });
});
