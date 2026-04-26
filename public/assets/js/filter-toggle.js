// Filter Toggle for Mobile
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('residentFilterForm');
    const filterLabel = filterForm?.querySelector('.resident-filter-bar__label');
    
    if (filterForm && filterLabel && window.innerWidth <= 1024) {
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'filter-toggle-btn';
        toggleBtn.setAttribute('aria-label', 'Toggle filter');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.innerHTML = `
            <i class="fas fa-filter"></i>
            <span>Filter</span>
            <i class="fas fa-chevron-down filter-toggle-icon"></i>
        `;
        
        // Insert button before filter form
        filterForm.parentNode.insertBefore(toggleBtn, filterForm);
        
        // Add click handler
        toggleBtn.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            filterForm.classList.toggle('filter-expanded');
            
            // Rotate chevron icon
            const chevron = this.querySelector('.filter-toggle-icon');
            if (chevron) {
                chevron.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
        
        // Initially hide filter on mobile
        if (window.innerWidth <= 1024) {
            filterForm.classList.add('filter-collapsed');
        }
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const toggleBtn = document.querySelector('.filter-toggle-btn');
        if (window.innerWidth > 1024 && toggleBtn) {
            filterForm?.classList.remove('filter-collapsed', 'filter-expanded');
        } else if (window.innerWidth <= 1024 && !filterForm?.classList.contains('filter-expanded')) {
            filterForm?.classList.add('filter-collapsed');
        }
    });
});
