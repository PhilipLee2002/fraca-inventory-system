import './bootstrap';

// Import Bootstrap JavaScript and expose globally
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import utilities
import { showToast } from './utils/toast.js';
import { hasPermission, hasRole, hasAnyPermission, hasAllPermissions, getUserRole, isAuthenticated } from './utils/permissions.js';
import { displayValidationErrors, clearValidationErrors, isValidEmail, validateRequiredFields } from './utils/validation.js';
import { showAdminVerifyModal, showConfirmModal } from './utils/modal.js';
import apiClient from './api/client.js';

// Currency formatter — Kenyan Shillings
window.formatKES = (value) =>
    'KSh ' + parseFloat(value ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// Make utilities globally available
window.utils = {
    showToast,
    hasPermission,
    hasRole,
    hasAnyPermission,
    hasAllPermissions,
    getUserRole,
    isAuthenticated,
    displayValidationErrors,
    clearValidationErrors,
    isValidEmail,
    validateRequiredFields,
    showAdminVerifyModal,
    showConfirmModal
};

// Expose API client globally
window.apiClient = apiClient;

// Initialize Bootstrap tooltips and popovers, and load page modules
document.addEventListener('DOMContentLoaded', async () => {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Lazy-load page-specific modules based on body data attribute
    const page = document.body.dataset.page;

    if (page === 'dashboard') {
        const { DashboardModule } = await import('./modules/dashboard.js');
        window.dashboardModule = new DashboardModule();
        window.dashboardModule.init();
    }

    if (page === 'products') {
        const { ProductsModule } = await import('./modules/products.js');
        window.productsModule = new ProductsModule();
        window.productsModule.init();
    }

    if (page === 'purchases') {
        const { PurchasesModule } = await import('./modules/purchases.js');
        window.purchasesModule = new PurchasesModule();
        window.purchasesModule.init();
    }

    if (page === 'sales') {
        const { SalesModule } = await import('./modules/sales.js');
        window.salesModule = new SalesModule();
        window.salesModule.init();
    }

    if (page === 'stock-adjustments') {
        const { StockAdjustmentsModule } = await import('./modules/stock-adjustments.js');
        window.stockAdjustmentsModule = new StockAdjustmentsModule();
        window.stockAdjustmentsModule.init();
    }

    if (page === 'reports') {
        const { ReportsModule } = await import('./modules/reports.js');
        window.reportsModule = new ReportsModule();
        window.reportsModule.init();
    }

    if (page === 'users') {
        const { UsersModule } = await import('./modules/users.js');
        window.usersModule = new UsersModule();
        window.usersModule.init();
    }

    if (page === 'categories') {
        const { CategoriesModule } = await import('./modules/categories.js');
        window.categoriesModule = new CategoriesModule();
        window.categoriesModule.init();
    }

    if (page === 'suppliers') {
        const { SuppliersModule } = await import('./modules/suppliers.js');
        window.suppliersModule = new SuppliersModule();
        window.suppliersModule.init();
    }

    if (page === 'customers') {
        const { CustomersModule } = await import('./modules/customers.js');
        window.customersModule = new CustomersModule();
        window.customersModule.init();
    }
});
