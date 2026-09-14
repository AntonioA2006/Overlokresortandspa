const REVEAL_SELECTOR = '[data-reveal], [data-fade], [data-lift], [data-image-reveal]';

export function initRevealAnimations() {
    const elements = document.querySelectorAll(REVEAL_SELECTOR);

    if (!elements.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        },
        {
            threshold: 0.15,
            rootMargin: '0px 0px -5% 0px',
        },
    );

    elements.forEach((element) => observer.observe(element));
}
