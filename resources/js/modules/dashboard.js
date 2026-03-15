import apiClient from '../api/client.js';

export class DashboardModule {
    constructor() {
        this.refreshInterval = null;
        this.isRefreshing = false;
    }

    init() {
        this.fetchStats();
        this.fetchWidgets();
        this.startAutoRefresh();

        // Clear interval on page unload
        window.addEventListener('beforeunload', () => this.destroy());
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.fetchStats(true);
        }, 30000);
    }

    showSkeletons() {
        document.querySelectorAll('[data-stat]').forEach(el => {
            el.innerHTML = '<span class="placeholder col-6 rounded"></span>';
            el.classList.add('placeholder-glow');
        });
    }

    hideSkeletons() {
        document.querySelectorAll('[data-stat]').forEach(el => {
            el.classList.remove('placeholder-glow');
        });
    }

    setStat(key, value) {
        const el = document.querySelector(`[data-stat="${key}"]`);
        if (el) {
            el.classList.remove('placeholder-glow');
            el.innerHTML = value;
        }
    }

    setRefreshIndicator(visible) {
        const indicator = document.getElementById('refresh-indicator');
        if (indicator) {
            indicator.classList.toggle('d-none', !visible);
        }
    }

    async fetchStats(isRefresh = false) {
        if (this.isRefreshing) return;
        this.isRefreshing = true;

        if (isRefresh) {
            this.setRefreshIndicator(true);
        } else {
            this.showSkeletons();
        }

        try {
            const response = await apiClient.get('/reports/dashboard');
            const data = response.data?.data ?? {};
            this.populateStats(data);
            this.populateWidgetsFromStats(data);
        } catch (error) {
            if (error.response?.status !== 401) {
                window.utils?.showToast('Failed to load dashboard statistics.', 'error');
            }
        } finally {
            this.isRefreshing = false;
            this.hideSkeletons();
            this.setRefreshIndicator(false);
        }
    }

    populateStats(data) {
        // The API returns { stats: {...}, recent_alerts: [...] }
        const stats = data.stats ?? data;

        // Basic stats
        this.setStat('total-products', stats.total_products ?? 0);
        this.setStat('todays-sales', this.formatCurrency(stats.today_sales ?? 0));
        this.setStat('low-stock', stats.low_stock_items ?? 0);
        this.setStat('total-users', stats.total_users ?? 0);

        // Extended metrics (Manager/Admin)
        this.setStat('monthly-sales', this.formatCurrency(stats.monthly_sales ?? 0));
        this.setStat('monthly-purchases', this.formatCurrency(stats.monthly_purchases ?? 0));
        this.setStat('profit-margin', (stats.profit_margin ?? 0) + '%');
        this.setStat('sales-vs-target', (stats.sales_vs_target ?? 0) + '%');

        this.setStat('out-of-stock', stats.out_of_stock_items ?? 0);
        this.setStat('overstock', stats.overstock ?? 0);
        this.setStat('stock-turnover', stats.stock_turnover ?? 0);
        this.setStat('inventory-valuation', this.formatCurrency(stats.inventory_valuation ?? 0));

        this.setStat('pending-sales', stats.pending_sales ?? 0);
        this.setStat('pending-purchases', stats.pending_purchases ?? 0);
        this.setStat('active-alerts', stats.active_alerts ?? 0);
        this.setStat('recent-adjustments', stats.recent_adjustments ?? 0);
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
    }

    // ── Widget loaders ────────────────────────────────────────────────────────

    fetchWidgets() {
        this.fetchActivityTimeline();
        this.fetchAlertsWidget();
    }

    // Populate top performers and financial summary from already-fetched stats
    populateWidgetsFromStats(data) {
        const stats = data.stats ?? data;
        this.renderTopPerformers(data.top_performers ?? []);
        this.renderFinancialSummary(stats);
        this.renderPendingActions(stats);
    }

    // ── 3.1 Activity Timeline ─────────────────────────────────────────────────

    async fetchActivityTimeline() {
        try {
            const response = await apiClient.get('/recent-activity');
            const items = response.data?.data ?? [];
            this.renderActivityTimeline(items);
        } catch {
            this.renderActivityTimeline([]);
        }
    }

    renderActivityTimeline(items) {
        const list = document.getElementById('activity-timeline');
        const badge = document.getElementById('activity-count');
        if (!list) return;

        if (badge) badge.textContent = items.length;

        if (!items.length) {
            list.innerHTML = '<li class="list-group-item text-center text-muted py-4">No recent activity</li>';
            return;
        }

        list.innerHTML = items.map(item => {
            const time = new Date(item.timestamp).toLocaleString();
            const amountHtml = item.amount != null
                ? `<span class="ms-1 text-muted small">${this.formatCurrency(item.amount)}</span>`
                : '';
            return `
                <li class="list-group-item list-group-item-action px-3 py-2">
                    <a href="${item.url}" class="text-decoration-none text-reset d-flex align-items-start gap-2">
                        <span class="badge bg-${item.color} mt-1" style="min-width:28px;">
                            <i class="fas ${item.icon}"></i>
                        </span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between">
                                <span class="fw-medium small text-truncate">${item.action}</span>
                                ${amountHtml}
                            </div>
                            <div class="small text-muted text-truncate">${item.entity} &mdash; ${item.detail}</div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="small text-muted"><i class="fas fa-user me-1"></i>${item.user}</span>
                                <span class="small text-muted">${time}</span>
                            </div>
                        </div>
                    </a>
                </li>`;
        }).join('');
    }

    // ── 3.2 Top Performers ────────────────────────────────────────────────────

    renderTopPerformers(performers) {
        const tbody = document.getElementById('top-performers-body');
        if (!tbody) return;

        if (!performers.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No data available</td></tr>';
            return;
        }

        const medals = ['🥇', '🥈', '🥉'];
        tbody.innerHTML = performers.map((p, i) => `
            <tr>
                <td>${medals[i] ?? i + 1}</td>
                <td>${p.name}</td>
                <td class="text-center"><span class="badge bg-primary">${p.sales_count}</span></td>
                <td class="text-end fw-semibold">${this.formatCurrency(p.total_amount)}</td>
            </tr>`).join('');
    }

    // ── 3.3 Financial Summary ─────────────────────────────────────────────────

    renderFinancialSummary(stats) {
        const container = document.getElementById('financial-summary');
        if (!container) return;

        const revenue  = stats.monthly_revenue  ?? stats.monthly_sales ?? 0;
        const expenses = stats.monthly_expenses ?? stats.monthly_purchases ?? 0;
        const profit   = stats.monthly_profit   ?? (revenue - expenses);
        const isProfit = profit >= 0;

        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <span class="text-muted">Revenue</span>
                <span class="fw-semibold text-success">${this.formatCurrency(revenue)}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <span class="text-muted">Expenses</span>
                <span class="fw-semibold text-danger">${this.formatCurrency(expenses)}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-1">
                <span class="fw-semibold">Net Profit</span>
                <span class="fw-bold fs-5 ${isProfit ? 'text-success' : 'text-danger'}">
                    <i class="fas fa-${isProfit ? 'arrow-up' : 'arrow-down'} me-1"></i>
                    ${this.formatCurrency(Math.abs(profit))}
                </span>
            </div>`;
    }

    // ── 3.4 Pending Actions ───────────────────────────────────────────────────

    renderPendingActions(stats) {
        const list = document.getElementById('pending-actions-list');
        if (!list) return;

        const items = [
            {
                label: 'Low Stock Items',
                count: stats.low_stock_items ?? 0,
                url: '/products',
                color: 'warning',
                icon: 'fa-exclamation-triangle',
            },
            {
                label: 'Out of Stock',
                count: stats.out_of_stock_items ?? 0,
                url: '/products',
                color: 'danger',
                icon: 'fa-times-circle',
            },
            {
                label: 'Pending Sales',
                count: stats.pending_sales ?? 0,
                url: '/sales',
                color: 'primary',
                icon: 'fa-clock',
            },
            {
                label: 'Pending Purchases',
                count: stats.pending_purchases ?? 0,
                url: '/purchases',
                color: 'warning',
                icon: 'fa-hourglass-half',
            },
            {
                label: 'Active Alerts',
                count: stats.active_alerts ?? 0,
                url: '#alerts-container',
                color: 'danger',
                icon: 'fa-bell',
            },
        ];

        const hasItems = items.some(i => i.count > 0);

        if (!hasItems) {
            list.innerHTML = '<li class="list-group-item text-center text-success py-4"><i class="fas fa-check-circle me-2"></i>All clear — no pending actions</li>';
            return;
        }

        list.innerHTML = items.map(item => `
            <a href="${item.url}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-2">
                <span><i class="fas ${item.icon} text-${item.color} me-2"></i>${item.label}</span>
                <span class="badge bg-${item.color} rounded-pill">${item.count}</span>
            </a>`).join('');
    }

    // ── 3.5 Alerts & Notifications ────────────────────────────────────────────

    async fetchAlertsWidget() {
        const container = document.getElementById('alerts-container');
        const badge = document.getElementById('alerts-badge');
        if (!container) return;

        try {
            const response = await apiClient.get('/alerts');
            const alerts = response.data?.data ?? [];
            this.renderAlertsWidget(alerts, container, badge);
        } catch {
            container.innerHTML = '<p class="text-muted text-center py-3 mb-0">Unable to load alerts.</p>';
        }
    }

    renderAlertsWidget(alerts, container, badge) {
        if (badge) {
            badge.textContent = alerts.length;
            badge.style.display = alerts.length ? '' : 'none';
        }

        if (!alerts.length) {
            container.innerHTML = '<p class="text-success text-center py-3 mb-0"><i class="fas fa-check-circle me-2"></i>No active alerts</p>';
            return;
        }

        const colorMap = { low_stock: 'warning', out_of_stock: 'danger', system: 'info' };

        container.innerHTML = alerts.map(alert => {
            const color = colorMap[alert.type] ?? 'warning';
            const stockInfo = alert.current_stock != null
                ? `<small class="d-block mt-1">Stock: <strong>${alert.current_stock}</strong> / Reorder: ${alert.reorder_level}</small>`
                : '';
            return `
                <div class="alert alert-${color} alert-sm py-2 px-3 mb-2 d-flex justify-content-between align-items-start" role="alert">
                    <div>
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <strong>${alert.message}</strong>
                        ${stockInfo}
                        <small class="d-block text-muted mt-1">${new Date(alert.created_at).toLocaleDateString()}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary ms-2 flex-shrink-0" data-dismiss-alert="${alert.id}" title="Dismiss">
                        <i class="fas fa-check"></i>
                    </button>
                </div>`;
        }).join('');

        // Bind dismiss buttons
        container.querySelectorAll('[data-dismiss-alert]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const alertId = btn.dataset.dismissAlert;
                try {
                    await apiClient.patch('/alerts/' + alertId + '/read');
                    this.fetchAlertsWidget();
                } catch {
                    window.utils?.showToast('Failed to dismiss alert.', 'error');
                }
            });
        });
    }
}
