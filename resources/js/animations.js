import { animate, inView, scroll, stagger } from 'motion';

/* Smooth deceleration used across the landing page, so every motion feels like one system */
const easeOutExpo = [0.16, 1, 0.3, 1];

const revealOffsets = {
    up: { y: [32, 0] },
    left: { x: [-40, 0] },
    right: { x: [40, 0] },
    scale: { scale: [0.92, 1] },
    draw: { scaleX: [0, 1] },
};

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function animateHeroEntrance() {
    const heroItems = document.querySelectorAll('[data-hero]');

    if (heroItems.length) {
        animate(heroItems, { opacity: [0, 1], y: [28, 0] }, { duration: 0.9, delay: stagger(0.12, { startDelay: 0.15 }), ease: easeOutExpo });
    }

    const heroImage = document.querySelector('[data-hero-image]');

    if (heroImage) {
        animate(heroImage, { scale: [1.12, 1] }, { duration: 2.4, ease: easeOutExpo });
    }
}

/* Background drifts slower than the page and the copy fades as the hero leaves the viewport */
function parallaxHero() {
    const layer = document.querySelector('[data-parallax]');
    const content = document.querySelector('[data-hero-content]');
    const section = layer?.closest('section');

    if (!section) {
        return;
    }

    scroll((progress) => {
        layer.style.transform = `translateY(${progress * 160}px)`;

        if (content) {
            content.style.opacity = String(1 - progress * 0.8);
            content.style.transform = `translateY(${progress * -60}px)`;
        }
    }, { target: section, offset: ['start start', 'end start'] });
}

function revealOnScroll() {
    inView('[data-reveal]', (element) => {
        const offset = revealOffsets[element.dataset.reveal] ?? revealOffsets.up;

        animate(element, { opacity: [0, 1], ...offset }, { duration: 0.8, delay: Number(element.dataset.revealDelay ?? 0), ease: easeOutExpo });
    }, { amount: 0.2 });

    inView('[data-reveal-stagger]', (container) => {
        animate([...container.children], { opacity: [0, 1], y: [32, 0] }, { duration: 0.7, delay: stagger(0.1), ease: easeOutExpo });
    }, { amount: 0.15 });
}

/* Counts "500+", "4.9" or "+2,500 Happy Patients" up from zero, keeping prefix, suffix and formatting */
function countUpNumbers() {
    inView('[data-count-up]', (element) => {
        const [, prefix, digits, suffix] = element.textContent.trim().match(/^(\D*)([\d.,]+)(.*)$/) ?? [];

        if (!digits) {
            return;
        }

        const decimals = digits.includes('.') ? digits.split('.')[1].length : 0;
        const format = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
            useGrouping: digits.includes(','),
        });

        animate(0, parseFloat(digits.replace(/,/g, '')), {
            duration: 1.8,
            ease: easeOutExpo,
            onUpdate: (value) => {
                element.textContent = prefix + format.format(value) + suffix;
            },
        });
    }, { amount: 0.6 });
}

function trackScrollProgress() {
    const bar = document.querySelector('[data-scroll-progress]');

    if (bar) {
        scroll((progress) => {
            bar.style.transform = `scaleX(${progress})`;
        });
    }
}

function condenseHeaderOnScroll() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const update = () => header.toggleAttribute('data-scrolled', window.scrollY > 40);

    update();
    window.addEventListener('scroll', update, { passive: true });
}

function toggleMobileMenu() {
    const button = document.querySelector('[data-menu-toggle]');
    const menu = button && document.getElementById(button.getAttribute('aria-controls'));

    if (!menu) {
        return;
    }

    const setOpen = (isOpen) => {
        button.setAttribute('aria-expanded', String(isOpen));
        menu.classList.toggle('hidden', !isOpen);

        if (isOpen && !prefersReducedMotion()) {
            animate(menu, { opacity: [0, 1], y: [-8, 0] }, { duration: 0.3, ease: easeOutExpo });
        }
    };

    button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));

    menu.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
}

/* Soft gold glow that follows the pointer across `.spotlight` cards */
function followPointerSpotlight() {
    document.querySelectorAll('.spotlight').forEach((card) => {
        card.addEventListener('pointermove', (event) => {
            const bounds = card.getBoundingClientRect();

            card.style.setProperty('--spotlight-x', `${event.clientX - bounds.left}px`);
            card.style.setProperty('--spotlight-y', `${event.clientY - bounds.top}px`);
        });
    });
}

function animateFaqAnswers() {
    document.querySelectorAll('[data-faq]').forEach((details) => {
        details.addEventListener('toggle', () => {
            if (details.open) {
                animate(details.querySelector('[data-faq-answer]'), { opacity: [0, 1], y: [-6, 0] }, { duration: 0.4, ease: easeOutExpo });
            }
        });
    });
}

export function initAnimations() {
    document.documentElement.dataset.motion = 'started';

    condenseHeaderOnScroll();
    toggleMobileMenu();
    trackScrollProgress();

    if (prefersReducedMotion()) {
        return;
    }

    animateHeroEntrance();
    parallaxHero();
    revealOnScroll();
    countUpNumbers();
    followPointerSpotlight();
    animateFaqAnswers();
}
