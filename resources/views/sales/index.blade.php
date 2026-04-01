@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Sales</h2>
        @can('create-sale')
        <button class="btn btn-action-primary" id="btn-new-sale">
            <i class="fas fa-plus me-1"></i> New Sale
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
                        <input type="text" class="form-control" id="sale-search" placeholder="Invoice # or customer...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filter-sale-status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="filter-sale-from">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="filter-sale-to">
                </div>
                <div class="col-6 col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btn-clear-sale-filters">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sales-tbody">
                        <tr><td colspan="6" class="text-center py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="sales-pagination">
                <small class="text-muted" id="sales-pagination-info"></small>
                <div id="sales-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

{{-- New / View Sale Modal --}}
<div class="modal fade" id="saleModal" tabindex="-1" aria-labelledby="saleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="saleModalLabel">New Sale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Create form --}}
                <form id="saleForm" novalidate>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="sale-customer">
                                <option value="">Walk-in / No Customer</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="sale_date" id="sale-date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="sale-payment-method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Mpesa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="sale-status">
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control form-control-sm" name="notes" id="sale-notes" rows="1" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="sale-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th style="width:110px">Qty</th>
                                    <th style="width:130px">Unit Price</th>
                                    <th style="width:120px" class="text-end">Subtotal</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="sale-items-body"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Total:</td>
                                    <td class="text-end fw-bold" id="sale-total">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-add-sale-row">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </button>
                </form>

                {{-- View-only section --}}
                <div id="sale-view-body" class="d-none"></div>
            </div>
            <div class="modal-footer" id="sale-modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-action-primary" id="btn-save-sale">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    Save Sale
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
