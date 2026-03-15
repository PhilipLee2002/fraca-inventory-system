import apiClient from '../api/client.js';

export class CategoriesModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
    }

    init() {
        this.bindEvents();
        this.loadCategories();
    }

    bindEvents() {
        document.getElementById('category-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadCategories(); }, 300);
        });
        document.getElementById('btn-clear-category-search')?.addEventListener('click', () => {
            const s = document.getElementById('category-search'); if (s) s.value = '';
            this.currentPage = 1; this.loadCategories();
        });
        document.getElementById('btn-add-category')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-category')?.addEventListener('click', () => this.save());
        document.getElementById('categories-tbody')?.addEventListener('click', (e) => {
            const edit = e.target.closest('[data-action="edit"]');
            const del  = e.target.closest('[data-action="delete"]');
            if (edit) this.edit(edit.dataset.id);
            if (del)  this.remove(del.dataset.id, del.dataset.name);
        });
    }

    async loadCategories() {
        const tbody = document.getElementById('categories-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';

        const params = { page: this.currentPage, per_page: 20 };
        const search = document.getElementById('category-search')?.value;
        if (search) params.search = search;

        try {
            const res = await apiClient.get('/categories', { params });
            // Categories API may return flat array or paginated object
            const payload = res.data?.data;
            const categories = Array.isArray(payload) ? payload : (payload?.data ?? []);
            const pagination = Array.isArray(payload) ? null : (payload?.pagination ?? {
                current_page: payload?.current_page,
                last_page:    payload?.last_page,
                total:        payload?.total,
                from:         payload?.from,
                to:           payload?.to,
            });
            this.renderTable(categories);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Failed to load categories.</td></tr>`;
        }
    }

    renderTable(categories) {
        const tbody = document.getElementById('categories-tbody');
        const canEdit   = window.utils?.hasAnyPermission(['edit-category', 'create-category']);
        const canDelete = window.utils?.hasAnyPermission(['delete-category', 'create-category']);

        if (!categories.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No categories found.</td></tr>`;
            return;
        }

        tbody.innerHTML = categories.map(c => {
            const editBtn = canEdit
                ? `<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="${c.id}">
                       <i class="fas fa-pencil-alt"></i></button>` : '';
            const delBtn = canDelete
                ? `<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${c.id}"
                       data-name="${this.esc(c.name)}"><i class="fas fa-trash"></i></button>` : '';
            return `<tr>
                <td>${this.esc(c.name)}</td>
                <td class="text-muted small">${this.esc(c.description ?? '—')}</td>
                <td class="text-center">${c.products_count ?? '—'}</td>
                <td class="text-end">${editBtn}${delBtn}</td>
            </tr>`;
        }).join('');
    }

    showModal(category = null) {
        this.editingId = category?.id ?? null;
        const form = document.getElementById('categoryForm');
        form.reset();
        window.utils?.clearValidationErrors(form);
        document.getElementById('categoryModalLabel').textContent = category ? 'Edit Category' : 'Add Category';
        document.getElementById('category-id').value = category?.id ?? '';
        if (category) {
            document.getElementById('category-name').value        = category.name ?? '';
            document.getElementById('category-description').value = category.description ?? '';
        }
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    async edit(id) {
        try {
            const res = await apiClient.get(`/categories/${id}`);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load category.', 'error');
        }
    }

    async save() {
        const form = document.getElementById('categoryForm');
        const btn  = document.getElementById('btn-save-category');
        const spin = btn.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);

        const payload = {
            name:        document.getElementById('category-name').value.trim(),
            description: document.getElementById('category-description').value.trim() || null,
        };

        btn.disabled = true; spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put(`/categories/${this.editingId}`, payload);
                window.utils?.showToast('Category updated.', 'success');
            } else {
                await apiClient.post('/categories', payload);
                window.utils?.showToast('Category created.', 'success');
            }
            bootstrap.Modal.getInstance(document.getElementById('categoryModal'))?.hide();
            this.loadCategories();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
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
            const ok = await window.utils?.showConfirmModal('Delete Category',
                `Delete <strong>${this.esc(name)}</strong>?`, 'Delete', 'btn-danger');
            if (ok) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete(`/categories/${id}`);
            window.utils?.showToast('Category deleted.', 'success');
            this.loadCategories();
        } catch (err) {
            if (err.response?.status !== 401 && err.response?.status !== 403) {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to delete.', 'error');
            }
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    renderPagination(pagination) {
        const info = document.getElementById('categories-pagination-info');
        const ctrl = document.getElementById('categories-pagination-controls');
        if (!pagination?.total) return;
        if (info) info.textContent = `Showing ${pagination.from ?? 0}–${pagination.to ?? 0} of ${pagination.total}`;
        if (!ctrl) return;
        if ((pagination.last_page ?? 1) <= 1) { ctrl.innerHTML = ''; return; }
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <button class="page-link" data-page="${i}">${i}</button></li>`;
        }
        html += '</ul></nav>';
        ctrl.innerHTML = html;
        ctrl.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => { this.currentPage = +btn.dataset.page; this.loadCategories(); });
        });
    }

}
