import { showToast } from './toast.js';
import { clearValidationErrors } from './validation.js';

/**
 * Show the admin verification modal and return a promise that resolves with credentials
 * @param {Function} onVerified - Callback function to execute after successful verification
 * @returns {Promise}
 */
export function showAdminVerifyModal(onVerified) {
    return new Promise((resolve, reject) => {
        const modal = document.getElementById('adminVerifyModal');
        const form = document.getElementById('adminVerifyForm');
        const verifyBtn = document.getElementById('verifyAdminBtn');
        const spinner = verifyBtn.querySelector('.spinner-border');

        if (!modal || !form || !verifyBtn) {
            console.error('Admin verify modal elements not found');
            reject(new Error('Modal elements not found'));
            return;
        }

        // Clear form and errors
        form.reset();
        clearValidationErrors(form);

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Handle verify button click
        const handleVerify = async () => {
            const email = form.querySelector('#adminEmail').value;
            const password = form.querySelector('#adminPassword').value;

            if (!email || !password) {
                showToast('Please enter both email and password', 'error');
                return;
            }

            // Show loading state
            verifyBtn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                // Call the verification callback
                const result = await onVerified({ email, password });
                
                // Hide modal on success
                bsModal.hide();
                resolve(result);
            } catch (error) {
                showToast(error.message || 'Verification failed', 'error');
                reject(error);
            } finally {
                // Reset loading state
                verifyBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        };

        // Attach event listener
        verifyBtn.addEventListener('click', handleVerify, { once: true });

        // Clean up on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            verifyBtn.removeEventListener('click', handleVerify);
            form.reset();
            clearValidationErrors(form);
        }, { once: true });
    });
}

/**
 * Show a confirmation modal
 * @param {string} title - Modal title
 * @param {string} message - Modal message
 * @param {string} confirmText - Confirm button text
 * @param {string} confirmClass - Confirm button class (default: btn-danger)
 * @returns {Promise<boolean>}
 */
export function showConfirmModal(title, message, confirmText = 'Confirm', confirmClass = 'btn-danger') {
    return new Promise((resolve) => {
        // Create modal HTML
        const modalId = `confirm-modal-${Date.now()}`;
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${confirmClass}" id="${modalId}-confirm">${confirmText}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modalElement = document.getElementById(modalId);
        const confirmBtn = document.getElementById(`${modalId}-confirm`);
        const bsModal = new bootstrap.Modal(modalElement);

        bsModal.show();

        confirmBtn.addEventListener('click', () => {
            bsModal.hide();
            resolve(true);
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
            resolve(false);
        }, { once: true });
    });
}
