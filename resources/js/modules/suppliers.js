import apiClient from '../api/client.js';

export class SuppliersModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
    }

    init() {
        this.bindEvents();
        this.loadSuppliers();
    }

    bindEvents() {
        document.getElementById('supplier-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadSuppliers(); }, 300);
        });
        document.getElementById('btn-clear-supplier-search')?.addEventListener('click', () => {
            const s = document.getElementById('supplier-search'); if (s) s.value = '';
            this.currentPage = 1; this.loadSuppliers();
        });
        document.getElementById('btn-add-supplier')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-supplier')?.addEventListener('click', () => this.save());
        document.getElementById('suppliers-tbody')?.addEventListener('click', (e) => {
            const edit = e.target.closest('[data-action="edit"]');
            const del  = e.target.closest('[data-action="delete"]');
            if (edit) this.edit(edit.dataset.id);
            if (del)  this.remove(del.dataset.id, del.dataset.name);
        });
    }

    async loadSuppliers() {
        const tbody = document.getElementById('suppliers-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';

        const params = { page: this.currentPage, per_page: 20 };
        const search = document.getElementById('supplier-search')?.value;
        if (search) params.search = search;

        try {
            const res = await apiClient.get('/suppliers', { params });
            const { data: suppliers, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(suppliers);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Failed to load suppliers.</td></tr>`;
        }
    }

    renderTable(suppliers) {
        const tbody = document.getElementById('suppliers-tbody');
        const canEdit   = window.utils?.hasAnyPermission(['edit-supplier', 'create-supplier']);
        const canDelete = window.utils?.hasAnyPermission(['delete-supplier', 'create-supplier']);

        if (!suppliers.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No suppliers found.</td></tr>`;
            return;
        }

        tbody.innerHTML = suppliers.map(s => {
            const editBtn = canEdit
                ? `<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="${s.id}">
                       <i class="fas fa-pencil-alt"></i></button>` : '';
            const delBtn = canDelete
                ? `<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${s.id}"
                       data-name="${this.esc(s.name)}"><i class="fas fa-trash"></i></button>` : '';
            return `<tr>
                <td>${this.esc(s.name)}</td>
                <td>${this.esc(s.contact_person ?? '—')}</td>
                <td>${this.esc(s.email ?? '—')}</td>
                <td>${this.esc(s.phone ?? '—')}</td>
                <td class="text-end">${editBtn}${delBtn}</td>
            </tr>`;
        }).join('');
    }

    renderPagination(pagination) {
        const wrap = document.getElementById('suppliers-pagination');
        const info = document.getElementById('suppliers-pagination-info');
        const ctrl = document.getElementById('suppliers-pagination-controls');
        if (!wrap || !pagination) return;
        info.textContent = `Showing ${pagination.from ?? 0}–${pagination.to ?? 0} of ${pagination.total}`;
        if (pagination.last_page <= 1) { ctrl.innerHTML = ''; return; }
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <button class="page-link" data-page="${i}">${i}</button></li>`;
        }
        html += '</ul></nav>';
        ctrl.innerHTML = html;
        ctrl.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => { this.currentPage = +btn.dataset.page; this.loadSuppliers(); });
        });
    }

    showModal(supplier = null) {
        this.editingId = supplier?.id ?? null;
        const form = document.getElementById('supplierForm');
        form.reset();
        window.utils?.clearValidationErrors(form);
        document.getElementById('supplierModalLabel').textContent = supplier ? 'Edit Supplier' : 'Add Supplier';
        document.getElementById('supplier-id').value = supplier?.id ?? '';
        if (supplier) {
            document.getElementById('supplier-name').value    = supplier.name ?? '';
            document.getElementById('supplier-contact').value = supplier.contact_person ?? '';
            document.getElementById('supplier-email').value   = supplier.email ?? '';
            document.getElementById('supplier-phone').value   = supplier.phone ?? '';
            document.getElementById('supplier-address').value = supplier.address ?? '';
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierModal')).show();
    }

    async edit(id) {
        try {
            const res = await apiClient.get(`/suppliers/${id}`);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load supplier.', 'error');
        }
    }

    async save() {
        const form = document.getElementById('supplierForm');
        const btn  = document.getElementById('btn-save-supplier');
        const spin = btn.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);

        const payload = {
            name:           document.getElementById('supplier-name').value.trim(),
            contact_person: document.getElementById('supplier-contact').value.trim() || null,
            email:          document.getElementById('supplier-email').value.trim() || null,
            phone:          document.getElementById('supplier-phone').value.trim() || null,
            address:        document.getElementById('supplier-address').value.trim() || null,
        };

        btn.disabled = true; spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put(`/suppliers/${this.editingId}`, payload);
                window.utils?.showToast('Supplier updated.', 'success');
            } else {
                await apiClient.post('/suppliers', payload);
                window.utils?.showToast('Supplier created.', 'success');
            }
            bootstrap.Modal.getInstance(document.getElementById('supplierModal'))?.hide();
            this.loadSuppliers();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            } else {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save supplier.', 'error');
            }
        } finally {
            btn.disabled = false; spin.classList.add('d-none');
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
            const ok = await window.utils?.showConfirmModal('Delete Supplier',
                `Delete <strong>${this.esc(name)}</strong>?`, 'Delete', 'btn-danger');
            if (ok) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete(`/suppliers/${id}`);
            window.utils?.showToast('Supplier deleted.', 'success');
            this.loadSuppliers();
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
