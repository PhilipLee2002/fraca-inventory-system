import apiClient from '../api/client.js';

export class ProductsModule {
    constructor() {
        this.currentPage = 1;
        this.searchTimer = null;
        this.editingId = null;
        this.filtersReady = false;
        this.filtersPromise = null;
    }

    init() {
        this.bindEvents();
        this.filtersPromise = this.loadFilters();
        this.loadProducts();
    }

    // ── Events ────────────────────────────────────────────────────────────────

    bindEvents() {
        // Search — debounced
        document.getElementById('product-search')?.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.currentPage = 1;
                this.loadProducts();
            }, 300);
        });

        // Filter dropdowns
        document.getElementById('filter-category')?.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadProducts();
        });
        document.getElementById('filter-supplier')?.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadProducts();
        });

        // Clear filters
        document.getElementById('btn-clear-filters')?.addEventListener('click', () => {
            document.getElementById('product-search').value = '';
            document.getElementById('filter-category').value = '';
            document.getElementById('filter-supplier').value = '';
            this.currentPage = 1;
            this.loadProducts();
        });

        // Add product button
        document.getElementById('btn-add-product')?.addEventListener('click', () => {
            this.showProductModal();
        });

        // Save product
        document.getElementById('btn-save-product')?.addEventListener('click', () => {
            this.saveProduct();
        });

        // Auto-generate SKU from product name (new products only)
        document.getElementById('product-name')?.addEventListener('input', () => {
            if (this.editingId) return; // don't overwrite SKU when editing
            const skuField = document.getElementById('product-sku');
            if (skuField?.dataset.manuallyEdited === 'true') return;
            const name = document.getElementById('product-name').value.trim();
            if (!name) { skuField.value = ''; return; }
            const prefix = this.generateSkuPrefix(name);
            skuField.value = prefix + '-???';
            clearTimeout(this.skuTimer);
            this.skuTimer = setTimeout(() => this.resolveSkuSequence(prefix), 400);
        });

        // If user manually edits the SKU field, stop auto-generating
        document.getElementById('product-sku')?.addEventListener('input', () => {
            if (this.editingId) return;
            document.getElementById('product-sku').dataset.manuallyEdited = 'true';
        });

        // Table row actions (delegated)
        document.getElementById('products-tbody')?.addEventListener('click', (e) => {
            const editBtn = e.target.closest('[data-action="edit"]');
            const deleteBtn = e.target.closest('[data-action="delete"]');
            if (editBtn) this.editProduct(editBtn.dataset.id);
            if (deleteBtn) this.deleteProduct(deleteBtn.dataset.id, deleteBtn.dataset.name);
        });
    }

    // ── Filters ───────────────────────────────────────────────────────────────

    async loadFilters() {
        const extract = (res) => {
            const d = res.data?.data;
            // paginated: { data: [...], pagination: {...} }
            if (d && Array.isArray(d.data)) return d.data;
            // flat array
            if (Array.isArray(d)) return d;
            return [];
        };

        const [catRes, supRes] = await Promise.allSettled([
            apiClient.get('/categories'),
            apiClient.get('/suppliers', { params: { per_page: 200 } }),
        ]);

        const categories = catRes.status === 'fulfilled' ? extract(catRes.value) : [];
        const suppliers  = supRes.status === 'fulfilled' ? extract(supRes.value) : [];

        this.populateSelect('filter-category', categories, 'All Categories');
        this.populateSelect('filter-supplier', suppliers, 'All Suppliers');
        this.populateSelect('product-category', categories, 'Select category...');
        this.populateSelect('product-supplier', suppliers, 'Select supplier...');
    }

    populateSelect(id, items, placeholder) {
        const el = document.getElementById(id);
        if (!el) return;
        const current = el.value;
        el.innerHTML = `<option value="">${placeholder}</option>` +
            items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
        if (current) el.value = current;
    }

    // ── List ──────────────────────────────────────────────────────────────────

    async loadProducts() {
        const tbody = document.getElementById('products-tbody');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...</td></tr>`;

        const params = {
            page:        this.currentPage,
            per_page:    20,
            search:      document.getElementById('product-search')?.value || undefined,
            category_id: document.getElementById('filter-category')?.value || undefined,
            supplier_id: document.getElementById('filter-supplier')?.value || undefined,
        };
        // Remove undefined keys
        Object.keys(params).forEach(k => params[k] === undefined && delete params[k]);

        try {
            const res = await apiClient.get('/products', { params });
            const { data: products, pagination } = res.data?.data ?? { data: [], pagination: null };
            this.renderTable(products);
            this.renderPagination(pagination);
        } catch {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">
                Failed to load products.</td></tr>`;
        }
    }

    renderTable(products) {
        const tbody = document.getElementById('products-tbody');
        const canEdit   = window.utils?.hasAnyPermission(['edit-product', 'create-product']);
        const canDelete = window.utils?.hasAnyPermission(['delete-product', 'create-product']);

        if (!products.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">
                <i class="fas fa-box-open me-2"></i>No products found.</td></tr>`;
            return;
        }

        tbody.innerHTML = products.map(p => {
            const isLow = p.current_stock <= p.reorder_level;
            const qtyClass = isLow ? 'text-danger fw-semibold' : '';
            const qtyBadge = isLow
                ? `<i class="fas fa-exclamation-triangle ms-1 text-warning" title="Low stock"></i>`
                : '';

            const editBtn = canEdit
                ? `<button class="btn btn-sm btn-outline-secondary me-1" data-action="edit" data-id="${p.id}"
                       title="Edit"><i class="fas fa-pencil-alt"></i></button>`
                : '';
            const deleteBtn = canDelete
                ? `<button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${p.id}"
                       data-name="${this.esc(p.name)}" title="Delete"><i class="fas fa-trash"></i></button>`
                : '';

            return `<tr>
                <td><code class="small">${this.esc(p.sku)}</code></td>
                <td>${this.esc(p.name)}</td>
                <td>${this.esc(p.category?.name ?? '—')}</td>
                <td class="text-center ${qtyClass}">${p.current_stock}${qtyBadge}</td>
                <td class="text-end">${window.formatKES(p.selling_price ?? p.unit_price ?? 0)}</td>
                <td class="text-end">${editBtn}${deleteBtn}</td>
            </tr>`;
        }).join('');
    }

    renderPagination(pagination) {
        const wrap = document.getElementById('products-pagination');
        const info = document.getElementById('products-pagination-info');
        const controls = document.getElementById('products-pagination-controls');
        if (!wrap || !pagination) return;

        wrap.style.display = '';
        info.textContent = `Showing ${pagination.from ?? 0}–${pagination.to ?? 0} of ${pagination.total}`;

        if (pagination.last_page <= 1) { controls.innerHTML = ''; return; }

        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <button class="page-link" data-page="${i}">${i}</button></li>`;
        }
        html += '</ul></nav>';
        controls.innerHTML = html;

        controls.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.currentPage = parseInt(btn.dataset.page);
                this.loadProducts();
            });
        });
    }

    // ── Add / Edit Modal ──────────────────────────────────────────────────────

    async showProductModal(product = null) {
        this.editingId = product?.id ?? null;
        const form = document.getElementById('productForm');
        const title = document.getElementById('productModalLabel');

        form.reset();
        window.utils?.clearValidationErrors(form);
        title.textContent = product ? 'Edit Product' : 'Add Product';
        document.getElementById('product-id').value = product?.id ?? '';

        // Show modal immediately
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();

        // Reset SKU manual-edit flag for new products
        if (!product) {
            const skuField = document.getElementById('product-sku');
            if (skuField) { skuField.value = ''; skuField.dataset.manuallyEdited = 'false'; }
        }

        // Wait for filters (categories/suppliers) to be populated before setting values
        if (this.filtersPromise) await this.filtersPromise;

        if (product) {
            document.getElementById('product-name').value         = product.name ?? '';
            document.getElementById('product-sku').value          = product.sku ?? '';
            document.getElementById('product-barcode').value      = product.barcode ?? '';
            document.getElementById('product-description').value  = product.description ?? '';
            document.getElementById('product-category').value     = product.category_id ?? '';
            document.getElementById('product-supplier').value     = product.supplier_id ?? '';
            document.getElementById('product-cost-price').value   = product.cost_price ?? '';
            document.getElementById('product-selling-price').value = product.selling_price ?? '';
            document.getElementById('product-quantity').value     = product.current_stock ?? '';
            document.getElementById('product-reorder-level').value = product.reorder_level ?? '';
        }
    }

    async editProduct(id) {
        try {
            const res = await apiClient.get(`/products/${id}`);
            this.showProductModal(res.data?.data);
        } catch {
            window.utils?.showToast('Failed to load product details.', 'error');
        }
    }

    async saveProduct() {
        const form = document.getElementById('productForm');
        const btn  = document.getElementById('btn-save-product');
        const spinner = btn.querySelector('.spinner-border');

        window.utils?.clearValidationErrors(form);

        const payload = {
            name:          document.getElementById('product-name').value.trim(),
            sku:           document.getElementById('product-sku').value.trim(),
            barcode:       document.getElementById('product-barcode').value.trim() || null,
            description:   document.getElementById('product-description').value.trim(),
            category_id:   document.getElementById('product-category').value || null,
            supplier_id:   document.getElementById('product-supplier').value || null,
            cost_price:    parseFloat(document.getElementById('product-cost-price').value),
            selling_price: parseFloat(document.getElementById('product-selling-price').value),
            current_stock: parseInt(document.getElementById('product-quantity').value),
            reorder_level: parseInt(document.getElementById('product-reorder-level').value),
        };

        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            if (this.editingId) {
                await apiClient.put(`/products/${this.editingId}`, payload);
                window.utils?.showToast('Product updated successfully.', 'success');
            } else {
                await apiClient.post('/products', payload);
                window.utils?.showToast('Product created successfully.', 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('productModal'))?.hide();
            this.loadProducts();
        } catch (err) {
            if (err.response?.status === 422) {
                window.utils?.displayValidationErrors(err.response.data.errors ?? {}, form);
            } else {
                window.utils?.showToast(err.response?.data?.message ?? 'Failed to save product.', 'error');
            }
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    async deleteProduct(id, name) {
        const role = window.utils?.getUserRole();

        if (role === 'manager') {
            // Manager: require admin verification
            try {
                await window.utils?.showAdminVerifyModal(async ({ email, password }) => {
                    const res = await apiClient.post('/verify-admin', { email, password });
                    if (!res.data?.data?.verified) throw new Error('Verification failed');
                });
                await this.doDelete(id);
            } catch {
                // modal handles error toast
            }
        } else {
            // Admin: simple confirmation
            const confirmed = await window.utils?.showConfirmModal(
                'Delete Product',
                `Are you sure you want to delete <strong>${this.esc(name)}</strong>? This cannot be undone.`,
                'Delete',
                'btn-danger'
            );
            if (confirmed) await this.doDelete(id);
        }
    }

    async doDelete(id) {
        try {
            await apiClient.delete(`/products/${id}`);
            window.utils?.showToast('Product deleted successfully.', 'success');
            this.loadProducts();
        } catch (err) {
            if (err.response?.status !== 401 && err.response?.status !== 403) {
                window.utils?.showToast(
                    err.response?.data?.message ?? 'Failed to delete product.',
                    'error'
                );
            }
        }
    }

    // ── SKU helpers ───────────────────────────────────────────────────────────

    generateSkuPrefix(name) {
        // Take first letter of each word, uppercase, max 4 chars
        return name.trim().split(/\s+/)
            .map(w => w[0]?.toUpperCase() ?? '')
            .join('')
            .slice(0, 4);
    }

    async resolveSkuSequence(prefix) {
        const skuField = document.getElementById('product-sku');
        if (!skuField || skuField.dataset.manuallyEdited === 'true') return;
        try {
            // Fetch products whose SKU starts with this prefix
            const res = await apiClient.get('/products', { params: { search: prefix + '-', per_page: 500 } });
            const existing = res.data?.data?.data ?? res.data?.data ?? [];
            // Count how many already use this exact prefix pattern
            const pattern = new RegExp('^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '-\\d+$', 'i');
            const count = existing.filter(p => pattern.test(p.sku)).length;
            const seq = String(count + 1).padStart(3, '0');
            // Only update if user hasn't typed in the field since
            if (skuField.dataset.manuallyEdited !== 'true') {
                skuField.value = prefix + '-' + seq;
            }
        } catch {
            if (skuField.dataset.manuallyEdited !== 'true') {
                skuField.value = prefix + '-001';
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}
