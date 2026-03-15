@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="h4 mb-4">Dashboard</h2>
            <!-- Welcome Message -->
            <div class="card mb-4">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="h5 mb-1">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="mb-0 text-muted">
                            You are logged in as
                            <span class="fw-semibold text-primary">{{ Auth::user()->role->name ?? 'User' }}</span>.
                            Last update: {{ Auth::user()->updated_at->format('F d, Y h:i A') }}
                        </p>
                    </div>
                    <span class="badge rounded-pill bg-success">
                        {{ Auth::user()->role->name ?? 'User' }}
                    </span>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row g-3 mb-4">
                <!-- Total Products -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Products</div>
                                <div class="fs-4 fw-semibold">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Sales -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Today's Sales</div>
                                <div class="fs-4 fw-semibold">$0.00</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Low Stock Items</div>
                                <div class="fs-4 fw-semibold">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-purple-subtle text-purple rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Users</div>
                                <div class="fs-4 fw-semibold">{{ \App\Models\User::count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (Only for Admin/Manager) -->
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Quick Actions</h3>
                    <div class="row g-3">
                        @can('create-product')
                        <div class="col-6 col-md-3">
                            <a href="#" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-plus fa-lg mb-2"></i>
                                <span class="fw-medium small">Add Product</span>
                            </a>
                        </div>
                        @endcan

                        @can('create-sale')
                        <div class="col-6 col-md-3">
                            <a href="#" class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-cart-plus fa-lg mb-2"></i>
                                <span class="fw-medium small">New Sale</span>
                            </a>
                        </div>
                        @endcan

                        @can('create-purchase')
                        <div class="col-6 col-md-3">
                            <a href="#" class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-shopping-bag fa-lg mb-2"></i>
                                <span class="fw-medium small">New Purchase</span>
                            </a>
                        </div>
                        @endcan

                        @can('create-user')
                        <div class="col-6 col-md-3">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-user-plus fa-lg mb-2"></i>
                                <span class="fw-medium small">Add User</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="row g-3">
                <!-- Recent Users (Admin Only) -->
                @if(Auth::user()->isAdmin())
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0">Recent Users</h3>
                                <a href="{{ route('users.index') }}" class="small text-primary text-decoration-none">View All</a>
                            </div>
                            <div class="vstack gap-2">
                                @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                                <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <span class="fw-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-medium small">{{ $user->name }}</div>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge rounded-pill
                                            {{ $user->role->name == 'admin' ? 'bg-purple-600' : 
                                               ($user->role->name == 'manager' ? 'bg-primary' : 'bg-secondary') }}">
                                            {{ $user->role->name }}
                                        </span>
                                        <div class="small text-muted mt-1">{{ $user->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- System Status -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-3">System Status</h3>
                            <div class="vstack gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-database"></i>
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-medium small">Database</div>
                                            <div class="small text-muted">Connected and running</div>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill bg-success">
                                        <i class="fas fa-check me-1"></i> Online
                                    </span>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-medium small">Registered Users</div>
                                        <div class="small text-muted">{{ \App\Models\User::count() }} registered</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-medium small">Inventory</div>
                                        <div class="small text-muted">Products: 0, Categories: 0</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Links -->
                            <div class="mt-4 pt-3 border-top">
                                <h4 class="small fw-semibold mb-2">Quick Links</h4>
                                <div class="row g-2">
                                    @can('view-product')
                                    <div class="col-6">
                                        <a href="{{ route('products.index') }}" class="small text-primary text-decoration-none">
                                            <i class="fas fa-box me-1"></i> Products
                                        </a>
                                    </div>
                                    @endcan
                                    @can('view-sale')
                                    <div class="col-6">
                                        <a href="{{ route('sales.index') }}" class="small text-success text-decoration-none">
                                            <i class="fas fa-shopping-cart me-1"></i> Sales
                                        </a>
                                    </div>
                                    @endcan
                                    @can('view-purchase')
                                    <div class="col-6">
                                        <a href="{{ route('purchases.index') }}" class="small text-warning text-decoration-none">
                                            <i class="fas fa-shopping-bag me-1"></i> Purchases
                                        </a>
                                    </div>
                                    @endcan
                                    @if(Auth::user()->isAdmin())
                                    <div class="col-6">
                                        <a href="{{ route('users.index') }}" class="small text-secondary text-decoration-none">
                                            <i class="fas fa-users me-1"></i> Users
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection