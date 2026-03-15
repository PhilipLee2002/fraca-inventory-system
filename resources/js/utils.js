export function showToast(message, type = 'success') {
    const container = document.getElementById('global-toast-container');
    if (!container) return;

    const id = `toast-${Date.now()}`;
    const isSuccess = type === 'success';
    const headerClass = isSuccess ? 'text-bg-success' : 'text-bg-danger';

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div id="${id}" class="toast align-items-center ${headerClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    const toastElement = wrapper.firstElementChild;
    container.appendChild(toastElement);

    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

