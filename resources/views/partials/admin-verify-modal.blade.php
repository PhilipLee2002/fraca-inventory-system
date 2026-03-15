<div class="modal fade" id="adminVerifyModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="adminVerifyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminVerifyModalLabel">Admin Verification Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    This action requires administrator credentials. 
                    Please enter an admin email and password to continue.
                </p>
                <form id="adminVerifyForm">
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Admin Email <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control" 
                               id="adminEmail" 
                               name="admin_email" 
                               required 
                               autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Admin Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="adminPassword" 
                               name="admin_password" 
                               required 
                               autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="verifyAdminBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Verify and Continue
                </button>
            </div>
        </div>
    </div>
</div>
