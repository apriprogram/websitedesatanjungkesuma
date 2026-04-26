// Image preview handling functions
function setupImagePreview(fileInput, previewImage, defaultImage) {
    if (!fileInput || !previewImage) return;

    const handleFileSelect = (e) => {
        const file = e.target.files[0];
        if (!file) {
            previewImage.src = defaultImage;
            return;
        }

        // Check if file is an image
        if (!file.type.startsWith('image/')) {
            console.error('Selected file is not an image');
            previewImage.src = defaultImage;
            return;
        }

        // Create object URL for preview
        const objectUrl = URL.createObjectURL(file);
        previewImage.src = objectUrl;

        // Clean up object URL after image loads
        previewImage.onload = () => URL.revokeObjectURL(objectUrl);
    };

    fileInput.addEventListener('change', handleFileSelect);
}

function setupAvatarPreview(formSelector, imageInputName, previewId, removeButtonSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    const fileInput = form.querySelector(`input[name="${imageInputName}"]`);
    const previewImage = document.getElementById(previewId);
    const removeButton = document.querySelector(removeButtonSelector);
    const defaultImage = previewImage?.dataset?.default || previewImage?.src;

    if (!fileInput || !previewImage) return;

    // Setup file input change handler
    setupImagePreview(fileInput, previewImage, defaultImage);

    // Setup remove button handler
    if (removeButton) {
        removeButton.addEventListener('click', () => {
            fileInput.value = '';
            previewImage.src = defaultImage;
            const removeField = form.querySelector('input[name="remove_gambar"]');
            if (removeField) {
                removeField.value = '1';
            }
        });
    }
}

// Initialize preview handlers for both user and pegawai forms
document.addEventListener('DOMContentLoaded', () => {
    // Setup for admin/user form
    setupAvatarPreview(
        '#adminForm',
        'gambar',
        'userAvatarPreview',
        '[data-action="remove-user-photo"]'
    );

    // Setup for pegawai form
    setupAvatarPreview(
        '#pegawaiForm',
        'gambar',
        'pegawaiAvatarPreview',
        '[data-action="remove-pegawai-photo"]'
    );
});