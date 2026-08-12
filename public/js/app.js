// Gram Panchayat - Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const hash = this.getAttribute('href');

            // A bare "#" is not a valid selector and makes querySelector throw,
            // which is what the language dropdown links carry to hang an inline
            // onclick off. Leave those to their own handler.
            if (!hash || hash === '#') {
                return;
            }

            let target = null;
            try {
                target = document.querySelector(hash);
            } catch (error) {
                return;
            }

            if (!target) {
                return;
            }

            e.preventDefault();

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });

            // scrollIntoView moves the viewport but not the caret. Without this
            // a keyboard user who follows the skip link is scrolled down while
            // focus stays in the header, so the next Tab returns them to the nav
            // and WCAG 2.4.1 is not actually satisfied. Anything that is not
            // natively focusable needs tabindex="-1" to accept focus.
            target.focus({ preventScroll: true });

            // Keeps the fragment in the address bar so the jump is linkable and
            // the Back button behaves as it would without the handler.
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', hash);
            }
        });
    });

    // Header scroll effect
    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Accessibility controls in the government masthead (GIGW 3.0).
    // The saved values are already applied by the inline script in <head>; this
    // only wires the buttons and keeps the pressed state in sync.
    (function () {
        // Capped at 1.2. Beyond that the nav row wraps on a phone, and browser
        // zoom is the better tool for anyone who needs more than a fifth again.
        const STEPS = [1, 1.1, 1.2];
        const root = document.documentElement;
        const sizeButtons = document.querySelectorAll('[data-text-scale]');
        const contrastButton = document.querySelector('[data-contrast-toggle]');

        function store(key, value) {
            try {
                if (value === null) {
                    localStorage.removeItem(key);
                } else {
                    localStorage.setItem(key, value);
                }
            } catch (e) {
                // Private browsing. The choice still applies for this page view.
            }
        }

        function currentScale() {
            const raw = parseFloat(root.style.getPropertyValue('--text-scale'));
            return STEPS.indexOf(raw) === -1 ? 1 : raw;
        }

        function applyScale(scale) {
            if (scale === 1) {
                root.style.removeProperty('--text-scale');
                store('gpTextScale', null);
            } else {
                root.style.setProperty('--text-scale', String(scale));
                store('gpTextScale', String(scale));
            }

            // A-/A/A+ are actions rather than toggles, so they carry no pressed
            // state. Disabling at the ends is what communicates the limit, and
            // it is exposed to assistive tech for free.
            sizeButtons.forEach(function (button) {
                const action = button.dataset.textScale;

                if (action === 'down') {
                    button.disabled = scale === STEPS[0];
                } else if (action === 'up') {
                    button.disabled = scale === STEPS[STEPS.length - 1];
                } else {
                    button.disabled = scale === 1;
                }
            });
        }

        sizeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const action = button.dataset.textScale;
                const index = STEPS.indexOf(currentScale());
                let next = 1;

                if (action === 'up') {
                    next = STEPS[Math.min(index + 1, STEPS.length - 1)];
                } else if (action === 'down') {
                    next = STEPS[Math.max(index - 1, 0)];
                }

                applyScale(next);
            });
        });

        applyScale(currentScale());

        if (contrastButton) {
            const on = root.classList.contains('high-contrast');
            contrastButton.setAttribute('aria-pressed', on ? 'true' : 'false');

            contrastButton.addEventListener('click', function () {
                const enabled = root.classList.toggle('high-contrast');
                contrastButton.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                store('gpHighContrast', enabled ? '1' : null);
            });
        }
    })();

    // Language menu. Opens on click so it works on touch, where there is no
    // hover, and closes on Escape or an outside click.
    (function () {
        const toggle = document.getElementById('langToggle');
        const menu = document.getElementById('langMenu');

        if (!toggle || !menu) {
            return;
        }

        function setOpen(open) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            menu.classList.toggle('open', open);
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                setOpen(false);
                toggle.focus();
            }
        });

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && e.target !== toggle) {
                setOpen(false);
            }
        });
    })();

    // Form validation feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        });
    });
});
