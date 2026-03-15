<p class="text-muted mb-3">
    Once your account is deleted, all of its resources and data will be permanently deleted.
    Before deleting your account, please download any data or information that you wish to retain.
</p>

<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    <i class="fas fa-trash me-1"></i> Delete Account
</button>

{{-- Delete Account Confirmation Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Confirm your password</label>
                        <input type="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               id="delete_password" name="password" placeholder="Enter your password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
