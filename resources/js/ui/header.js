const SCROLL_THRESHOLD = 24;
const DESKTOP_BREAKPOINT = 960;

function getMenuLabels() {
    const body = document.body;

    return {
        open: body.dataset.menuOpenLabel || 'Open menu',
        close: body.dataset.menuCloseLabel || 'Close menu',
    };
}

function getFocusableElements(container) {
    if (!container) {
        return [];
    }

    return [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')];
}

export function initPremiumHeader() {
    const header = document.querySelector('[data-premium-header]');

    if (!header) {
        return;
    }

    const toggle = header.querySelector('[data-header-toggle]');
    const panel = header.querySelector('[data-header-panel]');
    const mobileLinks = panel?.querySelectorAll('a, button') ?? [];
    const labels = getMenuLabels();

    const updateScrollState = () => {
        header.classList.toggle('is-scrolled', window.scrollY > SCROLL_THRESHOLD);
    };

    const closeMenu = () => {
        header.classList.remove('is-menu-open');
        document.body.classList.remove('is-menu-open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', labels.open);
        panel?.setAttribute('aria-hidden', 'true');
        panel?.setAttribute('inert', '');
    };

    const openMenu = () => {
        header.classList.add('is-menu-open');
        document.body.classList.add('is-menu-open');
        toggle?.setAttribute('aria-expanded', 'true');
        toggle?.setAttribute('aria-label', labels.close);
        panel?.setAttribute('aria-hidden', 'false');
        panel?.removeAttribute('inert');

        const focusables = getFocusableElements(panel);
        focusables[0]?.focus();
    };

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });

    toggle?.addEventListener('click', () => {
        if (header.classList.contains('is-menu-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    mobileLinks.forEach((element) => {
        element.addEventListener('click', closeMenu);
    });

    panel?.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || !header.classList.contains('is-menu-open')) {
            return;
        }

        const focusables = getFocusableElements(panel);

        if (focusables.length === 0) {
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && header.classList.contains('is-menu-open')) {
            closeMenu();
            toggle?.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= DESKTOP_BREAKPOINT) {
            closeMenu();
        }
    });
}
