@extends('layouts.app')

@section('title', '403 Forbidden')

@section('content')
<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="display-1 fw-bold text-danger">403</div>
        <h2 class="h4 mb-3">Access Denied</h2>
        <p class="text-muted mb-4">You don't have permission to access this page.</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-action-primary">
            <i class="fas fa-home me-1"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
