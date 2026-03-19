@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Products</h2>
        @can('create-product')
        <button class="btn btn-action-primary" id="btn-add-product">
            <i class="fas fa-plus me-1"></i> Add Product
        </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="product-search"
                               placeholder="Name or SKU...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Category</label>
                    <select class="form-select form-select-sm" id="filter-category">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Supplier</label>
                    <select class="form-select form-select-sm" id="filter-supplier">
                        <option value="">All Suppliers</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btn-clear-filters">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="products-table">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Loading products...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="products-pagination" style="display:none!important">
                <small class="text-muted" id="products-pagination-info"></small>
                <div id="products-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Product Modal --}}
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm" novalidate>
                    <input type="hidden" id="product-id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="product-name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sku" id="product-sku" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" name="barcode" id="product-barcode" placeholder="Optional">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="product-description" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="product-category" required>
                                <option value="">Select category...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select class="form-select" name="supplier_id" id="product-supplier">
                                <option value="">Select supplier...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cost Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control" name="cost_price" id="product-cost-price"
                                       min="0" step="0.01" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control" name="selling_price" id="product-selling-price"
                                       min="0" step="0.01" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="current_stock" id="product-quantity"
                                   min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="reorder_level" id="product-reorder-level"
                                   min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-action-primary" id="btn-save-product">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    Save Product
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
