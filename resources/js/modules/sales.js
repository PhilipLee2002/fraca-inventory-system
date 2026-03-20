import apiClient from '../api/client.js';

export class SalesModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
        this.products = [];
    }

    init() {
        this.bindEvents();
        this.loadSales();
        this.loadCustomers();
        this.loadProducts();
        const d = document.getElementById('sale-date');
        if (d) d.value = new Date().toISOString().split('T')[0];
    }

    bindEvents() {
        document.getElementById('sale-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => { this.currentPage = 1; this.loadSales(); }, 300);
        });
        ['filter-sale-status','filter-sale-from','filter-sale-to'].forEach(id =>
            document.getElementById(id)?.addEventListener('change', () => { this.currentPage = 1; this.loadSales(); })
        );
        document.getElementById('btn-clear-sale-filters')?.addEventListener('click', () => {
            ['sale-search','filter-sale-status','filter-sale-from','filter-sale-to']
                .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
            this.currentPage = 1; this.loadSales();
        });
        document.getElementById('btn-new-sale')?.addEventListener('click', () => this.showModal());
        document.getElementById('btn-save-sale')?.addEventListener('click', () => this.save());
        document.getElementById('btn-add-sale-row')?.addEventListener('click', () => this.addItemRow());
        document.getElementById('sales-tbody')?.addEventListener('click', e => {
            const v  = e.target.closest('[data-action="view"]');
            const ed = e.target.closest('[data-action="edit"]');
            const dl = e.target.closest('[data-action="delete"]');
            if (v)  this.viewSale(v.dataset.id);
            if (ed) this.editSale(ed.dataset.id);
            if (dl) this.deleteSale(dl.dataset.id, dl.dataset.ref);
        });
        document.getElementById('sale-items-body')?.addEventListener('input', e => {
            if (e.target.matches('.item-qty,.item-price')) this.recalcRow(e.target.closest('tr'));
        });
        document.getElementById('sale-items-body')?.addEventListener('change', e => {
            if (e.target.matches('.item-product')) this.onProductSelect(e.target);
        });
        document.getElementById('sale-items-body')?.addEventListener('click', e => {
            const rm = e.target.closest('.btn-remove-row');
            if (rm) { rm.closest('tr').remove(); this.recalcTotal(); }
        });
    }

    async loadCustomers() {
        try {
            const res = await apiClient.get('/customers', { params: { per_page: 200 } });
            const list = res.data?.data?.data ?? res.data?.data ?? [];
            const sel = document.getElementById('sale-customer');
            if (!sel) return;
            sel.innerHTML = '<option value="">Walk-in / No Customer</option>' +
                list.map(c => '<option value="' + c.id + '">' + this.esc((c.first_name ?? '') + ' ' + (c.last_name ?? '')) + '</option>').join('');
        } catch {}
    }

    async loadProducts() {
        try {
            const res = await apiClient.get('/products', { params: { per_page: 500 } });
            this.products = res.data?.data?.data ?? res.data?.data ?? [];
        } catch {}
    }

    async loadSales() {
        const tbody = document.getElementById('sales-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>';
        const params = { page: this.currentPage, per_page: 20 };
        const s  = document.getElementById('sale-search')?.value;
        const st = document.getElementById('filter-sale-status')?.value;
        const fr = document.getElementById('filter-sale-from')?.value;
        const to = document.getElementById('filter-sale-to')?.value;
        if (s)  params.search    = s;
        if (st) params.status    = st;
        if (fr) params.date_from = fr;
        if (to) params.date_to   = to;
        try {
            const res = await apiClient.get('/sales', { params });
            const { data: sales, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(sales);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load sales.</td></tr>';
        }
    }

    renderTable(sales) {
        const tbody   = document.getElementById('sales-tbody');
        const canEdit = window.utils?.hasAnyPermission(['edit-sale']);
        const canDel  = window.utils?.hasAnyPermission(['delete-sale']);
        if (!sales.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No sales found.</td></tr>';
            return;
        }
        const badge = s => {
            const m = { completed: 'success', pending: 'warning', cancelled: 'danger' };
            return '<span class="badge bg-' + (m[s] ?? 'secondary') + '">' + s + '</span>';
        };
        tbody.innerHTML = sales.map(s => {
            const cust = s.customer ? ((s.customer.first_name ?? '') + ' ' + (s.customer.last_name ?? '')).trim() : 'Walk-in';
            const eb = canEdit ? '<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="' + s.id + '"><i class="fas fa-pencil-alt"></i></button>' : '';
            const db = canDel  ? '<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="' + s.id + '" data-ref="' + this.esc(s.reference_number ?? s.id) + '"><i class="fas fa-trash"></i></button>' : '';
            return '<tr>' +
                '<td><button class="btn btn-link btn-sm p-0" data-action="view" data-id="' + s.id + '">' + this.esc(s.reference_number ?? '#' + s.id) + '</button></td>' +
                '<td>' + this.esc(cust) + '</td>' +
                '<td>' + (s.sale_date ?? (s.created_at ?? '').split('T')[0] ?? '---') + '</td>' +
                '<td class="text-end">' + window.formatKES(s.total_amount ?? 0) + '</td>' +
                '<td class="text-center">' + badge(s.status ?? 'pending') + '</td>' +
                '<td class="text-end">' + eb + db + '</td>' +
                '</tr>';
        }).join('');
    }

    renderPagination(p) {
        const info = document.getElementById('sales-pagination-info');
        const ctrl = document.getElementById('sales-pagination-controls');
        if (!p) return;
        if (info) info.textContent = 'Showing ' + (p.from ?? 0) + '-' + (p.to ?? 0) + ' of ' + p.total;
        if (!ctrl) return;
        if ((p.last_page ?? 1) <= 1) { ctrl.innerHTML = ''; return; }
        let h = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= p.last_page; i++)
            h += '<li class="page-item ' + (i === p.current_page ? 'active' : '') + '"><button class="page-link" data-page="' + i + '">' + i + '</button></li>';
        h += '</ul></nav>';
        ctrl.innerHTML = h;
        ctrl.querySelectorAll('[data-page]').forEach(b => b.addEventListener('click', () => { this.currentPage = +b.dataset.page; this.loadSales(); }));
    }

    async showModal(sale = null) {
        if (!this.products.length) await this.loadProducts();
        this.editingId = sale?.id ?? null;
        const form = document.getElementById('saleForm');
        const vb   = document.getElementById('sale-view-body');
        const sb   = document.getElementById('btn-save-sale');
        form.reset();
        form.classList.remove('d-none');
        vb.classList.add('d-none');
        vb.innerHTML = '';
        sb.classList.remove('d-none');
        window.utils?.clearValidationErrors(form);
        document.getElementById('saleModalLabel').textContent      = sale ? 'Edit Sale' : 'New Sale';
        document.getElementById('sale-date').value                 = sale?.sale_date ?? new Date().toISOString().split('T')[0];
        document.getElementById('sale-customer').value             = sale?.customer_id ?? '';
        document.getElementById('sale-payment-method').value       = sale?.payment_method ?? 'cash';
        document.getElementById('sale-status').value               = sale?.status ?? 'pending';
        document.getElementById('sale-notes').value                = sale?.notes ?? '';
        const ib = document.getElementById('sale-items-body');
        ib.innerHTML = '';
        if (sale?.items?.length) sale.items.forEach(i => this.addItemRow(i));
        else this.addItemRow();
        this.recalcTotal();
        new bootstrap.Modal(document.getElementById('saleModal')).show();
    }

    async viewSale(id) {
        try {
            const res  = await apiClient.get('/sales/' + id);
            const s    = res.data?.data;
            document.getElementById('saleForm').classList.add('d-none');
            document.getElementById('btn-save-sale').classList.add('d-none');
            const vb   = document.getElementById('sale-view-body');
            vb.classList.remove('d-none');
            const cust = s.customer ? ((s.customer.first_name ?? '') + ' ' + (s.customer.last_name ?? '')).trim() : 'Walk-in';
            const rows = (s.items ?? []).map(i =>
                '<tr>' +
                '<td>' + this.esc(i.product?.name ?? '---') + '</td>' +
                '<td class="text-center">' + i.quantity + '</td>' +
                '<td class="text-end">' + window.formatKES(i.unit_price ?? 0) + '</td>' +
                '<td class="text-end">' + window.formatKES(i.subtotal ?? (i.quantity * (i.unit_price ?? 0))) + '</td>' +
                '</tr>'
            ).join('');
            vb.innerHTML =
                '<div class="row g-3 mb-3">' +
                '<div class="col-md-3"><strong>Invoice:</strong><br>' + this.esc(s.reference_number ?? '#' + s.id) + '</div>' +
                '<div class="col-md-3"><strong>Customer:</strong><br>' + this.esc(cust) + '</div>' +
                '<div class="col-md-3"><strong>Date:</strong><br>' + (s.sale_date ?? '---') + '</div>' +
                '<div class="col-md-3"><strong>Status:</strong><br><span class="badge bg-' + (s.status === 'completed' ? 'success' : s.status === 'cancelled' ? 'danger' : 'warning') + '">' + s.status + '</span></div>' +
                '<div class="col-md-3"><strong>Payment:</strong><br>' + (s.payment_method ?? '---') + '</div>' +
                '<div class="col-md-9"><strong>Notes:</strong><br>' + this.esc(s.notes ?? '---') + '</div>' +
                '</div>' +
                '<table class="table table-sm">' +
                '<thead class="table-light"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '<tfoot><tr><td colspan="3" class="text-end fw-bold">Total:</td><td class="text-end fw-bold">' + window.formatKES(s.total_amount ?? 0) + '</td></tr></tfoot>' +
                '</table>';
            document.getElementById('saleModalLabel').textContent = 'Sale ' + (s.reference_number ?? '#' + s.id);
            new bootstrap.Modal(document.getElementById('saleModal')).show();
        } catch {
            window.utils?.showToast('Failed to load sale.', 'error');
        }
    }

    async editSale(id) {
        try {
            const res = await apiClient.get('/sales/' + id);
            this.showModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load sale.', 'error');
        }
    }

    addItemRow(item = null) {
        const tbody = document.getElementById('sale-items-body');
        const tr    = document.createElement('tr');
        const opts  = this.products.map(p =>
            '<option value="' + p.id + '" data-price="' + (p.selling_price ?? p.unit_price ?? 0) + '"' +
            (item?.product_id == p.id ? ' selected' : '') + '>' + this.esc(p.name) + '</option>'
        ).join('');
        tr.innerHTML =
            '<td><select class="form-select form-select-sm item-product"><option value="">Select product...</option>' + opts + '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" value="' + (item?.quantity ?? 1) + '" min="1"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" value="' + parseFloat(item?.unit_price ?? 0).toFixed(2) + '" min="0" step="0.01"></td>' +
            '<td class="text-end item-subtotal">' + parseFloat((item?.quantity ?? 1) * (item?.unit_price ?? 0)).toFixed(2) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(tr);
        this.recalcTotal();
    }

    onProductSelect(sel) {
        const price = parseFloat(sel.options[sel.selectedIndex]?.dataset.price ?? 0);
        const row   = sel.closest('tr');
        row.querySelector('.item-price').value = price.toFixed(2);
        this.recalcRow(row);
    }

    recalcRow(row) {
        const qty   = parseFloat(row.querySelector('.item-qty')?.value ?? 0);
        const price = parseFloat(row.querySelector('.item-price')?.value ?? 0);
        row.querySelector('.item-subtotal').textContent = (qty * price).toFixed(2);
        this.recalcTotal();
    }

    recalcTotal() {
        let t = 0;
        document.querySelectorAll('#sale-items-body .item-subtotal').forEach(el => { t += parseFloat(el.textContent ?? 0); });
        const el = document.getElementById('sale-total');
        if (el) el.textContent = window.formatKES(t);
    }

    async save() {
        const form = document.getElementById('saleForm');
        const btn  = document.getElementById('btn-save-sale');
        if (!btn) { console.error('btn-save-sale not found'); return; }
        const spin = btn.querySelector('.spinner-border');
        if (!spin) { console.error('spinner not found in btn-save-sale'); }
        window.utils?.clearValidationErrors(form);
        const items = [];
        document.querySelectorAll('#sale-items-body tr').forEach(row => {
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
            customer_id:    document.getElementById('sale-customer').value || null,
            sale_date:      document.getElementById('sale-date').value,
            payment_method: document.getElementById('sale-payment-method').value,
            status:         document.getElementById('sale-status').value,
            notes:          document.getElementById('sale-notes').value.trim() || null,
            items,
        };
        btn.disabled = true;
        spin.classList.remove('d-none');
        try {
            if (this.editingId) {
                await apiClient.put('/sales/' + this.editingId, payload);
                window.utils?.showToast('Sale updated.', 'success');
            } else {
                await apiClient.post('/sales', payload);
                window.utils?.showToast('Sale created.', 'success');
            }
            bootstrap.Modal.getInstance(document.getElementById('saleModal'))?.hide();
            this.loadSales();
        } catch (err) {
            if (err.response?.status === 422)
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            else
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save sale.', 'error');
        } finally {
            btn.disabled = false;
            spin.classList.add('d-none');
        }
    }

    async deleteSale(id, ref) {
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
                'Delete Sale',
                'Delete sale <strong>' + this.esc(ref) + '</strong>?',
                'Delete', 'btn-danger'
            );
            if (ok) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete('/sales/' + id);
            window.utils?.showToast('Sale deleted.', 'success');
            this.loadSales();
        } catch (err) {
            if (err.response?.status !== 401 && err.response?.status !== 403)
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to delete.', 'error');
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
}