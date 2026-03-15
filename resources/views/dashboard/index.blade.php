@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid" data-module="dashboard">

    {{-- Welcome banner --}}
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="h5 mb-1">Welcome back, {{ Auth::user()->name }}!</h3>
                <p class="mb-0 text-muted">
                    Logged in as
                    <span class="fw-semibold text-primary">{{ Auth::user()->role->name ?? 'User' }}</span>
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Refresh indicator --}}
                <span id="refresh-indicator" class="d-none text-muted small">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Refreshing...
                </span>
                <span class="badge rounded-pill bg-success">{{ Auth::user()->role->name ?? 'User' }}</span>
            </div>
        </div>
    </div>

    {{-- Basic Stats (all roles) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary-subtle text-primary rounded-circle me-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Products</div>
                        <div class="fs-4 fw-semibold placeholder-glow" data-stat="total-products">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success-subtle text-success rounded-circle me-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Today's Sales</div>
                        <div class="fs-4 fw-semibold placeholder-glow" data-stat="todays-sales">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Low Stock Items</div>
                        <div class="fs-4 fw-semibold placeholder-glow" data-stat="low-stock">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info-subtle text-info rounded-circle me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="fs-4 fw-semibold placeholder-glow" data-stat="total-users">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Extended Metrics (Manager / Admin only) --}}
    @can('view-report')

    {{-- Monthly Performance --}}
    <h5 class="mb-3 mt-2">Monthly Performance</h5>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success-subtle text-success rounded-circle me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Monthly Sales</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="monthly-sales">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Monthly Purchases</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="monthly-purchases">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary-subtle text-primary rounded-circle me-3">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Profit Margin</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="profit-margin">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle me-3">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Sales vs Target</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="sales-vs-target">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Health --}}
    <h5 class="mb-3">Inventory Health</h5>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle me-3">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Out of Stock</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="out-of-stock">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Overstock</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="overstock">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info-subtle text-info rounded-circle me-3">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Stock Turnover</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="stock-turnover">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success-subtle text-success rounded-circle me-3">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Inventory Valuation</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="inventory-valuation">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Operational Metrics --}}
    <h5 class="mb-3">Operational Metrics</h5>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Sales</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="pending-sales">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle me-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Purchases</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="pending-purchases">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle me-3">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active Alerts</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="active-alerts">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info-subtle text-info rounded-circle me-3">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Recent Adjustments</div>
                        <div class="fs-5 fw-semibold placeholder-glow" data-stat="recent-adjustments">
                            <span class="placeholder col-6 rounded"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endcan

    {{-- Quick Actions (Staff) --}}
    @php $role = Auth::user()->role->name ?? ''; @endphp
    @if($role === 'staff')
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @can('view-product')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.products') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-box fa-lg mb-1 d-block"></i>
                        View Products
                    </a>
                </div>
                @endcan
                @can('create-sale')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.sales') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-cart-plus fa-lg mb-1 d-block"></i>
                        New Sale
                    </a>
                </div>
                @endcan
                @can('create-purchase')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.purchases') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-shopping-bag fa-lg mb-1 d-block"></i>
                        New Purchase
                    </a>
                </div>
                @endcan
                @can('view-sale')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.sales') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-list-alt fa-lg mb-1 d-block"></i>
                        View Sales
                    </a>
                </div>
                @endcan
                @can('view-purchase')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.purchases') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-clipboard-list fa-lg mb-1 d-block"></i>
                        View Purchases
                    </a>
                </div>
                @endcan
                @can('view-report')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.reports') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-chart-bar fa-lg mb-1 d-block"></i>
                        View Reports
                    </a>
                </div>
                @endcan
                @can('view-customer')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.customers') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-users fa-lg mb-1 d-block"></i>
                        View Customers
                    </a>
                </div>
                @endcan
                @can('view-supplier')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.suppliers') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-truck fa-lg mb-1 d-block"></i>
                        View Suppliers
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Actions (Manager / Admin) --}}
    @can('view-report')
    @if($role !== 'staff')
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">

                @can('create-product')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.products') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-plus-circle fa-lg mb-1 d-block"></i>
                        Add Product
                    </a>
                </div>
                @endcan

                @can('manage-stock')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.stock-adjustments') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-sliders-h fa-lg mb-1 d-block"></i>
                        Adjust Stock
                    </a>
                </div>
                @endcan

                @can('create-category')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.categories') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-tags fa-lg mb-1 d-block"></i>
                        Add Category
                    </a>
                </div>
                @endcan

                @can('view-report')
                <div class="col-6 col-md-3">
                    <a href="#alerts-container" class="btn btn-action-dark w-100" onclick="document.getElementById('alerts-container').closest('.card').scrollIntoView({behavior:'smooth'});return false;">
                        <i class="fas fa-bell fa-lg mb-1 d-block"></i>
                        View Alerts
                    </a>
                </div>
                @endcan

                @can('create-sale')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.sales') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-cart-plus fa-lg mb-1 d-block"></i>
                        New Sale
                    </a>
                </div>
                @endcan

                @can('create-purchase')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.purchases') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-shopping-bag fa-lg mb-1 d-block"></i>
                        New Purchase
                    </a>
                </div>
                @endcan

                @can('view-sale')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.sales') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-list-alt fa-lg mb-1 d-block"></i>
                        View Sales
                    </a>
                </div>
                @endcan

                @can('view-purchase')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.purchases') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-clipboard-list fa-lg mb-1 d-block"></i>
                        View Purchases
                    </a>
                </div>
                @endcan

                @can('create-customer')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.customers') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-user-plus fa-lg mb-1 d-block"></i>
                        Add Customer
                    </a>
                </div>
                @endcan

                @can('create-supplier')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.suppliers') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-truck fa-lg mb-1 d-block"></i>
                        Add Supplier
                    </a>
                </div>
                @endcan

                @can('create-user')
                <div class="col-6 col-md-3">
                    <a href="{{ route('users.index') }}" class="btn btn-action-primary w-100">
                        <i class="fas fa-user-shield fa-lg mb-1 d-block"></i>
                        Add User
                    </a>
                </div>
                @endcan

                @can('view-report')
                <div class="col-6 col-md-3">
                    <a href="{{ route('page.reports') }}" class="btn btn-action-dark w-100">
                        <i class="fas fa-chart-bar fa-lg mb-1 d-block"></i>
                        View Reports
                    </a>
                </div>
                @endcan

            </div>
        </div>
    </div>
    @endif
    @endcan

    {{-- Widgets row (Manager / Admin only) --}}
    @can('view-report')
    <div class="row g-4 mb-4">

        {{-- 3.1 Activity Timeline --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2"></i>Activity Timeline</span>
                    <span class="badge bg-secondary" id="activity-count">â€”</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush overflow-auto" id="activity-timeline" style="max-height:320px;">
                        <li class="list-group-item text-center text-muted py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Loading activity...
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- 3.2 Top Performers --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-trophy me-2"></i>Top Performers (This Month)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th class="text-center">Sales</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="top-performers-body">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3.3 Financial Summary --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-chart-pie me-2"></i>Financial Summary (This Month)
                </div>
                <div class="card-body" id="financial-summary">
                    <div class="text-center text-muted py-3">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Loading...
                    </div>
                </div>
            </div>
        </div>

        {{-- 3.4 Pending Actions --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-tasks me-2"></i>Pending Actions
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="pending-actions-list">
                        <li class="list-group-item text-center text-muted py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Loading...
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- 3.5 Alerts & Notifications (Manager/Admin) --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell me-2"></i>Alerts & Notifications</span>
                    <span class="badge bg-danger" id="alerts-badge" style="display:none!important"></span>
                </div>
                <div class="card-body overflow-auto p-2" id="alerts-container" style="max-height:320px;">
                    <div class="text-center text-muted py-3">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Loading...
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endcan

</div>
@endsection
