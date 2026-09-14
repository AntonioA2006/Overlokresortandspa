document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-landing-parallax]').forEach((element) => {
        const media = element.querySelector('[data-landing-parallax-media]');

        if (!media || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        window.addEventListener('scroll', () => {
            const offset = window.scrollY * 0.18;
            media.style.transform = `translate3d(0, ${offset}px, 0) scale(1.04)`;
        }, { passive: true });
    });
});
