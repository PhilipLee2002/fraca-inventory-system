@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Users</h2>
        @can('create-user')
        <button class="btn btn-action-primary" id="btn-add-user">
            <i class="fas fa-user-plus me-1"></i> Add User
        </button>
        @endcan
    </div>

    {{-- Search --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="user-search" placeholder="Name or email...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Role</label>
                    <select class="form-select form-select-sm" id="filter-user-role">
                        <option value="">All Roles</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btn-clear-user-filters">
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
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr><td colspan="5" class="text-center py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="users-pagination">
                <small class="text-muted" id="users-pagination-info"></small>
                <div id="users-pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" novalidate>
                    <input type="hidden" id="user-id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="user-email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" id="password-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="user-password" name="password"
                                   autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted d-none" id="password-hint">Leave blank to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="user-role" name="role_id" required>
                                <option value="">Select role...</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="user-status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-action-primary" id="btn-save-user">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
