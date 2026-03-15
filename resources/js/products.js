import axios from 'axios';
import { showToast } from './utils';

document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('#products-table');
    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const loadingRowHtml = `
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading products...
            </td>
        </tr>
    `;

    const renderProducts = (items) => {
        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No products found.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = items
            .map((product, index) => {
                const categoryName = product.category?.name ?? '-';
                return `
                    <tr data-id="${product.id}">
                        <td>${index + 1}</td>
                        <td>${product.name}</td>
                        <td>${product.sku ?? '-'}</td>
                        <td>${categoryName}</td>
                        <td>${product.current_stock ?? 0}</td>
                        <td>${product.reorder_level ?? '-'}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" disabled>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            })
            .join('');
    };

    const loadProducts = async () => {
        tbody.innerHTML = loadingRowHtml;

        try {
            const response = await axios.get('/api/products');
            // Expecting either paginated { data: [...], pagination: {...} } or plain array
            let items = [];
            if (Array.isArray(response.data.data)) {
                items = response.data.data;
            } else if (response.data.data && Array.isArray(response.data.data.data)) {
                // If wrapped in paginated helper from BaseController
                items = response.data.data.data;
            }

            renderProducts(items);
        } catch (error) {
            console.error(error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        Failed to load products. Please try again.
                    </td>
                </tr>
            `;
            showToast('Failed to load products.', 'error');
        }
    };

    loadProducts();
});

