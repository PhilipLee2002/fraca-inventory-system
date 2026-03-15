<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">FRACA SERVCOM</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                @can('view-product')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('products*') ? 'active' : '' }}"
                       href="{{ route('page.products') }}">Products</a>
                </li>
                @endcan
                @can('view-sale')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('sales*') ? 'active' : '' }}"
                       href="{{ route('page.sales') }}">Sales</a>
                </li>
                @endcan
                @can('view-purchase')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}"
                       href="{{ route('page.purchases') }}">Purchases</a>
                </li>
                @endcan
                @can('manage-stock')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('stock-adjustments*') ? 'active' : '' }}"
                       href="{{ route('page.stock-adjustments') }}">Stock</a>
                </li>
                @endcan
                @can('view-category')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('categories*') ? 'active' : '' }}"
                       href="{{ route('page.categories') }}">Categories</a>
                </li>
                @endcan
                @can('view-customer')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('customers*') ? 'active' : '' }}"
                       href="{{ route('page.customers') }}">Customers</a>
                </li>
                @endcan
                @can('view-supplier')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}"
                       href="{{ route('page.suppliers') }}">Suppliers</a>
                </li>
                @endcan
                @can('view-report')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}"
                       href="{{ route('page.reports') }}">Reports</a>
                </li>
                @endcan
                @can('view-user')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}"
                       href="{{ route('users.index') }}">Users</a>
                </li>
                @endcan
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>