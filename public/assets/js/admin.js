document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const metricCards = document.querySelectorAll('.metric-card');
  const tableRows = Array.from(document.querySelectorAll('.table-row'));
  const tabButtons = document.querySelectorAll('.tab-btn');
  const searchInput = document.getElementById('productSearch');
  const darkModeSwitch = document.getElementById('darkModeSwitch');
  const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
  const sidebarExpandBtn = document.getElementById('sidebarExpandBtn');
  const sidebar = document.querySelector('.admin-sidebar');
  const exportBtn = document.getElementById('exportBtn');
  const profileDropdowns = Array.from(document.querySelectorAll('.profile-dropdown'))
    .map((dropdown) => {
      const toggle = dropdown.querySelector('#profileMenuToggle') || dropdown.querySelector('.header-avatar');
      const menu = dropdown.querySelector('.profile-menu');
      return { dropdown, toggle, menu };
    })
    .filter(({ dropdown, toggle, menu }) => dropdown && toggle && menu);
  const notificationDropdown = document.querySelector('.notification-dropdown');
  const notificationToggle = document.getElementById('notificationToggle');
  const notificationMenu = notificationDropdown?.querySelector('.notification-menu');
  const markAllReadBtn = document.getElementById('markAllRead');

  const submenuToggles = document.querySelectorAll('.nav-item.has-children > .nav-link-toggle');

  const openSubmenu = (submenu) => {
    const targetHeight = submenu.scrollHeight;
    submenu.style.height = `${targetHeight}px`;
    submenu.style.opacity = '1';
    const onTransitionEnd = (event) => {
      if (event.propertyName !== 'height') return;
      submenu.style.height = 'auto';
    };
    submenu.addEventListener('transitionend', onTransitionEnd, { once: true });
  };

  const closeSubmenu = (submenu) => {
    if (submenu.style.height === 'auto' || !submenu.style.height) {
      submenu.style.height = `${submenu.scrollHeight}px`;
    }
    requestAnimationFrame(() => {
      submenu.style.height = '0px';
      submenu.style.opacity = '0';
    });
    const onTransitionEnd = (event) => {
      if (event.propertyName !== 'height') return;
      submenu.style.height = '0px';
    };
    submenu.addEventListener('transitionend', onTransitionEnd, { once: true });
  };

  const prepareSubmenu = (parent, submenu, toggle) => {
    parent.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    submenu.setAttribute('aria-hidden', 'true');
    submenu.style.display = 'grid';
    submenu.style.height = '0px';
    submenu.style.opacity = '0';
  };

  const closeAllSubmenus = (exceptionToggle) => {
    submenuToggles.forEach((toggle) => {
      if (toggle === exceptionToggle) return;
      const parent = toggle.closest('.nav-item.has-children');
      const submenu = parent?.querySelector('.nav-submenu');
      if (!parent || !submenu) return;
      if (parent.classList.contains('is-open')) {
        closeSubmenu(submenu);
      }
      parent.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      submenu.setAttribute('aria-hidden', 'true');
    });
  };

  const overlayMq = window.matchMedia('(max-width: 1100px)');
  const isOverlayMode = () => overlayMq.matches;
  let restoreHiddenOnDesktop = false;

  const syncSidebarMode = () => {
    if (isOverlayMode()) {
      restoreHiddenOnDesktop = body.classList.contains('sidebar-hidden');
      body.classList.remove('sidebar-hidden');
    } else {
      body.classList.remove('sidebar-open');
      if (restoreHiddenOnDesktop) {
        body.classList.add('sidebar-hidden');
        restoreHiddenOnDesktop = false;
      }
    }
  };

  const showSidebar = () => {
    body.classList.remove('sidebar-hidden');
    if (isOverlayMode()) {
      body.classList.add('sidebar-open');
    } else {
      restoreHiddenOnDesktop = false;
    }
  };

  const hideSidebar = () => {
    body.classList.remove('sidebar-open');
    if (!isOverlayMode()) {
      body.classList.add('sidebar-hidden');
      restoreHiddenOnDesktop = true;
    }
    closeAllSubmenus();
    closeProfileMenu();
    closeNotificationMenu();
  };

  const toggleSidebarFromTrigger = () => {
    if (isOverlayMode()) {
      const isOpen = body.classList.toggle('sidebar-open');
      if (!isOpen) {
        closeAllSubmenus();
        closeProfileMenu();
        closeNotificationMenu();
      }
    } else if (body.classList.contains('sidebar-hidden')) {
      showSidebar();
    } else {
      hideSidebar();
    }
  };

  syncSidebarMode();
  if (typeof overlayMq.addEventListener === 'function') {
    overlayMq.addEventListener('change', syncSidebarMode);
  } else if (typeof overlayMq.addListener === 'function') {
    overlayMq.addListener(syncSidebarMode);
  }

  const filterTable = ({ status, query }) => {
    let visible = 0;
    const term = (query || '').trim().toLowerCase();
    tableRows.forEach((row) => {
      const matchesStatus = status === 'all' || row.dataset.status === status;
      const matchesQuery = !term || (row.dataset.name || '').toLowerCase().includes(term);
      const show = matchesStatus && matchesQuery;
      row.style.display = show ? '' : 'none';
      if (show) visible += 1;
    });
    const footerCount = document.querySelector('.table-footer span');
    if (footerCount) footerCount.textContent = `${visible} hasil`;
  };

  let currentStatus = 'all';
  filterTable({ status: currentStatus, query: '' });

  tabButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      tabButtons.forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      currentStatus = btn.dataset.tab || 'all';
      filterTable({ status: currentStatus, query: searchInput?.value || '' });
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', (event) => {
      filterTable({ status: currentStatus, query: event.target.value });
    });
  }

  tableRows.forEach((row) => {
    row.addEventListener('pointerenter', () => row.classList.add('is-hovered'));
    row.addEventListener('pointerleave', () => row.classList.remove('is-hovered'));
  });

  // Persist dark mode across page navigation within the same tab (cleared on manual reload)
  const navEntry = performance.getEntriesByType('navigation')[0];
  const isReload = navEntry ? navEntry.type === 'reload' : performance.navigation.type === 1;
  const themeKey = 'admin-theme';
  if (isReload) {
    sessionStorage.removeItem(themeKey);
  }

  if (darkModeSwitch) {
    const setDark = (on, persist = true) => {
      body.classList.toggle('dark-mode', !!on);
      if (persist) {
        if (on) {
          sessionStorage.setItem(themeKey, 'dark');
        } else {
          sessionStorage.removeItem(themeKey);
        }
      }
    };

    const savedTheme = sessionStorage.getItem(themeKey);
    if (savedTheme === 'dark') {
      darkModeSwitch.checked = true;
    }
    setDark(darkModeSwitch.checked, false);

    darkModeSwitch.addEventListener('change', () => setDark(darkModeSwitch.checked));
  } else {
    // Apply saved theme even when switch tidak tersedia
    if (sessionStorage.getItem(themeKey) === 'dark') {
      body.classList.add('dark-mode');
    }
  }

  sidebarCollapseBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    hideSidebar();
    closeProfileMenu();
  });

  sidebarExpandBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    toggleSidebarFromTrigger();
  });

  submenuToggles.forEach((toggle) => {
    const parent = toggle.closest('.nav-item.has-children');
    const submenu = parent?.querySelector('.nav-submenu');
    if (!parent || !submenu) return;

    const shouldInitiallyOpen =
      parent.dataset.initialOpen === 'true' ||
      parent.classList.contains('is-open') ||
      !!parent.querySelector('.nav-submenu a.is-active');

    prepareSubmenu(parent, submenu, toggle);

    if (shouldInitiallyOpen) {
      parent.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      submenu.setAttribute('aria-hidden', 'false');
      submenu.style.height = 'auto';
      submenu.style.opacity = '1';
    }

    toggle.addEventListener('click', (event) => {
      event.preventDefault();
      const isOpen = parent.classList.contains('is-open');
      if (isOpen) {
        parent.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        submenu.setAttribute('aria-hidden', 'true');
        closeSubmenu(submenu);
      } else {
        closeAllSubmenus(toggle);
        parent.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        submenu.setAttribute('aria-hidden', 'false');
        openSubmenu(submenu);
      }
    });
  });


  sidebar?.addEventListener('click', (event) => {
    if (!body.classList.contains('sidebar-open')) return;
    const toggleLink = event.target.closest('.nav-link-toggle');
    if (toggleLink) return;
    const submenuLink = event.target.closest('.nav-submenu a');
    const primaryLink = event.target.closest('.sidebar-nav > .nav-section .nav-item > a');
    if (submenuLink || (primaryLink && !primaryLink.classList.contains('nav-link-toggle'))) {
      body.classList.remove('sidebar-open');
      closeAllSubmenus();
      closeProfileMenu();
      closeNotificationMenu();
    }
  });

  document.addEventListener('click', (event) => {
    if (!body.classList.contains('sidebar-open')) return;
    const insideSidebar = sidebar?.contains(event.target);
    const triggerClicked = sidebarExpandBtn?.contains(event.target);
    if (!insideSidebar && !triggerClicked) {
      body.classList.remove('sidebar-open');
      closeAllSubmenus();
    }
  });

  window.addEventListener('resize', syncSidebarMode);

  const setProfileState = (control, isOpen) => {
    control.dropdown.classList.toggle('is-open', isOpen);
    control.toggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    control.menu?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  };

  function closeProfileMenu(control) {
    if (control) {
      setProfileState(control, false);
      return;
    }
    profileDropdowns.forEach((ctrl) => setProfileState(ctrl, false));
  }

  function openProfileMenu(control) {
    if (!control) return;
    closeNotificationMenu();
    closeProfileMenu();
    setProfileState(control, true);
  }

  function openNotificationMenu() {
    if (!notificationDropdown || !notificationMenu || !notificationToggle) return;
    closeProfileMenu();
    notificationDropdown.classList.add('is-open');
    notificationToggle.setAttribute('aria-expanded', 'true');
    notificationMenu.setAttribute('aria-hidden', 'false');
  }

  function closeNotificationMenu() {
    if (!notificationDropdown || !notificationMenu || !notificationToggle) return;
    notificationDropdown.classList.remove('is-open');
    notificationToggle.setAttribute('aria-expanded', 'false');
    notificationMenu.setAttribute('aria-hidden', 'true');
  }

  profileDropdowns.forEach((control) => {
    control.toggle?.addEventListener('click', (event) => {
      event.preventDefault();
      const isOpen = control.dropdown.classList.contains('is-open');
      closeProfileMenu();
      if (!isOpen) {
        openProfileMenu(control);
      }
    });

    control.menu?.addEventListener('click', (event) => {
      if (event.target.closest('a')) {
        closeProfileMenu(control);
      }
    });
  });

  notificationToggle?.addEventListener('click', (event) => {
    event.preventDefault();
    if (!notificationDropdown) return;
    const isOpen = notificationDropdown.classList.contains('is-open');
    if (isOpen) {
      closeNotificationMenu();
    } else {
      closeNotificationMenu();
      openNotificationMenu();
    }
  });

  markAllReadBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    closeNotificationMenu();
  });

  notificationMenu?.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
      closeNotificationMenu();
    }
  });

  document.addEventListener('click', (event) => {
    const clickedInsideProfile = profileDropdowns.some((ctrl) => ctrl.dropdown.contains(event.target));
    if (!clickedInsideProfile) {
      closeProfileMenu();
    }
    if (notificationDropdown && !notificationDropdown.contains(event.target)) {
      closeNotificationMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeProfileMenu();
      closeNotificationMenu();
    }
  });

  exportBtn?.addEventListener('click', () => {
    exportBtn.classList.add('pulse');
    setTimeout(() => exportBtn.classList.remove('pulse'), 600);
  });
});

