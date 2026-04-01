@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Purchases</h2>
        @can('create-purchase')
        <button class="btn btn-action-primary" id="btn-new-purchase">
            <i class="fas fa-plus me-1"></i> New Purchase
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
                        <input type="text" class="form-control" id="purchase-search" placeholder="PO number or supplier...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filter-purchase-status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="filter-purchase-from">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="filter-purchase-to">
                </div>
                <div class="col-6 col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btn-clear-purchase-filters">
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
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="purchases-tbody">
                        <tr><td colspan="6" class="text-center py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="purchases-pagination">
                <small class="text-muted" id="purchases-pagination-info"></small>
                <div id="purchases-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

{{-- New / View Purchase Modal --}}
<div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="purchaseModalLabel">New Purchase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="purchaseForm" novalidate>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" name="supplier_id" id="purchase-supplier" required>
                                <option value="">Select supplier...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="purchase_date" id="purchase-date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="purchase-payment-method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Mpesa</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="purchase-status">
                                <option value="pending">Pending</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control form-control-sm" name="notes" id="purchase-notes" rows="1" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="purchase-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th style="width:110px">Qty</th>
                                    <th style="width:130px">Unit Price</th>
                                    <th style="width:120px" class="text-end">Subtotal</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="purchase-items-body"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Total:</td>
                                    <td class="text-end fw-bold" id="purchase-total">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-add-purchase-row">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </button>
                </form>

                <div id="purchase-view-body" class="d-none"></div>
            </div>
            <div class="modal-footer" id="purchase-modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-action-primary" id="btn-save-purchase">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    Save Purchase
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
