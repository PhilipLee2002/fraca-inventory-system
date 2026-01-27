<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Welcome back, {{ Auth::user()->name }}! 👋</h3>
                            <p class="text-gray-600 mt-1">
                                You are logged in as <span class="font-semibold text-blue-600">{{ Auth::user()->role->name ?? 'User' }}</span>.
                                Last login: {{ Auth::user()->updated_at->format('F d, Y h:i A') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <!-- REMOVED STATUS BADGE SINCE COLUMN DOESN'T EXIST -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ Auth::user()->role->name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Products -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-box text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Products</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Sales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                                <p class="text-2xl font-semibold text-gray-900">$0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\User::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (Only for Admin/Manager) -->
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @can('create-product')
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                            <div class="text-blue-600 mb-2">
                                <i class="fas fa-plus text-2xl"></i>
                            </div>
                            <p class="font-medium">Add Product</p>
                        </a>
                        @endcan

                        @can('create-sale')
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                            <div class="text-green-600 mb-2">
                                <i class="fas fa-cart-plus text-2xl"></i>
                            </div>
                            <p class="font-medium">New Sale</p>
                        </a>
                        @endcan

                        @can('create-purchase')
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                            <div class="text-yellow-600 mb-2">
                                <i class="fas fa-shopping-bag text-2xl"></i>
                            </div>
                            <p class="font-medium">New Purchase</p>
                        </a>
                        @endcan

                        @can('create-user')
                        <a href="{{ route('users.create') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                            <div class="text-purple-600 mb-2">
                                <i class="fas fa-user-plus text-2xl"></i>
                            </div>
                            <p class="font-medium">Add User</p>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Users (Admin Only) -->
                @if(Auth::user()->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Recent Users</h3>
                            <a href="{{ route('users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        <div class="space-y-4">
                            @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                            <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-800 font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        {{ $user->role->name == 'admin' ? 'bg-purple-100 text-purple-800' : 
                                           ($user->role->name == 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $user->role->name }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- System Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">System Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-database text-green-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Database</p>
                                        <p class="text-sm text-gray-500">Connected and running</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Online
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-users text-blue-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Registered Users</p>
                                        <!-- CHANGED: Removed status filter, just show total count -->
                                        <p class="text-sm text-gray-500">{{ \App\Models\User::count() }} registered</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-box text-yellow-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Inventory</p>
                                        <p class="text-sm text-gray-500">Products: 0, Categories: 0</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Quick Links</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @can('view-product')
                                <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                    <i class="fas fa-box mr-1"></i> Products
                                </a>
                                @endcan
                                @can('view-sale')
                                <a href="{{ route('sales.index') }}" class="text-sm text-green-600 hover:text-green-800 hover:underline">
                                    <i class="fas fa-shopping-cart mr-1"></i> Sales
                                </a>
                                @endcan
                                @can('view-purchase')
                                <a href="{{ route('purchases.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800 hover:underline">
                                    <i class="fas fa-shopping-bag mr-1"></i> Purchases
                                </a>
                                @endcan
                                @if(Auth::user()->isAdmin())
                                <a href="{{ route('users.index') }}" class="text-sm text-purple-600 hover:text-purple-800 hover:underline">
                                    <i class="fas fa-users mr-1"></i> Users
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Font Awesome for icons -->
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endpush
</x-app-layout>