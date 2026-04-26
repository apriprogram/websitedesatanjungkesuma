/**
 * Accessibility Widget Logic
 * Handles contrast, text size, and other a11y features.
 */

document.addEventListener('DOMContentLoaded', function () {
    const a11yState = JSON.parse(localStorage.getItem('a11yState')) || {};
    const html = document.documentElement;
    const body = document.body;

    // -- Feature Toggles -- (moved before applySettings to avoid hoisting error)
    const toggles = {
        'invert': 'a11y-invert',
        'contrast-dark': 'a11y-contrast-dark',
        'contrast-light': 'a11y-contrast-light',
        'highlight-links': 'a11y-highlight-links',
        'big-text': 'a11y-big-text',
        'text-spacing': 'a11y-text-spacing',
        'reduce-motion': 'a11y-reduce-motion',
        'hide-images': 'a11y-hide-images',
        'dyslexia': 'a11y-dyslexia',
        'cursor': 'a11y-cursor',
        'narrator': 'a11y-narrator', // Placeholder for screen reader logic if needed
        'line-height': 'a11y-line-height',
        'text-align': 'a11y-text-align',
        'saturation': 'a11y-saturation',
    };

    // Apply saved settings
    applySettings();

    Object.keys(toggles).forEach(key => {
        const btn = document.getElementById(`feat-${key}`);
        if (!btn) return;

        // Set initial state based on saved settings
        if (a11yState[key]) {
            btn.setAttribute('aria-pressed', 'true');
            btn.classList.add('active');
        }

        btn.addEventListener('click', () => {
            const isActive = btn.getAttribute('aria-pressed') === 'true';
            const newState = !isActive;
            
            btn.setAttribute('aria-pressed', newState);
            btn.classList.toggle('active', newState);
            
            a11yState[key] = newState;
            saveSettings();
            applySettings();
        });
    });

    // -- Text Scaling --
    let textScale = a11yState.textScale || 100;
    const scaleTextEl = document.getElementById('a11yScaleText');
    const btnInc = document.getElementById('a11yInc');
    const btnDec = document.getElementById('a11yDec');
    const btnReset = document.getElementById('a11yReset');

    function updateTextScale() {
        if (scaleTextEl) scaleTextEl.textContent = `${textScale}%`;
        html.style.setProperty('--a11y-font-scale', textScale / 100);
        a11yState.textScale = textScale;
        saveSettings();
    }

    if (btnInc) {
        btnInc.addEventListener('click', () => {
            if (textScale < 200) {
                textScale += 10;
                updateTextScale();
            }
        });
    }

    if (btnDec) {
        btnDec.addEventListener('click', () => {
            if (textScale > 50) {
                textScale -= 10;
                updateTextScale();
            }
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', () => {
            textScale = 100;
            updateTextScale();
        });
    }
    
    // Initialize scale
    updateTextScale();


    // -- Reset All --
    const btnResetAll = document.getElementById('a11yResetAll');
    if (btnResetAll) {
        btnResetAll.addEventListener('click', () => {
            localStorage.removeItem('a11yState');
            // Reload to clear everything cleanly
            window.location.reload();
        });
    }

    // -- Helper Functions --

    function saveSettings() {
        localStorage.setItem('a11yState', JSON.stringify(a11yState));
    }

    function applySettings() {
        // Apply classes to HTML tag for global effect
        Object.keys(toggles).forEach(key => {
            const className = toggles[key];
            if (a11yState[key]) {
                html.classList.add(className);
            } else {
                html.classList.remove(className);
            }
        });

        // Specific logic needed for exclusive contrasts?
        // Usually contrast-dark and contrast-light shouldn't look good together, 
        // but CSS cascade will handle it (last one wins) or we can enforce mutual exclusion.
        if (a11yState['contrast-dark'] && a11yState['contrast-light']) {
             // Let user decide, or auto-disable one. For now, simple toggles are fine.
        }
    }
});
