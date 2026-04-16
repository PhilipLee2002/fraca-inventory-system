import apiClient from '../api/client.js';

export class UsersModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
    }

    init() {
        this.bindEvents();
        this.loadRoles();
        this.loadUsers();
    }

    bindEvents() {
        // Search — debounced
        document.getElementById('user-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadUsers(); }, 300);
        });
        document.getElementById('filter-user-role')?.addEventListener('change', () => { this.currentPage = 1; this.loadUsers(); });
        document.getElementById('btn-clear-user-filters')?.addEventListener('click', () => {
            const s = document.getElementById('user-search'); if (s) s.value = '';
            const r = document.getElementById('filter-user-role'); if (r) r.value = '';
            this.currentPage = 1; this.loadUsers();
        });

        document.getElementById('btn-add-user')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-user')?.addEventListener('click', () => this.save());
        document.getElementById('users-tbody')?.addEventListener('click', (e) => {
            const edit = e.target.closest('[data-action="edit"]');
            const del  = e.target.closest('[data-action="delete"]');
            if (edit) this.edit(edit.dataset.id);
            if (del)  this.remove(del.dataset.id, del.dataset.name);
        });
    }

    async loadRoles() {
        try {
            const res = await apiClient.get('/users/roles');
            const roles = res.data?.data ?? [];
            // Populate both the form select and the filter select
            ['user-role', 'filter-user-role'].forEach(id => {
                const select = document.getElementById(id);
                if (!select) return;
                const placeholder = id === 'filter-user-role' ? 'All Roles' : 'Select role...';
                select.innerHTML = '<option value="">' + placeholder + '</option>' +
                    roles.map(r => '<option value="' + r.id + '">' + this.esc(r.name) + '</option>').join('');
            });
        } catch {}
    }

    async loadUsers() {
        const tbody = document.getElementById('users-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';

        const params = { page: this.currentPage, per_page: 20 };
        const search  = document.getElementById('user-search')?.value;
        const roleId  = document.getElementById('filter-user-role')?.value;
        if (search) params.search  = search;
        if (roleId) params.role_id = roleId;

        try {
            const res = await apiClient.get('/users', { params });
            const { data: users, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(users);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Failed to load users.</td></tr>`;
        }
    }

    renderTable(users) {
        const tbody = document.getElementById('users-tbody');
        const canEdit   = window.utils?.hasAnyPermission(['edit-user', 'create-user']);
        const canDelete = window.utils?.hasAnyPermission(['delete-user', 'create-user']);

        if (!users.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>`;
            return;
        }

        tbody.innerHTML = users.map(u => {
            const statusCls = u.status === 'active' ? 'bg-success' : u.status === 'inactive' ? 'bg-secondary' : 'bg-warning text-dark';
            const editBtn = canEdit
                ? `<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="${u.id}">
                       <i class="fas fa-pencil-alt"></i></button>` : '';
            const delBtn = canDelete
                ? `<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${u.id}"
                       data-name="${this.esc(u.name)}"><i class="fas fa-trash"></i></button>` : '';
            return `<tr>
                <td>${this.esc(u.name)}</td>
                <td>${this.esc(u.email)}</td>
                <td><span class="badge bg-dark">${this.esc(u.role?.name ?? '—')}</span></td>
                <td class="text-center"><span class="badge ${statusCls}">${u.status ?? 'active'}</span></td>
                <td class="text-end">${editBtn}${delBtn}</td>
            </tr>`;
        }).join('');
    }

    renderPagination(pagination) {
        const info = document.getElementById('users-pagination-info');
        const ctrl = document.getElementById('users-pagination-controls');
        if (!pagination) return;
        if (info) info.textContent = `Showing ${pagination.from ?? 0}–${pagination.to ?? 0} of ${pagination.total}`;
        if (!ctrl) return;
        if (pagination.last_page <= 1) { ctrl.innerHTML = ''; return; }
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <button class="page-link" data-page="${i}">${i}</button></li>`;
        }
        html += '</ul></nav>';
        ctrl.innerHTML = html;
        ctrl.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => { this.currentPage = +btn.dataset.page; this.loadUsers(); });
        });
    }

    showModal(user = null) {
        this.editingId = user?.id ?? null;
        const form = document.getElementById('userForm');
        form.reset();
        window.utils?.clearValidationErrors(form);
        document.getElementById('userModalLabel').textContent = user ? 'Edit User' : 'Add User';
        document.getElementById('user-id').value = user?.id ?? '';

        const pwdLabel = document.getElementById('password-label');
        const pwdHint  = document.getElementById('password-hint');
        const pwdInput = document.getElementById('user-password');

        if (user) {
            document.getElementById('user-name').value   = user.name ?? '';
            document.getElementById('user-email').value  = user.email ?? '';
            document.getElementById('user-role').value   = user.role_id ?? '';
            document.getElementById('user-status').value = user.status ?? 'active';
            pwdLabel.innerHTML = 'Password';
            pwdHint.classList.remove('d-none');
            pwdInput.required = false;
        } else {
            pwdLabel.innerHTML = 'Password <span class="text-danger">*</span>';
            pwdHint.classList.add('d-none');
            pwdInput.required = true;
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).show();
    }

    async edit(id) {
        try {
            const res = await apiClient.get(`/users/${id}`);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load user.', 'error');
        }
    }

    async save() {
        const form = document.getElementById('userForm');
        const btn  = document.getElementById('btn-save-user');
        const spin = btn.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);

        const payload = {
            name:     document.getElementById('user-name').value.trim(),
            email:    document.getElementById('user-email').value.trim(),
            role_id:  document.getElementById('user-role').value,
            status:   document.getElementById('user-status').value,
        };
        const pwd = document.getElementById('user-password').value;
        if (pwd) payload.password = pwd;

        btn.disabled = true; spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put(`/users/${this.editingId}`, payload);
                window.utils?.showToast('User updated.', 'success');
            } else {
                await apiClient.post('/users', payload);
                window.utils?.showToast('User created.', 'success');
            }
            bootstrap.Modal.getInstance(document.getElementById('userModal'))?.hide();
            this.loadUsers();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            } else {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save user.', 'error');
            }
        } finally {
            btn.disabled = false; spin.classList.add('d-none');
        }
    }

    async remove(id, name) {
        // Users management is admin-only; simple confirm
        const ok = await window.utils?.showConfirmModal('Delete User',
            `Delete user <strong>${this.esc(name)}</strong>? This cannot be undone.`, 'Delete', 'btn-danger');
        if (ok) {
            try {
                await apiClient.delete(`/users/${id}`);
                window.utils?.showToast('User deleted.', 'success');
                this.loadUsers();
            } catch (err) {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to delete user.', 'error');
            }
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}
