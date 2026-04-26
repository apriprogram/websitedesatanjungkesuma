// Form handling functions
function resetAdminForm() {
    const form = document.querySelector('#adminForm');
    if (form) {
        form.reset();
        // Reset image preview if exists
        const previewImg = form.querySelector('.image-preview');
        if (previewImg) {
            previewImg.src = previewImg.getAttribute('data-default') || '/assets/default/user.jpg';
        }
    }
}

function resetPegawaiForm() {
    const form = document.querySelector('#pegawaiForm');
    if (form) {
        form.reset();
        // Reset image preview if exists
        const previewImg = form.querySelector('.image-preview');
        if (previewImg) {
            previewImg.src = previewImg.getAttribute('data-default') || '/assets/default/user.jpg';
        }
    }
}

// Image preview handling
function setupImagePreview(inputSelector, previewSelector) {
    const input = document.querySelector(inputSelector);
    const preview = document.querySelector(previewSelector);
    
    if (input && preview) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = preview.getAttribute('data-default') || '/assets/default/user.jpg';
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Setup image previews
    setupImagePreview('#adminImageInput', '#adminImagePreview');
    setupImagePreview('#pegawaiImageInput', '#pegawaiImagePreview');

    // Setup form reset handlers
    const adminForm = document.querySelector('#adminForm');
    if (adminForm) {
        adminForm.addEventListener('reset', resetAdminForm);
    }

    const pegawaiForm = document.querySelector('#pegawaiForm');
    if (pegawaiForm) {
        pegawaiForm.addEventListener('reset', resetPegawaiForm);
    }
});