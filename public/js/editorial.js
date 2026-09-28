(() => {
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const pointerFine = window.matchMedia('(pointer: fine)').matches;

    if (!reducedMotion) {
        root.classList.add('motion-ready');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, currentObserver) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -36px 0px' });

            document.querySelectorAll('[data-reveal]').forEach((element, index) => {
                element.style.transitionDelay = `${Math.min(index % 5, 4) * 70}ms`;
                observer.observe(element);
            });
        } else {
            document.querySelectorAll('[data-reveal]').forEach((element) => {
                element.classList.add('is-visible');
            });
        }
    }

    if (pointerFine && !reducedMotion) {
        root.classList.add('has-pointer');
        let pointerFrame = 0;
        window.addEventListener('pointermove', (event) => {
            if (pointerFrame) return;
            pointerFrame = window.requestAnimationFrame(() => {
                root.style.setProperty('--pointer-x', `${event.clientX}px`);
                root.style.setProperty('--pointer-y', `${event.clientY}px`);
                pointerFrame = 0;
            });
        }, { passive: true });

        document.querySelectorAll('.editorial-card').forEach((card) => {
            card.addEventListener('pointermove', (event) => {
                const bounds = card.getBoundingClientRect();
                card.style.setProperty('--card-x', `${event.clientX - bounds.left}px`);
                card.style.setProperty('--card-y', `${event.clientY - bounds.top}px`);
            }, { passive: true });
        });

        document.querySelectorAll('.profile-feature-visual').forEach((visual) => {
            visual.addEventListener('pointermove', (event) => {
                const bounds = visual.getBoundingClientRect();
                visual.style.setProperty('--visual-x', `${event.clientX - bounds.left}px`);
                visual.style.setProperty('--visual-y', `${event.clientY - bounds.top}px`);
            }, { passive: true });
        });
    }

    if (!reducedMotion) {
        const parallaxItems = document.querySelectorAll('[data-scroll-float]');
        let parallaxFrame = 0;
        const updateParallax = () => {
            const midpoint = window.innerHeight / 2;
            parallaxItems.forEach((element) => {
                const bounds = element.getBoundingClientRect();
                const offset = Math.max(-18, Math.min(18, (midpoint - (bounds.top + bounds.height / 2)) * 0.035));
                element.style.setProperty('--scroll-offset', `${offset}px`);
            });
            parallaxFrame = 0;
        };
        window.addEventListener('scroll', () => {
            if (!parallaxFrame) parallaxFrame = window.requestAnimationFrame(updateParallax);
        }, { passive: true });
        updateParallax();
    }

    const progress = document.getElementById('scrollProgress');
    if (progress) {
        let scrollFrame = 0;
        const updateProgress = () => {
            const range = document.documentElement.scrollHeight - window.innerHeight;
            const percent = range > 0 ? (window.scrollY / range) * 100 : 0;
            progress.style.width = `${percent}%`;
            scrollFrame = 0;
        };
        window.addEventListener('scroll', () => {
            if (!scrollFrame) scrollFrame = window.requestAnimationFrame(updateProgress);
        }, { passive: true });
        updateProgress();
    }

    const mobileNav = document.getElementById('mobileNav');
    let mobileNavTrigger = null;
    window.openMobileNav = () => {
        if (!mobileNav) return;
        mobileNavTrigger = document.activeElement;
        mobileNav.classList.add('is-open');
        mobileNav.setAttribute('aria-hidden', 'false');
        document.getElementById('mobileNavTrigger')?.setAttribute('aria-expanded', 'true');
        document.body.classList.add('mobile-navigation-open');
        document.body.style.overflow = 'hidden';
        mobileNav.querySelector('button')?.focus();
    };
    window.closeMobileNav = () => {
        if (!mobileNav) return;
        mobileNav.classList.remove('is-open');
        mobileNav.setAttribute('aria-hidden', 'true');
        document.getElementById('mobileNavTrigger')?.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('mobile-navigation-open');
        document.body.style.overflow = '';
        mobileNavTrigger?.focus?.();
    };
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNav?.classList.contains('is-open')) window.closeMobileNav();
    });

    const loadingState = document.getElementById('gridLoading');
    const inventoryGrid = document.querySelector('[data-inventory-grid]');
    const showInventoryLoading = () => {
        if (!loadingState || !inventoryGrid) return;
        loadingState.classList.remove('hidden');
        inventoryGrid.classList.add('is-loading');
        inventoryGrid.setAttribute('aria-hidden', 'true');
    };
    document.querySelector('.inventory-filter')?.addEventListener('submit', showInventoryLoading);
    document.querySelectorAll('.page-main a[href*="cursor="]').forEach((link) => {
        link.addEventListener('click', showInventoryLoading);
    });
})();