// ─── Global: shake + red outline for FORM modals only ────────────────────────
(function () {
  const SHAKE_CLASS = 'dialog--shake';
  const SHAKE_DURATION = 400;

  function isFormModal(backdropEl) {
    // Only shake modals that contain a <form> (edit/add data).
    // Detail / view modals have no form, so they close normally.
    return !!backdropEl.querySelector('form');
  }

  function triggerShake(backdropEl) {
    const dialog =
      backdropEl.querySelector('.dialog') ||
      backdropEl.querySelector(':scope > *') ||
      backdropEl;

    dialog.classList.remove(SHAKE_CLASS);
    void dialog.offsetWidth; // force reflow to restart animation
    dialog.classList.add(SHAKE_CLASS);

    setTimeout(() => dialog.classList.remove(SHAKE_CLASS), SHAKE_DURATION);
  }

  function closeBackdrop(backdropEl) {
    backdropEl.classList.remove('is-visible');
    backdropEl.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.dialog-backdrop.is-visible')) {
      document.body.classList.remove('modal-open');
    }
  }

  // Backdrop click handler
  document.addEventListener(
    'click',
    (event) => {
      const backdrop = event.target.closest('.dialog-backdrop');
      if (!backdrop) return;
      if (event.target !== backdrop) return;
      if (!backdrop.classList.contains('is-visible')) return;

      if (isFormModal(backdrop)) {
        triggerShake(backdrop);  // form modal → shake, do NOT close
      } else {
        closeBackdrop(backdrop); // detail/view modal → close normally
      }
    },
    true
  );

  // Escape key handler
  document.addEventListener(
    'keydown',
    (event) => {
      if (event.key !== 'Escape') return;
      const visibleModal = document.querySelector('.dialog-backdrop.is-visible');
      if (!visibleModal) return;

      if (isFormModal(visibleModal)) {
        event.stopImmediatePropagation(); // block other Escape handlers
        triggerShake(visibleModal);       // form modal → shake, do NOT close
      }
      // detail/view modal: let Escape propagate so it closes normally
    },
    true
  );
})();
