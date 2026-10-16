import { animate } from 'motion';

const easeOutExpo = [0.16, 1, 0.3, 1];

/*
 * Modal dialogs on the landing page. A button with data-modal-open="<dialog id>" opens it;
 * [data-modal-close], Esc or a click on the backdrop closes it. An iframe carrying
 * data-modal-src (e.g. the doctor's intro video) only loads while its dialog is open.
 */
export function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        const dialog = document.getElementById(button.dataset.modalOpen);

        if (!dialog) {
            return;
        }

        button.addEventListener('click', () => {
            dialog.querySelectorAll('iframe[data-modal-src]').forEach((frame) => {
                frame.src = frame.dataset.modalSrc;
            });

            dialog.showModal();

            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                animate(dialog, { opacity: [0, 1], scale: [0.94, 1] }, { duration: 0.45, ease: easeOutExpo });
            }
        });
    });

    document.querySelectorAll('dialog[data-modal]').forEach((dialog) => {
        dialog.querySelectorAll('[data-modal-close]').forEach((closeButton) => {
            closeButton.addEventListener('click', () => dialog.close());
        });

        // A click on the dimmed backdrop lands on the <dialog> itself, not on its content
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        // Unloading the player stops any video and its audio once the modal is gone
        dialog.addEventListener('close', () => {
            dialog.querySelectorAll('iframe[data-modal-src]').forEach((frame) => frame.removeAttribute('src'));
        });
    });
}
