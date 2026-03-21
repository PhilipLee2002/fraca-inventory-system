import apiClient from '../api/client.js';

export class PurchasesModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
        this.products = [];
    }

    init() {
        this.bindEvents();
        this.loadPurchases();
        this.loadSuppliers();
        this.loadProducts();
        const d = document.getElementById('purchase-date');
        if (d) d.value = new Date().toISOString().split('T')[0];
    }

    bindEvents() {
        document.getElementById('purchase-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadPurchases(); }, 300);
        });
        ['filter-purchase-status', 'filter-purchase-from', 'filter-purchase-to'].forEach(id =>
            document.getElementById(id)?.addEventListener('change', () => { this.currentPage = 1; this.loadPurchases(); })
        );
        document.getElementById('btn-clear-purchase-filters')?.addEventListener('click', () => {
            ['purchase-search', 'filter-purchase-status', 'filter-purchase-from', 'filter-purchase-to']
                .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
            this.currentPage = 1;
            this.loadPurchases();
        });
        document.getElementById('btn-new-purchase')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-purchase')?.addEventListener('click', () => this.save());
        document.getElementById('btn-add-purchase-row')?.addEventListener('click', () => this.addItemRow());
        document.getElementById('purchases-tbody')?.addEventListener('click', e => {
            const v  = e.target.closest('[data-action="view"]');
            const ed = e.target.closest('[data-action="edit"]');
            const dl = e.target.closest('[data-action="delete"]');
            if (v)  this.viewPurchase(v.dataset.id);
            if (ed) this.editPurchase(ed.dataset.id);
            if (dl) this.deletePurchase(dl.dataset.id, dl.dataset.ref);
        });
        document.getElementById('purchase-items-body')?.addEventListener('input', e => {
            if (e.target.matches('.item-qty,.item-price')) this.recalcRow(e.target.closest('tr'));
        });
        document.getElementById('purchase-items-body')?.addEventListener('click', e => {
            const rm = e.target.closest('.btn-remove-row');
            if (rm) { rm.closest('tr').remove(); this.recalcTotal(); }
        });
    }

    async loadSuppliers() {
        try {
            const res  = await apiClient.get('/suppliers', { params: { per_page: 200 } });
            const list = res.data?.data?.data ?? res.data?.data ?? [];
            const sel  = document.getElementById('purchase-supplier');
            if (!sel) return;
            sel.innerHTML = '<option value="">Select supplier...</option>' +
                list.map(s => '<option value="' + s.id + '">' + this.esc(s.name) + '</option>').join('');
        } catch {}
    }

    async loadProducts() {
        try {
            const res = await apiClient.get('/products', { params: { per_page: 500 } });
            this.products = res.data?.data?.data ?? res.data?.data ?? [];
        } catch {}
    }

    async loadPurchases() {
        const tbody = document.getElementById('purchases-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';
        const params = { page: this.currentPage, per_page: 20 };
        const s  = document.getElementById('purchase-search')?.value;
        const st = document.getElementById('filter-purchase-status')?.value;
        const fr = document.getElementById('filter-purchase-from')?.value;
        const to = document.getElementById('filter-purchase-to')?.value;
        if (s)  params.search    = s;
        if (st) params.status    = st;
        if (fr) params.date_from = fr;
        if (to) params.date_to   = to;
        try {
            const res = await apiClient.get('/purchases', { params });
            const { data: purchases, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(purchases);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load purchases.</td></tr>';
        }
    }

    renderTable(purchases) {
        const tbody   = document.getElementById('purchases-tbody');
        const canEdit = window.utils?.hasAnyPermission(['edit-purchase']);
        const canDel  = window.utils?.hasAnyPermission(['delete-purchase']);
        if (!purchases.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No purchases found.</td></tr>';
            return;
        }
        const badge = s => {
            const m = { received: 'success', pending: 'warning', cancelled: 'danger' };
            return '<span class="badge bg-' + (m[s] ?? 'secondary') + '">' + s + '</span>';
        };
        tbody.innerHTML = purchases.map(p => {
            const eb = canEdit ? '<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="' + p.id + '"><i class="fas fa-pencil-alt"></i></button>' : '';
            const db = canDel  ? '<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="' + p.id + '" data-ref="' + this.esc(p.purchase_number ?? p.id) + '"><i class="fas fa-trash"></i></button>' : '';
            return '<tr>' +
                '<td><button class="btn btn-link btn-sm p-0" data-action="view" data-id="' + p.id + '">' + this.esc(p.purchase_number ?? '#' + p.id) + '</button></td>' +
                '<td>' + this.esc(p.supplier?.name ?? '---') + '</td>' +
                '<td>' + (p.purchase_date ?? (p.created_at ?? '').split('T')[0] ?? '---') + '</td>' +
                '<td class="text-end">' + window.formatKES(p.total_amount ?? 0) + '</td>' +
                '<td class="text-center">' + badge(p.status ?? 'pending') + '</td>' +
                '<td class="text-end">' + eb + db + '</td>' +
                '</tr>';
        }).join('');
    }

    renderPagination(p) {
        const info = document.getElementById('purchases-pagination-info');
        const ctrl = document.getElementById('purchases-pagination-controls');
        if (!p) return;
        if (info) info.textContent = 'Showing ' + (p.from ?? 0) + '-' + (p.to ?? 0) + ' of ' + p.total;
        if (!ctrl) return;
        if ((p.last_page ?? 1) <= 1) { ctrl.innerHTML = ''; return; }
        let h = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= p.last_page; i++)
            h += '<li class="page-item ' + (i === p.current_page ? 'active' : '') + '"><button class="page-link" data-page="' + i + '">' + i + '</button></li>';
        h += '</ul></nav>';
        ctrl.innerHTML = h;
        ctrl.querySelectorAll('[data-page]').forEach(b =>
            b.addEventListener('click', () => { this.currentPage = +b.dataset.page; this.loadPurchases(); })
        );
    }

    async showModal(purchase = null) {
        if (!this.products.length) await this.loadProducts();
        this.editingId = purchase?.id ?? null;
        const form = document.getElementById('purchaseForm');
        const vb   = document.getElementById('purchase-view-body');
        const sb   = document.getElementById('btn-save-purchase');
        form.reset();
        form.classList.remove('d-none');
        vb.classList.add('d-none');
        vb.innerHTML = '';
        sb.classList.remove('d-none');
        window.utils?.clearValidationErrors(form);
        document.getElementById('purchaseModalLabel').textContent  = purchase ? 'Edit Purchase' : 'New Purchase';
        document.getElementById('purchase-date').value             = purchase?.purchase_date ?? new Date().toISOString().split('T')[0];
        document.getElementById('purchase-supplier').value         = purchase?.supplier_id ?? '';
        document.getElementById('purchase-payment-method').value   = purchase?.payment_method ?? 'cash';
        document.getElementById('purchase-status').value           = purchase?.status ?? 'pending';
        document.getElementById('purchase-notes').value            = purchase?.notes ?? '';
        const ib = document.getElementById('purchase-items-body');
        ib.innerHTML = '';
        if (purchase?.items?.length) purchase.items.forEach(i => this.addItemRow(i));
        else this.addItemRow();
        this.recalcTotal();
        new bootstrap.Modal(document.getElementById('purchaseModal')).show();
    }

    async viewPurchase(id) {
        try {
            const res = await apiClient.get('/purchases/' + id);
            const p   = res.data?.data;
            document.getElementById('purchaseForm').classList.add('d-none');
            document.getElementById('btn-save-purchase').classList.add('d-none');
            const vb  = document.getElementById('purchase-view-body');
            vb.classList.remove('d-none');
            const rows = (p.items ?? []).map(i =>
                '<tr>' +
                '<td>' + this.esc(i.product?.name ?? '---') + '</td>' +
                '<td class="text-center">' + i.quantity + '</td>' +
                '<td class="text-end">' + window.formatKES(i.unit_price ?? i.cost_price ?? 0) + '</td>' +
                '<td class="text-end">' + window.formatKES(i.subtotal ?? (i.quantity * (i.unit_price ?? i.cost_price ?? 0))) + '</td>' +
                '</tr>'
            ).join('');
            vb.innerHTML =
                '<div class="row g-3 mb-3">' +
                '<div class="col-md-3"><strong>PO Number:</strong><br>' + this.esc(p.purchase_number ?? '#' + p.id) + '</div>' +
                '<div class="col-md-3"><strong>Supplier:</strong><br>' + this.esc(p.supplier?.name ?? '---') + '</div>' +
                '<div class="col-md-3"><strong>Date:</strong><br>' + (p.purchase_date ?? '---') + '</div>' +
                '<div class="col-md-3"><strong>Status:</strong><br><span class="badge bg-' + (p.status === 'received' ? 'success' : p.status === 'cancelled' ? 'danger' : 'warning') + '">' + p.status + '</span></div>' +
                '<div class="col-md-3"><strong>Payment:</strong><br>' + (p.payment_method ?? '---') + '</div>' +
                '<div class="col-md-9"><strong>Notes:</strong><br>' + this.esc(p.notes ?? '---') + '</div>' +
                '</div>' +
                '<table class="table table-sm">' +
                '<thead class="table-light"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Subtotal</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '<tfoot><tr><td colspan="3" class="text-end fw-bold">Total:</td><td class="text-end fw-bold">' + window.formatKES(p.total_amount ?? 0) + '</td></tr></tfoot>' +
                '</table>';
            document.getElementById('purchaseModalLabel').textContent = 'Purchase ' + (p.purchase_number ?? '#' + p.id);
            new bootstrap.Modal(document.getElementById('purchaseModal')).show();
        } catch {
            window.utils?.showToast('Failed to load purchase.', 'error');
        }
    }

    async editPurchase(id) {
        try {
            const res = await apiClient.get('/purchases/' + id);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load purchase.', 'error');
        }
    }

    addItemRow(item = null) {
        const tbody = document.getElementById('purchase-items-body');
        const tr    = document.createElement('tr');
        const opts  = this.products.map(p =>
            '<option value="' + p.id + '" data-cost="' + (p.cost_price ?? 0) + '"' +
            (item?.product_id == p.id ? ' selected' : '') + '>' + this.esc(p.name) + '</option>'
        ).join('');
        tr.innerHTML =
            '<td><select class="form-select form-select-sm item-product"><option value="">Select product...</option>' + opts + '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" value="' + (item?.quantity ?? 1) + '" min="1"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" value="' + parseFloat(item?.unit_price ?? item?.cost_price ?? 0).toFixed(2) + '" min="0" step="0.01"></td>' +
            '<td class="text-end item-subtotal">' + parseFloat((item?.quantity ?? 1) * (item?.unit_price ?? item?.cost_price ?? 0)).toFixed(2) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(tr);
        this.recalcTotal();
    }

    recalcRow(row) {
        const qty = parseFloat(row.querySelector('.item-qty')?.value ?? 0);
        const pr  = parseFloat(row.querySelector('.item-price')?.value ?? 0);
        row.querySelector('.item-subtotal').textContent = (qty * pr).toFixed(2);
        this.recalcTotal();
    }

    recalcTotal() {
        let t = 0;
        document.querySelectorAll('#purchase-items-body .item-subtotal').forEach(el => {
            t += parseFloat(el.textContent ?? 0);
        });
        const el = document.getElementById('purchase-total');
        if (el) el.textContent = window.formatKES(t);
    }

    async save() {
        const form = document.getElementById('purchaseForm');
        const btn  = document.getElementById('btn-save-purchase');
        if (!btn) return;
        const spin = btn.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);
        const items = [];
        document.querySelectorAll('#purchase-items-body tr').forEach(row => {
            const pid = row.querySelector('.item-product')?.value;
            const qty = parseFloat(row.querySelector('.item-qty')?.value ?? 0);
            const pr  = parseFloat(row.querySelector('.item-price')?.value ?? 0);
            if (pid) items.push({ product_id: parseInt(pid), quantity: parseInt(qty), unit_price: parseFloat(pr) });
        });
        if (!items.length) {
            window.utils?.showToast('Please add at least one product.', 'error');
            return;
        }
        const payload = {
            supplier_id:    document.getElementById('purchase-supplier').value || null,
            purchase_date:  document.getElementById('purchase-date').value,
            payment_method: document.getElementById('purchase-payment-method').value,
            status:         document.getElementById('purchase-status').value,
            notes:          document.getElementById('purchase-notes').value.trim() || null,
            items,
        };
        btn.disabled = true;
        if (spin) spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put('/purchases/' + this.editingId, payload);
                window.utils?.showToast('Purchase updated.', 'success');
            } else {
                await apiClient.post('/purchases', payload);
                window.utils?.showToast('Purchase created.', 'success');
            }
            bootstrap.Modal.getInstance(document.getElementById('purchaseModal'))?.hide();
            this.loadPurchases();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            } else {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save purchase.', 'error');
            }
        } finally {
            btn.disabled = false;
            if (spin) spin.classList.add('d-none');
        }
    }

    async deletePurchase(id, ref) {
        const role = window.utils?.getUserRole();
        if (role === 'manager') {
            try {
                await window.utils?.showAdminVerifyModal(async ({ email, password }) => {
                    const r = await apiClient.post('/verify-admin', { email, password });
                    if (!r.data?.data?.verified) throw new Error('failed');
                });
                await this.doDelete(id);
            } catch {}
        } else {
            const ok = await window.utils?.showConfirmModal(
                'Delete Purchase',
                'Delete purchase <strong>' + this.esc(ref) + '</strong>?',
                'Delete', 'btn-danger'
            );
            if (ok) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete('/purchases/' + id);
            window.utils?.showToast('Purchase deleted.', 'success');
            this.loadPurchases();
        } catch (err) {
            if (err.response?.status !== 401 && err.response?.status !== 403)
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to delete.', 'error');
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
}