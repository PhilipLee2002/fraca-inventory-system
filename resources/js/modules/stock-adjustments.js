import apiClient from '../api/client.js';
import { cachedFetch } from '../utils/cache.js';

export class StockAdjustmentsModule {
    constructor() {
        this.currentPage = 1;
    }

    init() {
        this.bindEvents();
        this.loadProducts();
        this.loadAdjustments();
    }

    bindEvents() {
        document.getElementById('btn-submit-adjustment')?.addEventListener('click', () => this.submitAdjustment());
    }

    async loadProducts() {
        const select = document.getElementById('adj-product');
        if (!select) return;
        try {
            const products = await cachedFetch('products-all', async () => {
                const res = await apiClient.get('/products', { params: { per_page: 500 } });
                return res.data?.data?.data ?? [];
            });
            select.innerHTML = '<option value="">Select product...</option>' +
                products.map(p => `<option value="${p.id}">${this.esc(p.name)} (Stock: ${p.current_stock})</option>`).join('');
        } catch {
            // non-fatal
        }
    }

    async loadAdjustments() {
        const tbody = document.getElementById('adjustments-tbody');
        if (!tbody) return;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4">
            <span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>`;
        try {
            const res = await apiClient.get('/stock-adjustments', { params: { page: this.currentPage, per_page: 20 } });
            const { data: adjustments, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(adjustments);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load adjustments.</td></tr>`;
        }
    }

    renderTable(adjustments) {
        const tbody = document.getElementById('adjustments-tbody');
        if (!adjustments.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No adjustments found.</td></tr>`;
            return;
        }
        tbody.innerHTML = adjustments.map(a => {
            const change = parseInt(a.quantity_change);
            const cls    = change >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold';
            const sign   = change >= 0 ? '+' : '';
            const date   = a.created_at ? new Date(a.created_at).toLocaleDateString() : '—';
            const type   = a.transaction_type ?? a.type ?? 'adjustment';
            const user   = a.user?.name ?? '—';
            return `<tr>
                <td>${this.esc(a.product?.name ?? '—')}</td>
                <td><span class="badge bg-secondary">${this.esc(type)}</span></td>
                <td class="text-center ${cls}">${sign}${change}</td>
                <td class="text-muted small">${this.esc(a.reason ?? a.notes ?? '—')}</td>
                <td class="small">${this.esc(user)}</td>
                <td class="small">${date}</td>
            </tr>`;
        }).join('');
    }

    renderPagination(pagination) {
        const info = document.getElementById('adj-pagination-info');
        const ctrl = document.getElementById('adj-pagination-controls');
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
            btn.addEventListener('click', () => { this.currentPage = +btn.dataset.page; this.loadAdjustments(); });
        });
    }

    async submitAdjustment() {
        const form = document.getElementById('adjustmentForm');
        const btn  = document.getElementById('btn-submit-adjustment');
        const spin = btn.querySelector('.spinner-border');
        window.utils?.clearValidationErrors(form);

        const payload = {
            product_id:      document.getElementById('adj-product').value,
            quantity_change: parseInt(document.getElementById('adj-quantity').value),
            reason:          document.getElementById('adj-reason').value.trim(),
        };

        if (!payload.product_id || isNaN(payload.quantity_change) || !payload.reason) {
            window.utils?.showToast('Please fill in all required fields.', 'error');
            return;
        }

        btn.disabled = true; spin.classList.remove('d-none');
        try {
            await apiClient.post('/stock-adjustments', payload);
            window.utils?.showToast('Stock adjustment submitted.', 'success');
            form.reset();
            // Refresh product list cache and table
            const { cacheClear } = await import('../utils/cache.js');
            cacheClear('products-all');
            await this.loadProducts();
            this.currentPage = 1;
            this.loadAdjustments();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            }
        } finally {
            btn.disabled = false; spin.classList.add('d-none');
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}
