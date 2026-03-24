import apiClient from '../api/client.js';

export class CustomersModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
    }

    init() {
        this.bindEvents();
        this.loadCustomers();
    }

    bindEvents() {
        document.getElementById('customer-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadCustomers(); }, 300);
        });
        document.getElementById('btn-clear-customer-search')?.addEventListener('click', () => {
            const s = document.getElementById('customer-search'); if (s) s.value = '';
            this.currentPage = 1; this.loadCustomers();
        });
        document.getElementById('btn-add-customer')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-customer')?.addEventListener('click', () => this.save());
        document.getElementById('customers-tbody')?.addEventListener('click', (e) => {
            const edit = e.target.closest('[data-action="edit"]');
            const del  = e.target.closest('[data-action="delete"]');
            if (edit) this.edit(edit.dataset.id);
            if (del)  this.remove(del.dataset.id, del.dataset.name);
        });
        // Clean up modal state on hide
        document.getElementById('customerModal')?.addEventListener('hidden.bs.modal', () => {
            this.editingId = null;
        });
    }

    async loadCustomers() {
        const tbody = document.getElementById('customers-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';

        const params = { page: this.currentPage, per_page: 20 };
        const search = document.getElementById('customer-search')?.value;
        if (search) params.search = search;

        try {
            const res = await apiClient.get('/customers', { params });
            const { data: customers, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(customers);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Failed to load customers.</td></tr>';
        }
    }

    renderTable(customers) {
        const tbody = document.getElementById('customers-tbody');
        const canEdit   = window.utils?.hasAnyPermission(['edit-customer']);
        const canDelete = window.utils?.hasAnyPermission(['delete-customer']);

        if (!customers.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No customers found.</td></tr>';
            return;
        }

        tbody.innerHTML = customers.map(c => {
            const fullName = ((c.first_name ?? '') + ' ' + (c.last_name ?? '')).trim();
            const editBtn = canEdit
                ? '<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="' + c.id + '"><i class="fas fa-pencil-alt"></i></button>'
                : '';
            const delBtn = canDelete
                ? '<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="' + c.id + '" data-name="' + this.esc(fullName) + '"><i class="fas fa-trash"></i></button>'
                : '';
            return '<tr>' +
                '<td>' + this.esc(fullName) + '</td>' +
                '<td>' + this.esc(c.email ?? '\u2014') + '</td>' +
                '<td>' + this.esc(c.phone ?? '\u2014') + '</td>' +
                '<td class="text-center">' + (c.sales_count ?? 0) + '</td>' +
                '<td class="text-end">' + editBtn + delBtn + '</td>' +
                '</tr>';
        }).join('');
    }

    renderPagination(pagination) {
        const wrap = document.getElementById('customers-pagination');
        const info = document.getElementById('customers-pagination-info');
        const ctrl = document.getElementById('customers-pagination-controls');
        if (!wrap || !pagination) return;
        if (info) info.textContent = 'Showing ' + (pagination.from ?? 0) + '\u2013' + (pagination.to ?? 0) + ' of ' + pagination.total;
        if (!ctrl) return;
        if (pagination.last_page <= 1) { ctrl.innerHTML = ''; return; }
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += '<li class="page-item ' + (i === pagination.current_page ? 'active' : '') + '"><button class="page-link" data-page="' + i + '">' + i + '</button></li>';
        }
        html += '</ul></nav>';
        ctrl.innerHTML = html;
        ctrl.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => { this.currentPage = +btn.dataset.page; this.loadCustomers(); });
        });
    }

    showModal(customer = null) {
        this.editingId = customer?.id ?? null;
        const form = document.getElementById('customerForm');
        form.reset();
        window.utils?.clearValidationErrors(form);
        document.getElementById('customerModalLabel').textContent = customer ? 'Edit Customer' : 'Add Customer';
        document.getElementById('customer-id').value = customer?.id ?? '';
        if (customer) {
            document.getElementById('customer-first-name').value = customer.first_name ?? '';
            document.getElementById('customer-last-name').value  = customer.last_name ?? '';
            document.getElementById('customer-email').value      = customer.email ?? '';
            document.getElementById('customer-phone').value      = customer.phone ?? '';
            document.getElementById('customer-address').value    = customer.address ?? '';
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('customerModal')).show();
    }

    async edit(id) {
        try {
            const res = await apiClient.get('/customers/' + id);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load customer.', 'error');
        }
    }

    async save() {
        const form = document.getElementById('customerForm');
        const btn  = document.getElementById('btn-save-customer');
        const spin = btn?.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);

        const payload = {
            first_name: document.getElementById('customer-first-name').value.trim(),
            last_name:  document.getElementById('customer-last-name').value.trim() || null,
            email:      document.getElementById('customer-email').value.trim() || null,
            phone:      document.getElementById('customer-phone').value.trim() || null,
            address:    document.getElementById('customer-address').value.trim() || null,
        };

        if (!payload.first_name) {
            window.utils?.showToast('First name is required.', 'error');
            return;
        }

        if (btn) btn.disabled = true;
        if (spin) spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put('/customers/' + this.editingId, payload);
                window.utils?.showToast('Customer updated.', 'success');
            } else {
                await apiClient.post('/customers', payload);
                window.utils?.showToast('Customer created.', 'success');
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('customerModal')).hide();
            this.loadCustomers();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            } else {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save customer.', 'error');
            }
        } finally {
            if (btn) btn.disabled = false;
            if (spin) spin.classList.add('d-none');
        }
    }

    async remove(id, name) {
        const role = window.utils?.getUserRole();
        if (role === 'manager') {
            try {
                await window.utils?.showAdminVerifyModal(async ({ email, password }) => {
                    const r = await apiClient.post('/verify-admin', { email, password });
                    if (!r.data?.data?.verified) throw new Error('Verification failed');
                });
                await this.doDelete(id);
            } catch {}
        } else {
            const ok = await window.utils?.showConfirmModal('Delete Customer',
                'Delete <strong>' + this.esc(name) + '</strong>?', 'Delete', 'btn-danger');
            if (ok) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete('/customers/' + id);
            window.utils?.showToast('Customer deleted.', 'success');
            this.loadCustomers();
        } catch (err) {
            if (err.response?.status !== 401 && err.response?.status !== 403) {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to delete.', 'error');
            }
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}