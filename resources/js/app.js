import { initAnimations } from './animations';
import { initAutoplayCarousels } from './carousels';
import { initModals } from './modals';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const dark = !document.documentElement.classList.contains('dark');
            document.documentElement.classList.toggle('dark', dark);
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        });
    });

    initAnimations();
    initModals();
    initAutoplayCarousels();
});
