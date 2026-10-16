/*
 * Auto-advancing horizontal scrollers: data-autoplay-carousel="<ms>" on a scroll-snap row
 * moves it one card at a time and rewinds at the end. It pauses while the visitor is
 * interacting (hover, touch, keyboard focus), while it is off screen or the tab is hidden,
 * and never runs for visitors who prefer reduced motion.
 */
export function initAutoplayCarousels() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    document.querySelectorAll('[data-autoplay-carousel]').forEach((track) => {
        const interval = Number(track.dataset.autoplayCarousel) || 2500;
        let isHovered = false;
        let isTouched = false;
        let isOnScreen = false;

        const advance = () => {
            if (isHovered || isTouched || !isOnScreen || document.hidden || track.contains(document.activeElement)) {
                return;
            }

            const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;

            if (atEnd) {
                track.scrollTo({ left: 0, behavior: 'smooth' });

                return;
            }

            const card = track.firstElementChild;
            const gap = parseFloat(getComputedStyle(track).columnGap) || 0;

            track.scrollBy({ left: card.getBoundingClientRect().width + gap, behavior: 'smooth' });
        };

        track.addEventListener('mouseenter', () => { isHovered = true; });
        track.addEventListener('mouseleave', () => { isHovered = false; });

        // After a swipe, give the visitor a moment before moving again
        let touchTimer;
        track.addEventListener('touchstart', () => {
            isTouched = true;
            clearTimeout(touchTimer);
        }, { passive: true });
        track.addEventListener('touchend', () => {
            touchTimer = setTimeout(() => { isTouched = false; }, interval * 2);
        }, { passive: true });

        new IntersectionObserver(([entry]) => {
            isOnScreen = entry.isIntersecting;
        }, { threshold: 0.3 }).observe(track);

        setInterval(advance, interval);
    });
}
