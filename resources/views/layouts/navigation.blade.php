<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <x-application-logo class="me-2" />
            <span class="fw-semibold">{{ config('app.name', 'Inventory System') }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('dashboard')) active @endif"
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> {{ __('Dashboard') }}
                    </a>
                </li>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users me-1"></i> User Management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('users.index') }}">
                                    <i class="fas fa-list me-1"></i> All Users
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('users.create') }}">
                                    <i class="fas fa-plus me-1"></i> Add New User
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-boxes me-1"></i> Inventory
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="inventoryDropdown">
                        @can('view-product')
                            <li>
                                <a class="dropdown-item" href="{{ route('products.index') }}">
                                    <i class="fas fa-box me-1"></i> Products
                                </a>
                            </li>
                        @endcan
                        @can('view-category')
                            <li>
                                <a class="dropdown-item" href="{{ route('categories.index') }}">
                                    <i class="fas fa-tags me-1"></i> Categories
                                </a>
                            </li>
                        @endcan
                        @can('view-sale')
                            <li>
                                <a class="dropdown-item" href="{{ route('sales.index') }}">
                                    <i class="fas fa-shopping-cart me-1"></i> Sales
                                </a>
                            </li>
                        @endcan
                        @can('view-purchase')
                            <li>
                                <a class="dropdown-item" href="{{ route('purchases.index') }}">
                                    <i class="fas fa-shopping-bag me-1"></i> Purchases
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            </ul>

            @auth
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-2"></i>
                            <div class="text-start">
                                <div class="small">{{ Auth::user()->name }}</div>
                                <div class="small text-muted">{{ Auth::user()->role->name ?? 'User' }}</div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-1"></i> {{ __('Profile') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-1"></i> {{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            @endauth
        </div>
    </div>
</nav>