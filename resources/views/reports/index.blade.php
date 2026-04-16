@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Reports</h2>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-inventory" data-bs-toggle="tab" data-bs-target="#pane-inventory"
                    type="button" role="tab" data-report="inventory-valuation">
                <i class="fas fa-warehouse me-1"></i> Inventory Valuation
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sales" data-bs-toggle="tab" data-bs-target="#pane-sales"
                    type="button" role="tab" data-report="sales">
                <i class="fas fa-chart-line me-1"></i> Sales by Period
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pl" data-bs-toggle="tab" data-bs-target="#pane-pl"
                    type="button" role="tab" data-report="profit-loss">
                <i class="fas fa-dollar-sign me-1"></i> Profit &amp; Loss
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-movement" data-bs-toggle="tab" data-bs-target="#pane-movement"
                    type="button" role="tab" data-report="stock-movement">
                <i class="fas fa-exchange-alt me-1"></i> Stock Movement
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-purchases" data-bs-toggle="tab" data-bs-target="#pane-purchases"
                    type="button" role="tab" data-report="purchases">
                <i class="fas fa-shopping-cart me-1"></i> Purchases
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-stock-levels" data-bs-toggle="tab" data-bs-target="#pane-stock-levels"
                    type="button" role="tab" data-report="stock-levels">
                <i class="fas fa-layer-group me-1"></i> Stock Levels
            </button>
        </li>
    </ul>

    {{-- Filters --}}
    <div class="card mb-3" id="report-filters">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 d-none date-filter">
                    <label class="form-label small">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="filter-start-date">
                </div>
                <div class="col-md-3 d-none date-filter">
                    <label class="form-label small">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="filter-end-date">
                </div>
                <div class="col-md-3 d-none category-filter">
                    <label class="form-label small">Category</label>
                    <select class="form-select form-select-sm" id="filter-report-category">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-3 d-none supplier-filter">
                    <label class="form-label small">Supplier</label>
                    <select class="form-select form-select-sm" id="filter-report-supplier">
                        <option value="">All Suppliers</option>
                    </select>
                </div>
                <div class="col-md-3 d-none customer-filter">
                    <label class="form-label small">Customer</label>
                    <select class="form-select form-select-sm" id="filter-report-customer">
                        <option value="">All Customers</option>
                    </select>
                </div>
                <div class="col-md-3 d-none product-filter">
                    <label class="form-label small">Product</label>
                    <select class="form-select form-select-sm" id="filter-report-product">
                        <option value="">All Products</option>
                    </select>
                </div>
                <div class="col-md-3 d-none groupby-filter">
                    <label class="form-label small">Group By</label>
                    <select class="form-select form-select-sm" id="filter-group-by">
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month" selected>Month</option>
                    </select>
                </div>
                <div class="col-md-3 d-none status-filter">
                    <label class="form-label small">Status</label>
                    <select class="form-select form-select-sm" id="filter-report-status">
                        <option value="completed">Completed / Received</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="all">All Statuses</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-action-primary btn-sm" id="btn-generate-report">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        <i class="fas fa-play me-1"></i> Generate
                    </button>
                </div>
                <div class="col-auto">
                    <button class="btn btn-action-dark btn-sm" id="btn-export-csv" disabled>
                        <i class="fas fa-download me-1"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-3 d-none" id="report-summary"></div>

    {{-- Tab panes --}}
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pane-inventory" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-inventory">
                        <thead class="table-dark">
                            <tr><th>Product</th><th>Category</th><th class="text-center">Stock</th>
                                <th class="text-end">Cost Value</th><th class="text-end">Potential Revenue</th>
                                <th class="text-end">Potential Profit</th></tr>
                        </thead>
                        <tbody id="tbody-inventory">
                            <tr><td colspan="6" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="pane-sales" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-sales">
                        <thead class="table-dark">
                            <tr><th>Invoice #</th><th>Customer</th><th>Date</th>
                                <th class="text-end">Amount</th><th class="text-center">Status</th></tr>
                        </thead>
                        <tbody id="tbody-sales">
                            <tr><td colspan="5" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="pane-pl" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-pl">
                        <thead class="table-dark">
                            <tr><th>Date</th><th class="text-end">Revenue</th>
                                <th class="text-end">Expenses</th><th class="text-end">Profit</th>
                                <th class="text-center">Transactions</th></tr>
                        </thead>
                        <tbody id="tbody-pl">
                            <tr><td colspan="5" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="pane-movement" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-movement">
                        <thead class="table-dark">
                            <tr><th>Product</th><th>SKU</th><th class="text-center">Change</th>
                                <th>Type</th><th>Date</th></tr>
                        </thead>
                        <tbody id="tbody-movement">
                            <tr><td colspan="5" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="pane-purchases" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-purchases">
                        <thead class="table-dark">
                            <tr><th>PO #</th><th>Supplier</th><th>Date</th>
                                <th class="text-end">Amount</th><th class="text-center">Status</th></tr>
                        </thead>
                        <tbody id="tbody-purchases">
                            <tr><td colspan="5" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="pane-stock-levels" role="tabpanel">
            <div class="card"><div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-stock-levels">
                        <thead class="table-dark">
                            <tr><th>Product</th><th>SKU</th><th>Category</th>
                                <th class="text-center">Stock</th><th class="text-center">Reorder Level</th>
                                <th class="text-center">Status</th></tr>
                        </thead>
                        <tbody id="tbody-stock-levels">
                            <tr><td colspan="6" class="text-center text-muted py-4">Click Generate to load report.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
    </div>
</div>
@endsection
