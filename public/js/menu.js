document.addEventListener('DOMContentLoaded', () => {
  const toggles = document.querySelectorAll('.admin-menu .toggle');
  const menuToggleBtn = document.getElementById('menu-toggle');
  const menu = document.querySelector('.admin-menu');
  const adminHeader = document.querySelector('body > header');
  let backdrop = null;
  const menuCollapseBtn = document.getElementById('menu-collapse');

  // Keep CSS var in sync with header height for sticky sidebar spacing
  const setHeaderHeightVar = () => {
    if (!adminHeader) return;
    document.documentElement.style.setProperty('--admin-header-height', adminHeader.offsetHeight + 'px');
  };
  setHeaderHeightVar();
  window.addEventListener('resize', setHeaderHeightVar);

  toggles.forEach(toggle => {
    const submenu = toggle.nextElementSibling;

    // Make sure ARIA attributes reflect initial state
    toggle.setAttribute('aria-expanded', 'false');
    if (submenu) submenu.setAttribute('aria-hidden', 'true');

    const toggleHandler = (e) => {
      // Close other submenus
      document.querySelectorAll('.admin-menu .submenu.open').forEach(openMenu => {
        if (openMenu !== submenu) {
          openMenu.classList.remove('open');
          const relatedToggle = openMenu.previousElementSibling;
          if (relatedToggle) relatedToggle.setAttribute('aria-expanded', 'false');
          openMenu.setAttribute('aria-hidden', 'true');
        }
      });

      const isOpen = submenu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      submenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    };

    toggle.addEventListener('click', toggleHandler);
    // Keyboard support
    toggle.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); toggleHandler(ev); }
    });
  });

  // Create backdrop when needed
  const ensureBackdrop = () => {
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'menu-backdrop';
      document.body.appendChild(backdrop);
      backdrop.addEventListener('click', () => { closeMenu(); });
    }
    return backdrop;
  };

  const openMenu = () => {
    if (!menu) return;
    menu.classList.add('open');
    ensureBackdrop().classList.add('show');
    document.body.style.overflow = 'hidden';
    menuToggleBtn?.setAttribute('aria-expanded', 'true');
    // focus the first link in the menu for accessibility
    const firstLink = menu.querySelector('a');
    if (firstLink) firstLink.focus();
  };

  const closeMenu = () => {
    if (!menu) return;
    menu.classList.remove('open');
    if (backdrop) backdrop.classList.remove('show');
    document.body.style.overflow = '';
    menuToggleBtn?.setAttribute('aria-expanded', 'false');
    menuToggleBtn?.focus();
  };

  // Toggle button
  if (menuToggleBtn) {
    menuToggleBtn.addEventListener('click', () => {
      if (menu.classList.contains('open')) closeMenu(); else openMenu();
    });
  }

  // Persisted collapse of the menu on desktop
  const applyCollapseState = (state) => {
    if (!menu) return;
    if (state) menu.classList.add('collapsed'); else menu.classList.remove('collapsed');
  };
  // Restore from localStorage
  const collapsed = localStorage.getItem('adminMenuCollapsed') === 'true';
  applyCollapseState(collapsed);
  if (menuCollapseBtn) {
    menuCollapseBtn.addEventListener('click', () => {
      const newState = !menu.classList.contains('collapsed');
      applyCollapseState(newState);
      localStorage.setItem('adminMenuCollapsed', newState);
    });
  }

  // Show small tooltip for keyboard focus when collapsed
  document.addEventListener('focusin', (e) => {
    const t = e.target;
    if (menu && menu.classList.contains('collapsed') && t.matches && t.matches('.toggle[data-tooltip]')) {
      // Add a temporary aria-describedby-like attribute
      const txt = t.getAttribute('data-tooltip');
      t.setAttribute('title', txt);
    }
  });

  // Close on ESC key
  window.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeMenu(); } });

  // Close if window resized larger than mobile breakpoint
  window.addEventListener('resize', () => { if (window.innerWidth > 860 && menu && menu.classList.contains('open')) closeMenu(); });

  // Public header menu toggle for front-end nav
  const publicMenuToggleBtn = document.getElementById('public-menu-toggle');
  const publicNav = document.querySelector('header nav');
  if (publicMenuToggleBtn && publicNav) {
    publicMenuToggleBtn.addEventListener('click', () => {
      const isOpen = publicNav.classList.toggle('open');
      publicMenuToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    // Close public nav on outside click
    document.addEventListener('click', (e) => {
      if (!publicNav.contains(e.target) && e.target !== publicMenuToggleBtn && publicNav.classList.contains('open')) publicNav.classList.remove('open');
    });
  }

  // Highlight active menu item by matching path
  (function highlightActive() {
    try {
      const links = document.querySelectorAll('.admin-menu a');
      const currentPath = window.location.pathname + window.location.search;
      links.forEach(a => {
        // If link matches current path or pathname without query
        if (a.href.endsWith(currentPath) || a.pathname === window.location.pathname || currentPath.indexOf(a.getAttribute('href')) === 0) {
          const li = a.closest('.menu-item');
          if (li) li.classList.add('active');
          // Expand parent submenu
          const submenu = a.closest('.submenu');
          if (submenu) { submenu.classList.add('open'); submenu.setAttribute('aria-hidden','false'); const related = submenu.previousElementSibling; if (related) related.setAttribute('aria-expanded', 'true'); }
        }
      });
    } catch (e) { /* not critical */ }
  })();
});
