// Image handler utility
const ImageHandler = {
    defaultUserImage: '/storage/default/user.jpg',
    defaultPegawaiImage: '/storage/default/user.jpg',
    fallbackImages: [
        '/storage/default/user.jpg',
        '/assets/img/Users/default.jpg',
        '/default/user.jpg',
        '/img/default-user.jpg',
        '/assets/default-avatar.png'
    ],

    /**
     * Handle image error by trying fallback images
     * @param {HTMLImageElement} img - The image element that failed to load
     * @param {number} attempt - Current attempt number (internal use)
     */
    handleImageError: function(img, attempt = 0) {
        if (attempt >= this.fallbackImages.length) {
            // If all fallbacks fail, use data URI for a basic avatar
            img.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzc4NzY3NiI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';
            return;
        }
        img.src = this.fallbackImages[attempt];
        img.onerror = () => this.handleImageError(img, attempt + 1);
    },

    /**
     * Setup image with proper error handling
     * @param {HTMLImageElement} img - The image element to setup
     * @param {string} src - The primary image source to try
     * @param {string} alt - Alt text for the image
     * @param {string} defaultImage - Default image to use if src fails
     */
    setupImage: function(img, src, alt = '', defaultImage = this.defaultUserImage) {
        img.alt = alt;
        img.setAttribute('data-original-src', src);
        img.setAttribute('data-default-src', defaultImage);
        
        // Add loading animation class
        img.classList.add('loading-image');
        
        // Set initial source
        img.src = src;
        
        // Setup error handling
        img.onerror = () => {
            // Remove loading animation
            img.classList.remove('loading-image');
            // Try default image first
            img.src = defaultImage;
            // If default fails, go through fallbacks
            img.onerror = () => this.handleImageError(img);
        };
        
        // On successful load
        img.onload = () => {
            img.classList.remove('loading-image');
        };
    },

    /**
     * Initialize all profile images in a container
     * @param {string} containerSelector - CSS selector for container
     * @param {string} imageSelector - CSS selector for images within container
     */
    initializeProfileImages: function(containerSelector = 'body', imageSelector = '.profile-image') {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        container.querySelectorAll(imageSelector).forEach(img => {
            const src = img.getAttribute('data-src') || img.src;
            const alt = img.getAttribute('alt') || 'Profile Image';
            const defaultImage = img.getAttribute('data-default') || this.defaultUserImage;
            this.setupImage(img, src, alt, defaultImage);
        });
    }
};

// Add CSS for loading animation
const style = document.createElement('style');
style.textContent = `
    .loading-image {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }
    .profile-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
`;
document.head.appendChild(style);

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', () => {
    ImageHandler.initializeProfileImages();
});

// Export for module usage
window.ImageHandler = ImageHandler;
