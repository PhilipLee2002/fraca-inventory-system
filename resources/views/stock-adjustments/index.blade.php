@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Stock Adjustments</h2>
    </div>

    @can('manage-stock')
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>New Adjustment</h6>
        </div>
        <div class="card-body">
            <form id="adjustmentForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="adj-product" name="product_id" required>
                            <option value="">Select product...</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity Change <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="adj-quantity" name="quantity_change"
                               placeholder="e.g. -5 or +10" required>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Negative = remove, Positive = add</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="adj-reason" name="reason" rows="1"
                                  placeholder="Reason for adjustment..." required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-action-primary w-100" id="btn-submit-adjustment">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="fas fa-check me-1"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Recent Adjustments</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th class="text-center">Change</th>
                            <th>Reason</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="adjustments-tbody">
                        <tr><td colspan="6" class="text-center py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="adj-pagination">
                <small class="text-muted" id="adj-pagination-info"></small>
                <div id="adj-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>
@endsection
