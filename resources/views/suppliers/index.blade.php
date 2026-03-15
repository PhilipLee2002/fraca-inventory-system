@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Suppliers</h2>
        @can('create-supplier')
        <button class="btn btn-action-primary" id="btn-add-supplier">
            <i class="fas fa-plus me-1"></i> Add Supplier
        </button>
        @endcan
    </div>

    {{-- Search --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="supplier-search" placeholder="Name, email or contact...">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" id="btn-clear-supplier-search">
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
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="suppliers-tbody">
                        <tr><td colspan="5" class="text-center py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="suppliers-pagination">
                <small class="text-muted" id="suppliers-pagination-info"></small>
                <div id="suppliers-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="supplierModalLabel">Add Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="supplierForm" novalidate>
                    <input type="hidden" id="supplier-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier-name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="supplier-contact" name="contact_person">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="supplier-email" name="email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="supplier-phone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="supplier-address" name="address" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-action-primary" id="btn-save-supplier">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
