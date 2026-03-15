@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container" style="max-width:760px;">

    <h2 class="h4 mb-4">Profile Settings</h2>

    {{-- Update Profile Information --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h6>
        </div>
        <div class="card-body">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- Update Password --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Update Password</h6>
        </div>
        <div class="card-body">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    {{-- Delete Account --}}
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0"><i class="fas fa-trash me-2"></i>Delete Account</h6>
        </div>
        <div class="card-body">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>
@endsection
