import apiClient from '../api/client.js';
import { exportToCSV } from '../utils/export.js';
import { cachedFetch } from '../utils/cache.js';

export class ReportsModule {
    constructor() {
        this.activeReport = 'inventory-valuation';
        this.lastData = null;
    }

    init() {
        this.bindEvents();
        this.loadFilters();
        this.updateFilterVisibility();
    }

    bindEvents() {
        document.querySelectorAll('#reportTabs [data-report]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', () => {
                this.activeReport = tab.dataset.report;
                this.updateFilterVisibility();
                this.lastData = null;
                document.getElementById('btn-export-csv').disabled = true;
                document.getElementById('report-summary').classList.add('d-none');
            });
        });

        document.getElementById('btn-generate-report')?.addEventListener('click', () => this.generateReport());
        document.getElementById('btn-export-csv')?.addEventListener('click', () => this.exportCSV());
    }

    async loadFilters() {
        try {
            const [cats, prods, sups, custs] = await Promise.all([
                cachedFetch('categories', async () => {
                    const r = await apiClient.get('/categories'); return r.data?.data ?? [];
                }),
                cachedFetch('products-all', async () => {
                    const r = await apiClient.get('/products', { params: { per_page: 500 } });
                    return r.data?.data?.data ?? [];
                }),
                cachedFetch('suppliers', async () => {
                    const r = await apiClient.get('/suppliers', { params: { per_page: 500 } });
                    return r.data?.data?.data ?? r.data?.data ?? [];
                }),
                cachedFetch('customers', async () => {
                    const r = await apiClient.get('/customers', { params: { per_page: 500 } });
                    return r.data?.data?.data ?? r.data?.data ?? [];
                }),
            ]);

            const catSel = document.getElementById('filter-report-category');
            if (catSel) catSel.innerHTML = '<option value="">All Categories</option>' +
                cats.map(c => '<option value="' + c.id + '">' + this.esc(c.name) + '</option>').join('');

            const supSel = document.getElementById('filter-report-supplier');
            if (supSel) supSel.innerHTML = '<option value="">All Suppliers</option>' +
                sups.map(s => '<option value="' + s.id + '">' + this.esc(s.name) + '</option>').join('');

            const custSel = document.getElementById('filter-report-customer');
            if (custSel) custSel.innerHTML = '<option value="">All Customers</option>' +
                custs.map(c => {
                    const name = ((c.first_name ?? '') + ' ' + (c.last_name ?? '')).trim();
                    return '<option value="' + c.id + '">' + this.esc(name) + '</option>';
                }).join('');

            const prodSel = document.getElementById('filter-report-product');
            if (prodSel) prodSel.innerHTML = '<option value="">All Products</option>' +
                prods.map(p => '<option value="' + p.id + '">' + this.esc(p.name) + '</option>').join('');
        } catch {}
    }

    updateFilterVisibility() {
        const showDate     = ['sales', 'profit-loss', 'stock-movement', 'purchases'].includes(this.activeReport);
        const showCategory = ['inventory-valuation', 'sales', 'stock-levels'].includes(this.activeReport);
        const showSupplier = ['inventory-valuation', 'purchases'].includes(this.activeReport);
        const showCustomer = ['sales'].includes(this.activeReport);
        const showProduct  = ['stock-movement'].includes(this.activeReport);
        const showGroupBy  = ['profit-loss'].includes(this.activeReport);

        document.querySelectorAll('.date-filter').forEach(el => el.classList.toggle('d-none', !showDate));
        document.querySelectorAll('.category-filter').forEach(el => el.classList.toggle('d-none', !showCategory));
        document.querySelectorAll('.supplier-filter').forEach(el => el.classList.toggle('d-none', !showSupplier));
        document.querySelectorAll('.customer-filter').forEach(el => el.classList.toggle('d-none', !showCustomer));
        document.querySelectorAll('.product-filter').forEach(el => el.classList.toggle('d-none', !showProduct));
        document.querySelectorAll('.groupby-filter').forEach(el => el.classList.toggle('d-none', !showGroupBy));
    }

    async generateReport() {
        const btn  = document.getElementById('btn-generate-report');
        const spin = btn.querySelector('.spinner-border');
        btn.disabled = true; spin.classList.remove('d-none');

        const params = {};
        const startDate = document.getElementById('filter-start-date')?.value;
        const endDate   = document.getElementById('filter-end-date')?.value;
        const catId     = document.getElementById('filter-report-category')?.value;
        const supId     = document.getElementById('filter-report-supplier')?.value;
        const custId    = document.getElementById('filter-report-customer')?.value;
        const prodId    = document.getElementById('filter-report-product')?.value;
        const groupBy   = document.getElementById('filter-group-by')?.value;

        if (startDate) params.start_date  = startDate;
        if (endDate)   params.end_date    = endDate;
        if (catId)     params.category_id = catId;
        if (supId)     params.supplier_id = supId;
        if (custId)    params.customer_id = custId;
        if (prodId)    params.product_id  = prodId;
        if (groupBy)   params.group_by    = groupBy;

        try {
            const res = await apiClient.get('/reports/' + this.activeReport, { params });
            this.lastData = res.data?.data;
            this.renderResults(this.lastData);
            document.getElementById('btn-export-csv').disabled = false;
        } catch {
            window.utils?.showToast('Failed to generate report.', 'error');
        } finally {
            btn.disabled = false; spin.classList.add('d-none');
        }
    }

    renderResults(data) {
        switch (this.activeReport) {
            case 'inventory-valuation': return this.renderInventory(data);
            case 'sales':               return this.renderSales(data);
            case 'profit-loss':         return this.renderPL(data);
            case 'stock-movement':      return this.renderMovement(data);
            case 'purchases':           return this.renderPurchases(data);
            case 'stock-levels':        return this.renderStockLevels(data);
        }
    }

    renderInventory(data) {
        const products = data?.products ?? [];
        const summary  = data?.summary ?? {};
        this.renderSummaryCards([
            { label: 'Total Products',  value: summary.total_products ?? 0,                                    icon: 'fa-box',        color: 'primary' },
            { label: 'Total Stock',     value: summary.total_items_in_stock ?? 0,                              icon: 'fa-cubes',      color: 'info' },
            { label: 'Cost Valuation',  value: '$' + parseFloat(summary.total_valuation ?? 0).toFixed(2),     icon: 'fa-dollar-sign',color: 'warning' },
            { label: 'Potential Profit',value: '$' + parseFloat(summary.total_potential_profit ?? 0).toFixed(2), icon: 'fa-chart-line', color: 'success' },
        ]);

        const tbody = document.getElementById('tbody-inventory');
        if (!products.length) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = products.map(p =>
            '<tr>' +
            '<td>' + this.esc(p.name) + '</td>' +
            '<td>' + this.esc(p.category?.name ?? '—') + '</td>' +
            '<td class="text-center">' + p.current_stock + '</td>' +
            '<td class="text-end">$' + parseFloat(p.valuation ?? 0).toFixed(2) + '</td>' +
            '<td class="text-end">$' + parseFloat(p.potential_revenue ?? 0).toFixed(2) + '</td>' +
            '<td class="text-end text-success">$' + parseFloat(p.potential_profit ?? 0).toFixed(2) + '</td>' +
            '</tr>'
        ).join('');
    }

    renderSales(data) {
        const sales   = data?.sales?.data ?? [];
        const summary = data?.summary ?? {};
        this.renderSummaryCards([
            { label: 'Total Revenue',  value: '$' + parseFloat(summary.total_sales ?? 0).toFixed(2),        icon: 'fa-dollar-sign', color: 'success' },
            { label: 'Transactions',   value: summary.total_transactions ?? 0,                               icon: 'fa-receipt',     color: 'primary' },
            { label: 'Avg Sale Value', value: '$' + parseFloat(summary.average_sale_value ?? 0).toFixed(2), icon: 'fa-chart-bar',   color: 'info' },
        ]);

        const tbody = document.getElementById('tbody-sales');
        if (!sales.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = sales.map(s => {
            const customer = s.customer ? ((s.customer.first_name ?? '') + ' ' + (s.customer.last_name ?? '')).trim() : '—';
            const statusCls = s.status === 'completed' ? 'bg-success' : s.status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark';
            return '<tr>' +
                '<td><code class="small">' + this.esc(s.invoice_number) + '</code></td>' +
                '<td>' + this.esc(customer) + '</td>' +
                '<td>' + (s.sale_date ?? '—') + '</td>' +
                '<td class="text-end">$' + parseFloat(s.total_amount ?? 0).toFixed(2) + '</td>' +
                '<td class="text-center"><span class="badge ' + statusCls + '">' + s.status + '</span></td>' +
                '</tr>';
        }).join('');
    }

    renderPL(data) {
        const breakdown = data?.breakdown ?? [];
        const summary   = data?.summary ?? {};
        const profitCls = (summary.profit ?? 0) >= 0 ? 'success' : 'danger';
        this.renderSummaryCards([
            { label: 'Revenue',    value: '$' + parseFloat(summary.revenue ?? 0).toFixed(2),  icon: 'fa-arrow-up',      color: 'success' },
            { label: 'Expenses',   value: '$' + parseFloat(summary.expenses ?? 0).toFixed(2), icon: 'fa-arrow-down',    color: 'danger' },
            { label: 'Net Profit', value: '$' + parseFloat(summary.profit ?? 0).toFixed(2),   icon: 'fa-balance-scale', color: profitCls },
            { label: 'Margin',     value: (summary.margin ?? 0) + '%',                        icon: 'fa-percent',       color: 'info' },
        ]);

        const tbody = document.getElementById('tbody-pl');
        if (!breakdown.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = breakdown.map(row => {
            const profitColor = row.profit >= 0 ? 'text-success' : 'text-danger';
            return '<tr>' +
                '<td>' + row.date + '</td>' +
                '<td class="text-end text-success">$' + parseFloat(row.revenue).toFixed(2) + '</td>' +
                '<td class="text-end text-danger">$' + parseFloat(row.expenses).toFixed(2) + '</td>' +
                '<td class="text-end ' + profitColor + ' fw-semibold">$' + parseFloat(row.profit).toFixed(2) + '</td>' +
                '<td class="text-center">' + row.transactions + '</td>' +
                '</tr>';
        }).join('');
    }

    renderMovement(data) {
        const movements = data?.movements?.data ?? [];
        const summary   = data?.summary ?? {};
        this.renderSummaryCards([
            { label: 'Total In',  value: '+' + (summary.total_in ?? 0),  icon: 'fa-plus-circle',  color: 'success' },
            { label: 'Total Out', value: '-' + (summary.total_out ?? 0), icon: 'fa-minus-circle', color: 'danger' },
        ]);

        const tbody = document.getElementById('tbody-movement');
        if (!movements.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = movements.map(m => {
            const change = parseInt(m.quantity_change);
            const cls    = change >= 0 ? 'text-success' : 'text-danger';
            const sign   = change >= 0 ? '+' : '';
            const date   = m.created_at ? new Date(m.created_at).toLocaleDateString() : '—';
            return '<tr>' +
                '<td>' + this.esc(m.product?.name ?? '—') + '</td>' +
                '<td><code class="small">' + this.esc(m.product?.sku ?? '—') + '</code></td>' +
                '<td class="text-center ' + cls + ' fw-semibold">' + sign + change + '</td>' +
                '<td><span class="badge bg-secondary">' + this.esc(m.transaction_type ?? '—') + '</span></td>' +
                '<td class="small">' + date + '</td>' +
                '</tr>';
        }).join('');
    }

    renderPurchases(data) {
        const purchases = data?.purchases?.data ?? data?.data ?? [];
        const summary   = data?.summary ?? {};
        this.renderSummaryCards([
            { label: 'Total Purchases', value: '$' + parseFloat(summary.total_amount ?? 0).toFixed(2), icon: 'fa-shopping-cart', color: 'primary' },
            { label: 'Transactions',    value: summary.total_transactions ?? purchases.length,          icon: 'fa-receipt',       color: 'info' },
        ]);

        const tbody = document.getElementById('tbody-purchases');
        if (!purchases.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = purchases.map(p => {
            const statusMap = { pending: 'bg-warning text-dark', received: 'bg-success', cancelled: 'bg-danger' };
            const statusCls = statusMap[p.status] ?? 'bg-secondary';
            return '<tr>' +
                '<td><code class="small">' + this.esc(p.purchase_number) + '</code></td>' +
                '<td>' + this.esc(p.supplier?.name ?? '—') + '</td>' +
                '<td>' + (p.purchase_date ?? '—') + '</td>' +
                '<td class="text-end">$' + parseFloat(p.total_amount ?? 0).toFixed(2) + '</td>' +
                '<td class="text-center"><span class="badge ' + statusCls + '">' + p.status + '</span></td>' +
                '</tr>';
        }).join('');
    }

    renderStockLevels(data) {
        const products = data?.products ?? data?.data ?? [];
        const summary  = data?.summary ?? {};
        this.renderSummaryCards([
            { label: 'Total Products', value: summary.total_products ?? products.length, icon: 'fa-box',                color: 'primary' },
            { label: 'Low Stock',      value: summary.low_stock ?? 0,                    icon: 'fa-exclamation-triangle',color: 'warning' },
            { label: 'Out of Stock',   value: summary.out_of_stock ?? 0,                 icon: 'fa-times-circle',        color: 'danger' },
        ]);

        const tbody = document.getElementById('tbody-stock-levels');
        if (!products.length) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No data.</td></tr>'; return; }
        tbody.innerHTML = products.map(p => {
            const isOut = p.current_stock === 0;
            const isLow = !isOut && p.current_stock <= p.reorder_level;
            const badge = isOut
                ? '<span class="badge bg-danger">Out of Stock</span>'
                : isLow
                    ? '<span class="badge bg-warning text-dark">Low Stock</span>'
                    : '<span class="badge bg-success">OK</span>';
            const stockCls = isOut ? 'text-danger fw-bold' : isLow ? 'text-warning fw-semibold' : '';
            return '<tr>' +
                '<td>' + this.esc(p.name) + '</td>' +
                '<td><code class="small">' + this.esc(p.sku ?? '—') + '</code></td>' +
                '<td>' + this.esc(p.category?.name ?? '—') + '</td>' +
                '<td class="text-center ' + stockCls + '">' + p.current_stock + '</td>' +
                '<td class="text-center">' + (p.reorder_level ?? '—') + '</td>' +
                '<td class="text-center">' + badge + '</td>' +
                '</tr>';
        }).join('');
    }

    renderSummaryCards(cards) {
        const wrap = document.getElementById('report-summary');
        if (!wrap) return;
        wrap.innerHTML = cards.map(c =>
            '<div class="col-sm-6 col-lg-3">' +
                '<div class="card text-center">' +
                    '<div class="card-body py-3">' +
                        '<i class="fas ' + c.icon + ' fa-lg text-' + c.color + ' mb-2"></i>' +
                        '<div class="fw-bold fs-5">' + c.value + '</div>' +
                        '<div class="text-muted small">' + c.label + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        ).join('');
        wrap.classList.remove('d-none');
    }

    exportCSV() {
        if (!this.lastData) return;
        switch (this.activeReport) {
            case 'inventory-valuation': {
                const rows = (this.lastData.products ?? []).map(p => [
                    p.name, p.category?.name ?? '', p.current_stock,
                    p.valuation, p.potential_revenue, p.potential_profit,
                ]);
                exportToCSV(['Product','Category','Stock','Cost Value','Potential Revenue','Potential Profit'], rows, 'inventory-valuation');
                break;
            }
            case 'sales': {
                const rows = (this.lastData.sales?.data ?? []).map(s => [
                    s.invoice_number,
                    s.customer ? ((s.customer.first_name ?? '') + ' ' + (s.customer.last_name ?? '')).trim() : '',
                    s.sale_date, s.total_amount, s.status,
                ]);
                exportToCSV(['Invoice #','Customer','Date','Amount','Status'], rows, 'sales-report');
                break;
            }
            case 'profit-loss': {
                const rows = (this.lastData.breakdown ?? []).map(r => [
                    r.date, r.revenue, r.expenses, r.profit, r.transactions,
                ]);
                exportToCSV(['Date','Revenue','Expenses','Profit','Transactions'], rows, 'profit-loss');
                break;
            }
            case 'stock-movement': {
                const rows = (this.lastData.movements?.data ?? []).map(m => [
                    m.product?.name ?? '', m.product?.sku ?? '',
                    m.quantity_change, m.transaction_type,
                    m.created_at ? new Date(m.created_at).toLocaleDateString() : '',
                ]);
                exportToCSV(['Product','SKU','Change','Type','Date'], rows, 'stock-movement');
                break;
            }
            case 'purchases': {
                const rows = (this.lastData.purchases?.data ?? this.lastData.data ?? []).map(p => [
                    p.purchase_number, p.supplier?.name ?? '', p.purchase_date,
                    p.total_amount, p.status,
                ]);
                exportToCSV(['PO #','Supplier','Date','Amount','Status'], rows, 'purchases-report');
                break;
            }
            case 'stock-levels': {
                const rows = (this.lastData.products ?? this.lastData.data ?? []).map(p => [
                    p.name, p.sku ?? '', p.category?.name ?? '',
                    p.current_stock, p.reorder_level ?? '',
                    p.current_stock === 0 ? 'Out of Stock' : p.current_stock <= p.reorder_level ? 'Low Stock' : 'OK',
                ]);
                exportToCSV(['Product','SKU','Category','Stock','Reorder Level','Status'], rows, 'stock-levels');
                break;
            }
        }
    }

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}
